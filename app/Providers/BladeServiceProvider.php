<?php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\View\Components\AuthValidationErrors;
use App\View\Components\Label;
use App\View\Components\Input;
use App\View\Components\Button;
use App\View\Components\AuthCard;
use App\View\Components\Mail\Message;
use App\View\Components\Mail\Button as MailButton;
use App\View\Components\Mail\Panel;

class BladeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Register form components
        Blade::component('auth-validation-errors', AuthValidationErrors::class);
        Blade::component('auth-card', AuthCard::class);
        Blade::component('label', Label::class);
        Blade::component('input', Input::class);
        Blade::component('button', Button::class);

        // Register mail components
        Blade::component('mail::message', Message::class);
        Blade::component('mail::button', MailButton::class);
        Blade::component('mail::panel', Panel::class);

        // Register mail layout components
        Blade::component('mail::layout', 'mail.layout');
        Blade::component('mail::header', 'mail.header');
        Blade::component('mail::footer', 'mail.footer');
        Blade::component('mail::subcopy', 'mail.subcopy');
    }
}
