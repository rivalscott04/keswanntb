<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-medium">Informasi Pribadi</h3>
                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">No. Telepon</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->phone }}</dd>
                        </div>
                    </dl>
                </div>

                <div>
                    <h3 class="text-lg font-medium">Informasi Perusahaan</h3>
                    <dl class="mt-4 space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Perusahaan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->company_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Alamat Perusahaan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->company_address }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">NPWP</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->npwp }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <h3 class="text-lg font-medium">Dokumen</h3>
            <dl class="mt-4 space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">SP3</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($user->sp3_file)
                            <a href="{{ Storage::url($user->sp3_file) }}" target="_blank" class="text-primary-600 hover:text-primary-500">
                                Lihat SP3
                            </a>
                        @else
                            <span class="text-gray-500">Belum diunggah</span>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">NPWP File</dt>
                    <dd class="mt-1 text-sm text-gray-900">
                        @if($user->npwp_file)
                            <a href="{{ Storage::url($user->npwp_file) }}" target="_blank" class="text-primary-600 hover:text-primary-500">
                                Lihat NPWP
                            </a>
                        @else
                            <span class="text-gray-500">Belum diunggah</span>
                        @endif
                    </dd>
                </div>
            </dl>
        </x-filament::section>

        <x-filament::section>
            <h3 class="text-lg font-medium">Status Verifikasi</h3>
            <dl class="mt-4 space-y-4">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        @if($user->provinsi_verified_at)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800">
                                Terverifikasi
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800">
                                Belum Terverifikasi
                            </span>
                        @endif
                    </dd>
                </div>
                @if($user->provinsi_verified_at)
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Diverifikasi Pada</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->provinsi_verified_at->format('d F Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Diverifikasi Oleh</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $user->provinsiVerifiedBy?->name }}</dd>
                    </div>
                @endif
            </dl>
        </x-filament::section>
    </div>
</x-filament-panels::page> 