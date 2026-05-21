<nav class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
    <a href="{{ route('groups.index') }}" class="text-xl font-bold text-indigo-600">
        Whisperly
    </a>
    <div class="flex items-center gap-4">
        <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
        <div x-data="{ open: false }" class="relative">
            <button @click="open = !open"
                    class="text-sm px-3 py-1 rounded-full border border-gray-300 hover:bg-gray-50">
                Status
            </button>
            <div x-show="open" @click.outside="open = false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border p-2 z-50">
                @foreach(['online' => 'Online', 'away' => 'Away', 'offline' => 'Offline'] as $val => $label)
                <button onclick="setStatus('{{ $val }}')"
                        class="w-full text-left px-3 py-2 text-sm hover:bg-gray-50 rounded">
                    {{ $label }}
                </button>
                @endforeach
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="text-sm text-red-500 hover:underline">Keluar</button>
        </form>
    </div>
</nav>
<script>
function setStatus(status) {
    fetch('{{ route("user.status.update") }}', {
        method: 'PATCH',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({ status })
    });
}
</script>