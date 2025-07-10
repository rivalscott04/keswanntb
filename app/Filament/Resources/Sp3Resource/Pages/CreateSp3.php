<?php

namespace App\Filament\Resources\Sp3Resource\Pages;

use App\Filament\Resources\Sp3Resource;
use App\Models\Sp3;
use App\Models\HistoriSp3;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSp3 extends CreateRecord
{
    protected static string $resource = Sp3Resource::class;

    public $submitType = 'draft';

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('save_draft')
                ->label('Simpan Draft')
                ->color('secondary')
                ->action(function () {
                    $this->submitType = 'draft';
                    $this->handleCustomCreate();
                }),
            Actions\Action::make('submit')
                ->label('Ajukan')
                ->color('primary')
                ->action(function () {
                    $this->submitType = 'submitted';
                    $this->handleCustomCreate();
                }),
        ];
    }

    protected function handleCustomCreate()
    {
        $data = $this->form->getState();
        $data = $this->mutateFormDataBeforeCreate($data);
        $sp3 = Sp3::create($data);
        $this->record = $sp3;
        $this->afterCreate();
        $this->redirect($this->getRedirectUrl());
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = Auth::id();
        $data['status'] = $this->submitType;
        return $data;
    }

    protected function afterCreate(): void
    {
        $sp3 = $this->record;
        HistoriSp3::create([
            'sp3_id' => $sp3->id,
            'user_id' => Auth::id(),
            'status' => $sp3->status,
            'catatan' => $sp3->status === 'submitted' ? 'Pengajuan diajukan' : 'Disimpan sebagai draft',
            'data_sebelum' => null,
            'data_sesudah' => $sp3->toArray(),
        ]);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
