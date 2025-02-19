<x-filament-panels::page.simple>
    <p class="text-center text-sm text-gray-500">
        {{
            __('filament-panels::pages/auth/email-verification/email-verification-prompt.messages.notification_sent', [
                'email' => filament()->auth()->user()->getEmailForVerification(),
            ])
        }}
    </p>

    <p class="text-center text-sm text-gray-500">
        {{ __('filament-panels::pages/auth/email-verification/email-verification-prompt.messages.notification_not_received') }}

        {{ $this->resendNotificationAction }}
    </p>
</x-filament-panels::page.simple>
