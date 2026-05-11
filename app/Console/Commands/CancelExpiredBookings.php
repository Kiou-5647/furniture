<?php

namespace App\Console\Commands;

use App\Actions\Booking\CancelBookingAction;
use App\Enums\BookingStatus;
use App\Models\Booking\Booking;
use App\Models\Hr\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';

    // The console command description
    protected $description = 'Tự động hủy lịch thiết kế nếu đã quá thời gian bắt đầu';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cancelAction = app(CancelBookingAction::class);

        $now = Carbon::now();

        // 1. Query lịch thiết kế có:
        $expiredBookings = Booking::query()
            // Lịch hẹn đã bắt đầu
            ->where('start_at', '<', $now)
            // và đang ở trong trạng thái chờ duyệt hoặc chờ đặt cọc
            ->whereIn('status', [BookingStatus::PendingDeposit, BookingStatus::PendingConfirmation])
            ->get();

        $count = $expiredBookings->count();

        if ($count === 0) {
            $this->info('Không có lịch hẹn quá hạn nào!');
            return 0;
        }

        // 2. Bắt đầu hủy lịch quá hạn
        foreach ($expiredBookings as $booking) {
            $performedBy = $booking->designer?->employee
                ?? Employee::whereHas('roles', fn($q) => $q->where('name', 'Quản trị viên'))->first();

            try {
                $cancelAction->execute($booking, $performedBy);
            } catch (\Exception $e) {
                $this->line("Không thể hủy lịch hẹn: {$booking->booking_number}.\n Lý do: {$e->getMessage()}");
            }

            $this->line("Hủy lịch hẹn: {$booking->booking_number}");
        }

        $this->info("Đã hủy {$count} lịch hẹn quá hạn.");
        return 0;
    }
}
