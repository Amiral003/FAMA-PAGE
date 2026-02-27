<div class="relative">
    <!-- Badge cliquable -->
    <button @click="open = !open" class="relative">
        🔔
        @if($notifications->count())
            <span class="absolute top-0 right-0 bg-red-500 text-white text-xs rounded-full px-1">
                {{ $notifications->count() }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div x-show="open" class="absolute right-0 mt-2 w-80 bg-white border shadow-lg z-50">
        @forelse($notifications as $notification)
            <a href="{{ $notification->data['url'] ?? '#' }}"
               wire:click.prevent="markAsRead('{{ $notification->id }}')"
               class="block px-4 py-2 hover:bg-gray-100">
                <div class="font-bold">{{ $notification->data['title'] }}</div>
                <div class="text-sm">{{ $notification->data['message'] }}</div>
            </a>
        @empty
            <div class="px-4 py-2 text-gray-500">Aucune notification</div>
        @endforelse
    </div>
</div>
<script>
    Livewire.on('notification-received', () => {
        let audio = new Audio('/sounds/notification.mp3');
        audio.play();
    });
</script>