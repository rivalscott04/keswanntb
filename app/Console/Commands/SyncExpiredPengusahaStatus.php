<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncExpiredPengusahaStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pengusaha:sync-expired-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nonaktifkan otomatis akun pengusaha yang tanggal berlakunya sudah kadaluarsa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $updated = User::query()
            ->whereHas('wewenang', function ($query) {
                $query->where('nama', 'Pengguna');
            })
            ->whereNotNull('tanggal_berlaku')
            ->where('tanggal_berlaku', '<=', now())
            ->where('is_active', true)
            ->update([
                'is_active' => false,
            ]);

        $this->info("Sinkronisasi selesai. {$updated} akun pengusaha dinonaktifkan karena kadaluarsa.");

        return self::SUCCESS;
    }
}
