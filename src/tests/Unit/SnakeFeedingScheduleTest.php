<?php

namespace Tests\Unit;

use App\Models\Snake;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Pure date-arithmetic checks on Snake::computeNextDueFrom · no DB.
 *
 * The dashboard's traffic-light depends on this returning the right next-due
 * date for both modes. Days-mode was the gap the PR-23 feeding-schedule
 * form opened up · `nextFeedingDueAt` previously only honoured the interval.
 */
class SnakeFeedingScheduleTest extends TestCase
{
    public function test_interval_mode_adds_days(): void
    {
        $snake = new Snake([
            'feeding_interval_days' => 10,
            'feeding_days' => null,
        ]);

        $next = $snake->computeNextDueFrom(Carbon::parse('2026-05-21 14:00'));

        $this->assertSame('2026-05-31 14:00', $next->format('Y-m-d H:i'));
    }

    public function test_interval_mode_with_feeding_time_snaps_to_clock(): void
    {
        $snake = new Snake([
            'feeding_interval_days' => 7,
            'feeding_days' => null,
        ]);
        // feeding_time is cast as 'datetime:H:i' · simulate by reaching into the
        // attribute directly because the cast wants a parseable value.
        $snake->feeding_time = '18:30';

        $next = $snake->computeNextDueFrom(Carbon::parse('2026-05-21 09:00'));

        $this->assertSame('2026-05-28 18:30', $next->format('Y-m-d H:i'));
    }

    public function test_days_mode_picks_next_configured_weekday(): void
    {
        // Mon, Wed, Fri · last fed on a Monday → next is Wednesday.
        $snake = new Snake([
            'feeding_days' => ['mon', 'wed', 'fri'],
            'feeding_interval_days' => 14, // ignored when days-mode active
        ]);

        // 2026-05-18 is a Monday.
        $next = $snake->computeNextDueFrom(Carbon::parse('2026-05-18 14:00'));

        $this->assertSame('Wed', $next->format('D'));
        $this->assertSame('2026-05-20', $next->format('Y-m-d'));
    }

    public function test_days_mode_wraps_to_next_week(): void
    {
        // Schedule says Monday only · last fed Tuesday → next is the following Monday.
        $snake = new Snake([
            'feeding_days' => ['mon'],
            'feeding_interval_days' => 7,
        ]);

        // 2026-05-19 is a Tuesday.
        $next = $snake->computeNextDueFrom(Carbon::parse('2026-05-19 14:00'));

        $this->assertSame('Mon', $next->format('D'));
        $this->assertSame('2026-05-25', $next->format('Y-m-d'));
    }

    public function test_days_mode_anchors_to_feeding_time_when_set(): void
    {
        $snake = new Snake([
            'feeding_days' => ['mon', 'wed', 'fri'],
            'feeding_interval_days' => 14,
        ]);
        $snake->feeding_time = '18:30';

        $next = $snake->computeNextDueFrom(Carbon::parse('2026-05-18 09:00'));

        $this->assertSame('2026-05-20 18:30', $next->format('Y-m-d H:i'));
    }

    public function test_days_mode_skips_same_day(): void
    {
        // Even though Mon is in the list, we don't return the day the snake was fed.
        $snake = new Snake([
            'feeding_days' => ['mon', 'tue'],
            'feeding_interval_days' => 7,
        ]);

        // 2026-05-18 is a Monday.
        $next = $snake->computeNextDueFrom(Carbon::parse('2026-05-18 14:00'));

        $this->assertSame('Tue', $next->format('D'));
        $this->assertSame('2026-05-19', $next->format('Y-m-d'));
    }
}
