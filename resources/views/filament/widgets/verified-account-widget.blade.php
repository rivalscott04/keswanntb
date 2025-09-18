<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-success-600">
                        Akun Terverifikasi
                    </h3>
                    <p class="mt-1 text-gray-600">
                        Selamat! Akun Anda telah diverifikasi dan aktif. Anda dapat melakukan pengajuan dan mengakses dokumen SP3 Anda.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::button
                        color="success"
                        tag="a"
                        href="{{ $this->getSp3Url() }}"
                        target="_blank"
                        icon="heroicon-o-document-text"
                    >
                        Lihat SP3
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        tag="a"
                        href="{{ route('filament.admin.resources.pengajuan.create') }}"
                        icon="heroicon-o-plus"
                    >
                        Buat Pengajuan
                    </x-filament::button>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
