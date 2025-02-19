@props([
    'label',
    'results',
])

<li
    {{ $attributes->class(['fi-global-search-result-group']) }}
>
    <div
        class="sticky top-0 z-10 border-b border-gray-200 bg-gray-50"
    >
        <h3
            class="px-4 py-2 text-sm font-semibold capitalize text-gray-950"
        >
            {{ $label }}
        </h3>
    </div>

    <ul class="divide-y divide-gray-200">
        @foreach ($results as $result)
            <x-filament-panels::global-search.result
                :actions="$result->actions"
                :details="$result->details"
                :title="$result->title"
                :url="$result->url"
            />
        @endforeach
    </ul>
</li>
