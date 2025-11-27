@php
    $user = $this->getUser();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <div class="flex-shrink-0">
                        <div class="w-16 h-16 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                            @svg('heroicon-o-user-circle', 'w-10 h-10 text-primary-600 dark:text-primary-400')
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $user->email }}
                        </p>
                        @if($user->kabKota)
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                                {{ $user->kabKota->nama }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($user->wewenang)
                        <x-filament::badge color="primary">
                            {{ $user->wewenang->nama }}
                        </x-filament::badge>
                    @endif
                    @if($user->is_admin)
                        <x-filament::badge color="danger">
                            Admin
                        </x-filament::badge>
                    @endif
                </div>
            </div>
            
            @if($user->wewenang && $user->wewenang->nama === 'Pengguna')
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="flex items-center gap-2">
                            @if($user->kab_kota_verified_at)
                                @svg('heroicon-o-check-circle', 'w-5 h-5 text-success-600')
                                <span class="text-sm text-gray-700 dark:text-gray-300">Terverifikasi Kab/Kota</span>
                            @else
                                @svg('heroicon-o-x-circle', 'w-5 h-5 text-danger-600')
                                <span class="text-sm text-gray-700 dark:text-gray-300">Belum Terverifikasi Kab/Kota</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($user->provinsi_verified_at)
                                @svg('heroicon-o-check-circle', 'w-5 h-5 text-success-600')
                                <span class="text-sm text-gray-700 dark:text-gray-300">Terverifikasi Provinsi</span>
                            @else
                                @svg('heroicon-o-x-circle', 'w-5 h-5 text-danger-600')
                                <span class="text-sm text-gray-700 dark:text-gray-300">Belum Terverifikasi Provinsi</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            @if($user->sp3)
                                @svg('heroicon-o-document-text', 'w-5 h-5 text-primary-600')
                                <span class="text-sm text-gray-700 dark:text-gray-300">SP3 Tersedia</span>
                            @else
                                @svg('heroicon-o-document', 'w-5 h-5 text-gray-400')
                                <span class="text-sm text-gray-700 dark:text-gray-300">SP3 Belum Diunggah</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

