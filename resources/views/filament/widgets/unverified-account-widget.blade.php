@php
    $verificationStatus = $this->getVerificationStatus();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-4">
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-warning-600">
                        {{ $verificationStatus['message'] }}
                    </h3>
                    <p class="mt-1 text-gray-600">
                        {{ $verificationStatus['description'] }}
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <x-filament::button
                        color="primary"
                        tag="a"
                        href="{{ route('filament.admin.auth.profile') }}"
                    >
                        Perbarui Profil
                    </x-filament::button>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget> 