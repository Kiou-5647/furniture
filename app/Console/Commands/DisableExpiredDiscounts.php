<?php

namespace App\Console\Commands;

use App\Models\Sales\Discount;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DisableExpiredDiscounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:disable-expired';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Tự động tắt các chương trình khuyến mãi đã hết hạn';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        
        // Tìm các khuyến mãi đang active nhưng đã quá ngày kết thúc
        $expiredDiscounts = Discount::where('is_active', true)
            ->whereNotNull('end_at')
            ->where('end_at', '<', $now)
            ->get();

        $count = $expiredDiscounts->count();

        if ($count === 0) {
            $this->info('Không có khuyến mãi nào hết hạn.');
            return self::SUCCESS;
        }

        $this->info('Đang tắt ' . $count . ' chương trình khuyến mãi hết hạn...');

        foreach ($expiredDiscounts as $discount) {
            $discount->update(['is_active' => false]);
            $this->line("Đã tắt: {$discount->name}");
        }

        $this->info('Hoàn thành cập nhật trạng thái khuyến mãi.');
        
        return self::SUCCESS;
    }
}
