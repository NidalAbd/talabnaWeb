<?php

namespace Tests\Unit;

use App\Models\BadgeType;
use App\Services\Feed\SponsoredPicker;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\MigratesTolerantly;
use Tests\TestCase;

/** The admin's "view boost %" per badge sets the featured share in the feeds (2026-10-10). */
class BadgeWeightsTest extends TestCase
{
    use MigratesTolerantly;

    protected function setUp(): void
    {
        parent::setUp();
        $this->migrateTolerantly();
        Cache::flush();
        foreach ([['diamond', 200], ['gold', 100], ['silver', 50]] as $i => [$slug, $boost]) {
            DB::table('badge_types')->insert([
                'id' => $i + 1, 'name' => json_encode(['en' => $slug]), 'slug' => $slug, 'points_per_day' => 1, 'priority' => $i + 1,
                'view_boost_percent' => $boost, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function test_weights_come_from_the_admin_percentages(): void
    {
        $this->assertEquals(['ماسي' => 3.0, 'ذهبي' => 2.0, 'فضي' => 1.5], SponsoredPicker::weights());
    }

    public function test_the_share_of_picks_follows_the_weights(): void
    {
        $picker = new SponsoredPicker();
        $candidates = [['id' => 1, 'have_badge' => 'ماسي'], ['id' => 2, 'have_badge' => 'ذهبي'], ['id' => 3, 'have_badge' => 'فضي']];
        $first = [1 => 0, 2 => 0, 3 => 0];
        $runs = 6000;
        for ($i = 0; $i < $runs; $i++) {
            $first[$picker->pick($candidates, 1, "seed$i")[0]]++;
        }
        // 3 : 2 : 1.5 of 6.5
        $this->assertEqualsWithDelta(3 / 6.5, $first[1] / $runs, 0.03);
        $this->assertEqualsWithDelta(2 / 6.5, $first[2] / $runs, 0.03);
        $this->assertEqualsWithDelta(1.5 / 6.5, $first[3] / $runs, 0.03);
    }

    public function test_an_admin_change_applies_at_once(): void
    {
        SponsoredPicker::weights();
        BadgeType::find(1)->update(['view_boost_percent' => 400]);
        $this->assertEquals(5.0, SponsoredPicker::weights()['ماسي']);
    }
}
