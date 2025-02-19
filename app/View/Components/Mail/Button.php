<?php

namespace App\View\Components\Mail;

use Illuminate\View\Component;

class Button extends Component
{
    public $url;
    public $color;

    public function __construct($url, $color = 'primary')
    {
        $this->url = $url;
        $this->color = $color;
    }

    public function render()
    {
        return view('components.mail.button');
    }
}
