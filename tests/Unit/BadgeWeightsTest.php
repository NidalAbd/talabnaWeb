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
        $w = SponsoredPicker::weights();
        $this->assertEquals([3.0, 2.0, 1.5], [$w['ماسي'], $w['ذهبي'], $w['فضي']]);
        $this->assertEquals([3.0, 2.0, 1.5], [$w['id:1'], $w['id:2'], $w['id:3']]);
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

    public function test_a_badge_the_admin_adds_counts_by_its_own_boost_above_or_between_the_others(): void
    {
        // "Gold Diamond" between Diamond and Gold, and "Royal" above Diamond
        BadgeType::create(['name' => ['ar' => 'ذهبي ماسي', 'en' => 'Gold Diamond'], 'slug' => 'gold-diamond', 'points_per_day' => 2, 'priority' => 2, 'view_boost_percent' => 150, 'is_active' => true, 'is_default' => false]);
        $royal = BadgeType::create(['name' => ['ar' => 'ملكي', 'en' => 'Royal'], 'slug' => 'royal', 'points_per_day' => 5, 'priority' => 0, 'view_boost_percent' => 500, 'is_active' => true, 'is_default' => false]);
        $w = SponsoredPicker::weights();
        $this->assertEquals(2.5, $w['ذهبي ماسي']);
        $this->assertEquals(6.0, $w['ملكي']);
        $this->assertEquals(6.0, $w['id:'.$royal->id]);

        // In picks: Royal (6) ahead of Diamond (3) about 2 to 1
        $picker = new SponsoredPicker();
        $c = [['id' => 1, 'have_badge' => 'ماسي'], ['id' => 2, 'have_badge' => 'ملكي', 'badge_type_id' => $royal->id]];
        $royalFirst = 0;
        for ($i = 0; $i < 3000; $i++) {
            $royalFirst += $picker->pick($c, 1, "s$i")[0] === 2 ? 1 : 0;
        }
        $this->assertEqualsWithDelta(6 / 9, $royalFirst / 3000, 0.04);

        // And it is ordered by its level where the order is by badge
        $this->assertStringContainsString("'ملكي'", BadgeType::getLegacyOrderByClause());
    }
}
