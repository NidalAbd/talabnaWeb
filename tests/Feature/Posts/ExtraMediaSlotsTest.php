<?php

namespace Tests\Feature\Posts;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** Photos/videos beyond the free four cost points, taken in the same transaction as the post. */
class ExtraMediaSlotsTest extends TestCase
{
    use MigratesTolerantly;

    private User $user;
    private int $cat;
    private int $sub;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        DB::statement('PRAGMA foreign_keys = OFF');
        Storage::fake('public');

        Schema::dropIfExists('point_transactions');
        Schema::create('point_transactions', function ($t) {
            $t->id();
            $t->unsignedBigInteger('from_user_id')->nullable();
            $t->unsignedBigInteger('to_user_id')->nullable();
            $t->enum('type', ['purchase', 'transfer', 'admin_grant', 'used']); // production's real schema
            $t->integer('point');
            $t->string('status')->nullable();
            $t->text('metadata')->nullable();
            $t->timestamps();
        });

        // Production renamed title_json/description_json with a MySQL-only migration that SQLite skipped.
        Schema::table('service_posts', function ($t) {
            $t->text('title')->nullable();
            $t->text('description')->nullable();
        });

        $this->cat = DB::table('categories')->insertGetId(['name' => json_encode(['en' => 'Cars']), 'created_at' => now(), 'updated_at' => now()]);
        $this->sub = DB::table('sub_categories')->insertGetId(['categories_id' => $this->cat, 'name' => json_encode(['en' => 'Sedan']), 'created_at' => now(), 'updated_at' => now()]);
        $id = DB::table('users')->insertGetId([
            'name' => 'Seller', 'user_name' => 'seller1', 'email' => 's@example.com', 'gender' => 'ذكر', 'password' => 'x',
            'auth_type' => 'email', 'is_active' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('palservice_points')->insert(['user_id' => $id, 'point' => 10, 'created_at' => now(), 'updated_at' => now()]);
        $this->user = User::find($id);
        Passport::actingAs($this->user);
    }

    private function balance(): int
    {
        return (int) DB::table('palservice_points')->where('user_id', $this->user->id)->value('point');
    }

    private function body(int $images): array
    {
        return [
            'categories_id' => $this->cat, 'sub_categories_id' => $this->sub, 'title' => 'Car for sale', 'description' => 'Clean car, one owner, no accidents.',
            'price' => 1000, 'locationLatitudes' => 30.0, 'locationLongitudes' => 31.0, 'type' => 'عرض',
            'images' => array_map(fn ($i) => UploadedFile::fake()->image("p$i.jpg"), range(1, max(1, $images))),
        ];
    }

    public function test_four_photos_are_free(): void
    {
        $this->post('/api/service_posts', $this->body(4), ['Accept' => 'application/json'])->assertStatus(201);
        $this->assertSame(10, $this->balance());
    }

    public function test_each_photo_beyond_four_costs_one_point_and_leaves_a_ledger_row(): void
    {
        $this->post('/api/service_posts', $this->body(6), ['Accept' => 'application/json'])->assertStatus(201);

        $this->assertSame(8, $this->balance());
        $this->assertDatabaseHas('point_transactions', ['type' => 'used', 'point' => 2]);
        $this->assertSame(6, DB::table('photos')->count());
    }

    public function test_without_enough_points_nothing_is_created_and_nothing_is_taken(): void
    {
        DB::table('palservice_points')->where('user_id', $this->user->id)->update(['point' => 1]);

        $this->post('/api/service_posts', $this->body(6), ['Accept' => 'application/json'])
            ->assertStatus(402)->assertJsonPath('required', 2)->assertJsonPath('balance', 1);

        $this->assertSame(1, $this->balance());
        $this->assertSame(0, DB::table('service_posts')->count());
        $this->assertSame(0, DB::table('photos')->count());
    }

    public function test_more_than_the_maximum_is_refused(): void
    {
        $this->post('/api/service_posts', $this->body(11), ['Accept' => 'application/json'])->assertStatus(422);
        $this->assertSame(10, $this->balance());
        $this->assertSame(0, DB::table('service_posts')->count());
    }

    private function createPostWithFour(): int
    {
        $this->post('/api/service_posts', $this->body(4), ['Accept' => 'application/json'])->assertStatus(201);

        return (int) DB::table('service_posts')->value('id');
    }

    private function updateBody(int $newImages): array
    {
        $b = $this->body($newImages);
        $b['_method'] = 'PUT';
        if ($newImages === 0) {
            unset($b['images']);
        }

        return $b;
    }

    public function test_updating_a_post_that_has_four_charges_only_for_the_new_ones(): void
    {
        $id = $this->createPostWithFour();

        $this->post("/api/service_posts/$id", $this->updateBody(2), ['Accept' => 'application/json'])->assertOk();

        $this->assertSame(8, $this->balance(), 'the 5th and 6th cost one point each');
        $this->assertSame(6, DB::table('photos')->count());
    }

    public function test_updating_without_new_media_costs_nothing(): void
    {
        $id = $this->createPostWithFour();

        $this->post("/api/service_posts/$id", $this->updateBody(0), ['Accept' => 'application/json'])->assertOk();
        $this->assertSame(10, $this->balance());
    }

    public function test_an_update_without_enough_points_saves_no_media_and_takes_nothing(): void
    {
        $id = $this->createPostWithFour();
        DB::table('palservice_points')->where('user_id', $this->user->id)->update(['point' => 1]);

        $this->post("/api/service_posts/$id", $this->updateBody(3), ['Accept' => 'application/json'])->assertStatus(402)->assertJsonPath('required', 3);

        $this->assertSame(1, $this->balance());
        $this->assertSame(4, DB::table('photos')->count());
    }
}
