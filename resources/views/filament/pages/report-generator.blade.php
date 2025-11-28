<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Generator Laporan Pengajuan Ternak</h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Gunakan form di bawah ini untuk generate laporan Excel berdasarkan filter yang Anda pilih.
                Laporan akan berisi data detail pengajuan ternak dengan berbagai breakdown dan analisis.
            </p>

            {{ $this->form }}

            <div class="mt-6 flex justify-end">
                <x-filament::button
                    type="button"
                    wire:click="generateReport"
                    icon="heroicon-o-arrow-down-tray"
                    color="success"
                    size="lg"
                >
                    Generate Laporan Excel
                </x-filament::button>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 dark:text-blue-100 mb-2">Informasi Laporan</h3>
            <ul class="text-sm text-blue-800 dark:text-blue-200 space-y-1 list-disc list-inside">
                <li>Laporan akan berisi 7 sheet: Ringkasan Eksekutif, Detail Transaksi, Antar Kab/Kota, Pengeluaran, Pemasukan, Analisis Kuota, dan Breakdown Perusahaan</li>
                <li>Data yang ditampilkan berdasarkan rentang tanggal yang dipilih</li>
                <li>Jika jenis ternak tidak dipilih, semua jenis ternak akan dimasukkan dalam laporan</li>
                <li>File Excel akan otomatis terdownload setelah proses selesai</li>
            </ul>
        </div>
    </div>
</x-filament-panels::page>

