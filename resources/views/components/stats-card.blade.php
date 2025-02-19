<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
    <div class="flex items-center">
        <div class="p-3 rounded-full {{ $bgColor ?? 'bg-blue-500' }} bg-opacity-75">
            {{ $icon ?? '' }}
        </div>
        <div class="mx-5">
            <h4 class="text-2xl font-semibold text-gray-700">
                {{ $title }}
            </h4>
            <div class="text-gray-500">
                {{ $value }}
            </div>
        </div>
    </div>
    @if(isset($footer))
        <div class="mt-4 text-sm text-gray-600">
            {{ $footer }}
        </div>
    @endif
</div>
