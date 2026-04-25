<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Illuminate\Contracts\View\View;

class Dashboard extends Component
{
    public function render(): View
    {
        return view('livewire.admin.dashboard')->layout('layouts.app');
    }
}
