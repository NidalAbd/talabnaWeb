<?php

namespace Tests\Unit;

use App\Models\Notification;
use PHPUnit\Framework\TestCase;

/** Tapping a notification opens what it is about (2026-10-10: an expired badge opened nothing). */
class NotificationTargetTest extends TestCase
{
    public function test_a_badge_expiry_written_as_post_number_opens_that_post(): void
    {
        $msg = json_encode(['en' => 'Your Gold badge on post #2783 has expired and is now Normal.', 'ar' => 'انتهت صلاحية شارة ذهبي على منشورك #2783 وأصبحت عادي']);
        $this->assertSame(['type' => 'post', 'id' => 2783], Notification::deriveTarget('badge', json_encode($msg)));
        $this->assertSame(['type' => 'post', 'id' => 2783], Notification::deriveTarget('badge_expired', $msg));
    }

    public function test_the_post_tag_still_wins_and_other_types_do_not_guess_from_numbers(): void
    {
        $this->assertSame(['type' => 'post', 'id' => 55], Notification::deriveTarget('badge', 'Applied to #12 [post_id:55]'));
        $this->assertSame(['type' => 'notifications', 'id' => null], Notification::deriveTarget('marketing', 'Offer #1 today'));
    }
}
