<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Input extends Component
{
    public $type;
    public $name;
    public $id;

    public function __construct($type = 'text', $name = '', $id = '')
    {
        $this->type = $type;
        $this->name = $name;
        $this->id = $id ?: $name;
    }

    public function render()
    {
        return view('components.input');
    }
}
