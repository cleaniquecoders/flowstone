<?php

namespace CleaniqueCoders\Flowstone\View\Components;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Layout extends Component
{
    public function render(): View
    {
        // Render the anonymous component view under the package view namespace
        return view('flowstone::components.layout');
    }
}
