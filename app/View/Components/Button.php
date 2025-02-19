<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Button extends Component
{
    public $type;

    public function __construct($type = 'button')
    {
        $this->type = $type;
    }

    public function render()
    {
        return view('components.button');
    }
}
