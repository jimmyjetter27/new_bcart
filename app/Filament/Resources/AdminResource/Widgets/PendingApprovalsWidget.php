<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Photo;
use Filament\Widgets\Widget;

class PendingApprovalsWidget extends Widget
{
    public $pendingPhotos;
    public $totalPending;

    protected static string $view = 'filament.widgets.pending-approvals';

    public function mount()
    {
        $this->pendingPhotos = Photo::where('is_approved', false)->take(5)->get();
        $this->totalPending = Photo::where('is_approved', false)->count();
    }
}
