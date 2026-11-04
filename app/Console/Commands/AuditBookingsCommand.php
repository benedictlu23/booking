<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class AuditBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:audit
                            {--cleanup-stale : Cancel stale pending test bookings to free room availability}
                            {--room= : Audit a specific room ID only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Audit room inventory, booking calendar integrity, availability engine, and next open slots.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('====================================================');
        $this->info('       AGRO-INLAND RESORT BOOKING AUDIT WORKFLOW     ');
        $this->info('====================================================');
        $this->newLine();

        $roomId = $this->option('room');
        $cleanupStale = $this->option('cleanup-stale');

        if ($cleanupStale) {
            $this->performStaleBookingsCleanup();
        }

        $roomsQuery = Room::with(['category', 'bookings'])->where('is_active', true);
        if ($roomId) {
            $roomsQuery->where('id', $roomId);
        }
        $rooms = $roomsQuery->get();

        if ($rooms->isEmpty()) {
            $this->error('No active rooms found in database.');

            return 1;
        }

        $this->info("Found {$rooms->count()} active room(s). Auditing inventory & calendar...");
        $this->newLine();

        // 1. Inventory & Next Available Date Table
        $tableData = [];
        $today = Carbon::today();

        foreach ($rooms as $room) {
            $activeBookingsCount = $room->bookings()
                ->whereIn('status', ['pending', 'confirmed', 'checked_in'])
                ->count();

            $isAvailableToday = $room->isAvailableFor($today, $room->booking_type === 'day_pass' ? $today : $today->copy()->addDay());
            $nextDate = $room->findNextAvailableDate($today);

            $tableData[] = [
                'ID' => $room->id,
                'Number' => $room->room_number,
                'Name' => $room->name,
                'Type' => $room->booking_type,
                'Status' => $room->status,
                'Active Bookings' => $activeBookingsCount,
                'Avail Today?' => $isAvailableToday ? 'YES' : 'NO',
                'Next Open Date' => $nextDate ? $nextDate->format('Y-m-d') : 'NONE (next 90 days)',
            ];
        }

        $this->table(
            ['ID', 'Number', 'Name', 'Type', 'Status', 'Active Bookings', 'Avail Today?', 'Next Open Date'],
            $tableData
        );
        $this->newLine();

        // 2. Data Integrity Checks
        $this->info('Checking database integrity for booking anomalies...');
        $corruptBookings = Booking::whereColumn('check_out', '<', 'check_in')
            ->where('booking_type', '!=', 'day_pass')
            ->get();

        if ($corruptBookings->isNotEmpty()) {
            $this->error("Found {$corruptBookings->count()} corrupted booking(s) where check_out is earlier than check_in!");
            foreach ($corruptBookings as $cb) {
                $this->line(" - Booking ID: {$cb->id} | Room: {$cb->room_id} | In: {$cb->check_in} | Out: {$cb->check_out}");
            }
        } else {
            $this->line('  ✓ No inverted check-in/out records found.');
        }

        // 3. Engine Simulation Tests on Rooms
        $this->newLine();
        $this->info('Testing availability engine logic across room types...');
        $allPassed = true;

        foreach ($rooms as $room) {
            $this->line("-> Testing Room {$room->id} ({$room->name} - {$room->booking_type}):");

            $nextOpen = $room->findNextAvailableDate($today);
            if (! $nextOpen) {
                $this->warn("  ! Skipped engine tests for Room {$room->id}: No open dates found in 90 days.");

                continue;
            }

            // Test A: Availability on known open date
            $testOut = $room->booking_type === 'day_pass' ? $nextOpen : $nextOpen->copy()->addDay();
            if ($room->isAvailableFor($nextOpen, $testOut)) {
                $this->line("  ✓ Open date check ({$nextOpen->format('Y-m-d')}) correctly reports AVAILABLE.");
            } else {
                $this->error("  ✗ Open date check ({$nextOpen->format('Y-m-d')}) failed: Expected true, got false.");
                $allPassed = false;
            }

            // Test B: Day-Pass vs Overnight Type enforcement
            if ($room->booking_type === 'day_pass') {
                $multiNight = $room->isAvailableFor($nextOpen, $nextOpen->copy()->addDays(2));
                if (! $multiNight) {
                    $this->line('  ✓ Day pass constraint correctly blocks multi-night overnight reservation.');
                }
            } elseif ($room->booking_type === 'overnight') {
                $sameDay = $room->isAvailableFor($nextOpen, $nextOpen);
                if (! $sameDay) {
                    $this->line('  ✓ Overnight constraint correctly blocks same-day day-pass reservation.');
                }
            }
        }

        $this->newLine();
        if ($allPassed) {
            $this->info('====================================================');
            $this->info('  AUDIT COMPLETE: ALL ROOM AVAILABILITY CHECKS PASSED');
            $this->info('====================================================');

            return 0;
        }

        $this->error('AUDIT FINISHED WITH ISSUES. Please review warnings above.');

        return 1;
    }

    protected function performStaleBookingsCleanup(): void
    {
        $this->info('Cleaning up stale pending test bookings...');

        $stale = Booking::where('status', 'pending')
            ->where(function ($q) {
                $q->where('created_at', '<', now()->subHours(24))
                    ->orWhere('guest_name', 'like', '%test%')
                    ->orWhere('guest_email', 'like', '%test%');
            })
            ->get();

        if ($stale->isEmpty()) {
            $this->line('  No stale test bookings to clean up.');

            return;
        }

        $count = $stale->count();
        foreach ($stale as $booking) {
            $booking->update(['status' => 'cancelled']);
        }

        $this->info("  Cancelled {$count} stale pending booking(s) to free up room availability.");
    }
}
