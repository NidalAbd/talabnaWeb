<?php

namespace Tests\Unit;

use App\Services\Feed\SponsoredPicker;
use PHPUnit\Framework\TestCase;

class SponsoredPickerTest extends TestCase
{
    private function pool(int $diamonds, int $golds, int $others = 0): array
    {
        $out = [];
        $id = 1;
        foreach ([['ماسي', $diamonds], ['ذهبي', $golds], ['فضي', $others]] as [$badge, $n]) {
            for ($i = 0; $i < $n; $i++) {
                $out[] = ['id' => $id++, 'have_badge' => $badge];
            }
        }

        return $out;
    }

    public function test_the_same_seed_gives_the_same_picks_regardless_of_candidate_order(): void
    {
        $p = new SponsoredPicker();
        $pool = $this->pool(10, 10, 10);

        $a = $p->pick($pool, 10, 'user5|2026092114');
        $b = $p->pick(array_reverse($pool), 10, 'user5|2026092114');

        $this->assertSame($a, $b, 'stable while the user scrolls and refreshes');
        $this->assertCount(10, array_unique($a));
    }

    public function test_different_users_and_hours_see_different_featured_posts(): void
    {
        $p = new SponsoredPicker();
        $pool = $this->pool(15, 15, 15);
        $sets = [];
        foreach (['u1|h1', 'u2|h1', 'u1|h2', 'u3|h1'] as $seed) {
            $sets[] = $p->pick($pool, 10, $seed);
        }

        $this->assertCount(4, array_unique(array_map('json_encode', $sets)), 'rotation: not the same ten for everyone, every hour');
    }

    public function test_every_featured_post_gets_its_turn_across_many_users(): void
    {
        $p = new SponsoredPicker();
        $pool = $this->pool(6, 6, 6);
        $seen = [];
        for ($u = 1; $u <= 200; $u++) {
            foreach ($p->pick($pool, 4, "user$u|h") as $id) {
                $seen[$id] = ($seen[$id] ?? 0) + 1;
            }
        }

        $this->assertCount(18, $seen, 'no featured post is starved');
    }

    public function test_higher_tiers_are_shown_more_often_but_lower_tiers_are_not_shut_out(): void
    {
        $p = new SponsoredPicker();
        $pool = $this->pool(5, 5, 5); // ids 1-5 diamond, 6-10 gold, 11-15 other
        $count = ['ماسي' => 0, 'ذهبي' => 0, 'فضي' => 0];
        $badgeOf = fn (int $id) => $id <= 5 ? 'ماسي' : ($id <= 10 ? 'ذهبي' : 'فضي');
        for ($u = 1; $u <= 400; $u++) {
            foreach ($p->pick($pool, 3, "user$u|h") as $id) {
                $count[$badgeOf($id)]++;
            }
        }

        $this->assertGreaterThan($count['ذهبي'], $count['ماسي']);
        $this->assertGreaterThan($count['فضي'], $count['ذهبي']);
        $this->assertGreaterThan(50, $count['فضي'], 'the lowest tier still appears');
    }

    public function test_an_empty_pool_or_zero_limit_picks_nothing(): void
    {
        $p = new SponsoredPicker();
        $this->assertSame([], $p->pick([], 10, 's'));
        $this->assertSame([], $p->pick($this->pool(3, 0), 0, 's'));
    }

    public function test_mixing_puts_two_featured_posts_between_organic_ones_at_fixed_places_without_duplicates(): void
    {
        $p = new SponsoredPicker();
        $organic = array_map(fn ($i) => "o$i", range(1, 10));
        $sponsored = [101 => 's101', 102 => 's102', 103 => 's103', 104 => 's104'];
        $picked = [101, 102, 103, 104];

        $page1 = $p->mix($organic, $sponsored, $picked, 1);
        $this->assertCount(12, $page1);
        $this->assertSame('s101', $page1[1]);
        $this->assertSame('s102', $page1[6 + 1]);
        $this->assertSame('o1', $page1[0], 'the newest organic post is still first');

        $page2 = $p->mix($organic, $sponsored, $picked, 2);
        $this->assertSame('s103', $page2[1]);
        $this->assertSame('s104', $page2[7]);

        $page3 = $p->mix($organic, $sponsored, $picked, 3);
        $this->assertSame($organic, $page3, 'once the picked list is used up, only organic posts');
        $this->assertCount(0, array_filter($page3, fn ($x) => str_starts_with($x, 's')));
        $this->assertSame(['s101', 's102', 's103', 's104'], array_values(array_filter(array_merge($page1, $page2), fn ($x) => str_starts_with($x, 's'))), 'each featured post appears once');
    }

    public function test_a_short_page_never_breaks_the_mix(): void
    {
        $p = new SponsoredPicker();
        $out = $p->mix(['o1', 'o2'], [1 => 's1', 2 => 's2'], [1, 2], 1);
        $this->assertSame(['o1', 's1', 'o2', 's2'], $out);
    }
}
