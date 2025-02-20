@props(['name', 'show' => false, 'maxWidth' => '2xl', 'focusable' => true])

@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

<div
    x-data="{
        show: @js($show),
        focusable: @js($focusable),
        focusableElements: null,
        lastFocusedElement: null,
        init() {
            this.$watch('show', value => {
                if (value) {
                    document.body.classList.add('overflow-y-hidden');
                    this.$nextTick(() => {
                        this.lastFocusedElement = document.activeElement;
                        if (this.focusable) {
                            this.focusableElements = [...this.$el.querySelectorAll('a, button, input, textarea, select, details, [tabindex]:not([tabindex=\'-1\'])')];
                            this.focusableElements[0]?.focus();
                        }
                    });
                } else {
                    document.body.classList.remove('overflow-y-hidden');
                    if (this.focusable) {
                        this.lastFocusedElement?.focus();
                    }
                }
            });
        }
    }"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="
        if (!focusable) return;
        let firstFocusable = focusableElements[0];
        let lastFocusable = focusableElements[focusableElements.length - 1];
        if ($event.shiftKey) {
            if ($event.target === firstFocusable) {
                lastFocusable.focus();
            }
        } else {
            if ($event.target === lastFocusable) {
                firstFocusable.focus();
            }
        }
    "
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: none;"
>
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
    </div>

    <div
        x-show="show"
        class="mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>
