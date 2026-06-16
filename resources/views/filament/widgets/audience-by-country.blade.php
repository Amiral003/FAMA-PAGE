<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Audience par pays
        </x-slot>

        <div class="space-y-3">
            @forelse ($countries as $item)
                <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3">
                    <span class="font-medium text-gray-800">
                        {{ $item->country }}
                    </span>
                    <span class="text-sm font-bold text-gray-900">
                        {{ number_format($item->visitors, 0, ',', ' ') }}
                    </span>
                </div>
            @empty
                <p class="text-sm text-gray-500">Aucune donnée pays disponible pour le moment.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
