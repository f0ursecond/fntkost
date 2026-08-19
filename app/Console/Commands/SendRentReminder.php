<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

use App\Services\FonnteService;
use App\Console\Commands\SendRentReminder;
use App\Models\Tenant;

#[Signature('rent:reminder')]
#[Description('Send rent expiration reminders to tenants')]
class SendRentReminder extends Command
{
    protected $signature = 'rent:reminder';

    protected $description = 'Send rent expiration reminders to tenants';

    public function handle(FonnteService $fonnte)
    {
        $reminderDate = now()->addDays(7)->toDateString();

        $tenants = Tenant::where('is_active', true)
            ->whereDate('move_out_date', $reminderDate)
            ->get();

        foreach ($tenants as $tenant) {

            $message =
                "Halo {$tenant->name},\n\n" .
                "Masa sewa kamar {$tenant->room_number} akan berakhir pada " .
                $tenant->move_out_date->format('d F Y') .
                ".\n\n" .
                "Mohon melakukan pembayaran/perpanjangan sebelum masa sewa berakhir.\n\n" .
                "Terima kasih.";

            $fonnte->send(
                $tenant->phone_number,
                $message
            );
        }

        return Command::SUCCESS;
    }
}
