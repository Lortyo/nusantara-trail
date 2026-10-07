<?php

namespace App\Console\Commands;

use App\Models\CategoryChange;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\MidtransService;
use Illuminate\Console\Command;

class ReleaseExpired extends Command
{
    protected $signature = 'registrations:release-expired';
    protected $description = 'Release slots of unpaid registrations and category changes';

    public function handle(MidtransService $midtrans): int
    {
        foreach (Registration::where('status', 'pending')->where('expiresAt', '<', now())->get() as $reg) {
            $midtrans->releaseRegistration($reg, 'expired');
            Payment::where('registration_id', (string) $reg->getKey())->where('status', 'pending')->update(['status' => 'expire']);
        }

        foreach (CategoryChange::where('status', 'pending_payment')->where('expiresAt', '<', now())->get() as $chg) {
            $midtrans->cancelChange($chg);
            Payment::where('change_id', (string) $chg->getKey())->where('status', 'pending')->update(['status' => 'expire']);
        }

        return self::SUCCESS;
    }
}