<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            API Token
        </x-slot>
        
        @if($token)
            <div class="space-y-2">
                <p class="text-sm text-gray-600">Your Bearer Token:</p>
                <div class="bg-gray-100 p-3 rounded font-mono text-xs break-all">
                    {{ $token }}
                </div>
                <p class="text-xs text-gray-500">
                    Use this token in your API requests with header: Authorization: Bearer {{ $token }}
                </p>
            </div>
        @else
            <p class="text-sm text-gray-600">No token available</p>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>