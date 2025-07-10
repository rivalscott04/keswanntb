<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
            <div class="flex-1">
                <h3 class="text-lg font-bold text-primary-600">
                    Selamat Datang di Sistem KESWAN NTB
                </h3>
                <p class="mt-1 text-gray-600">
                    Akun Anda telah berhasil dibuat. Untuk dapat melakukan pengajuan izin keluar ternak, Anda perlu membuat pengajuan SP3 (Sertifikat Pendaftaran Perusahaan Peternakan) terlebih dahulu. Silakan lengkapi data perusahaan dan upload dokumen yang diperlukan.
                </p>
                <div class="mt-3 text-sm text-gray-500">
                    <p><strong>Langkah selanjutnya:</strong></p>
                    <ol class="mt-1 space-y-1" style="list-style-type: decimal; margin-left: 1.25rem;">
                        <li><span class="font-semibold">Klik tombol "Buat Pengajuan SP3" di bawah</span></li>
                        <li>Lengkapi data perusahaan/instansi</li>
                        <li>Upload dokumen yang diperlukan</li>
                        <li>Submit pengajuan untuk diverifikasi</li>
                    </ol>
                </div>
            </div>
            <div class="flex justify-end gap-2 mt-6">
                <x-filament::button
                    color="primary"
                    tag="a"
                    href="{{ route('filament.admin.resources.sp3.create') }}"
                >
                    Buat Pengajuan SP3
                </x-filament::button>
                <x-filament::button
                    color="gray"
                    tag="a"
                    href="{{ route('filament.admin.auth.profile') }}"
                >
                    Perbarui Profil
                </x-filament::button>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget> 