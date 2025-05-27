<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CancelExpiredBookings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cancel-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Уақыты өткен брондарды автоматты түрде өшіреді';

    /**
     * Execute the console command.
     */
    public function handle()
    {
         $expiredBookings = Booking::where('is_paid', false)
                    ->where('status', 'pending')
                    ->where('expires_at', '<', Carbon::now())
                    ->get();

                foreach ($expiredBookings as $booking) {
                    $booking->update([
                        'status' => 'cancelled'
                    ]);
                    $this->info("Booking #{$booking->id} cancelled.");
                }

                $this->info('Авто-отмена завершена.');
    }
}
