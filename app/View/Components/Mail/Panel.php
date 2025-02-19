<?php

namespace App\View\Components\Mail;

use Illuminate\View\Component;

class Panel extends Component
{
    public function __construct()
    {
    }

    public function render()
    {
        return view('components.mail.panel');
    }
}
