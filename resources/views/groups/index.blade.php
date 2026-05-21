<x-app-layout>
    <x-slot name="title">Grup Saya</x-slot>

    <div class="flex h-full">
        <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-200">
                <h2 class="font-semibold text-gray-700 mb-3">Grup Saya</h2>
                <button onclick="document.getElementById('modal-create').classList.remove('hidden')"
                        class="w-full bg-indigo-600 text-white text-sm py-2 rounded-lg hover:bg-indigo-700">
                    + Buat Grup Baru
                </button>
            </div>

            <div class="flex-1 overflow-y-auto">
                @forelse($groups as $group)
                <a href="{{ route('groups.show', $group) }}"
                   class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-100">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-600">
                        {{ strtoupper(substr($group->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-sm text-gray-800 truncate">{{ $group->name }}</p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ $group->lastMessage?->body ?? 'Belum ada pesan' }}
                        </p>
                    </div>
                </a>
                @empty
                <p class="p-4 text-sm text-gray-400">Belum ada grup. Buat yang pertama!</p>
                @endforelse
            </div>
        </div>

        <div class="flex-1 flex items-center justify-center bg-gray-50">
            <div class="text-center text-gray-400">
                <p class="text-2xl mb-2">💬</p>
                <p>Pilih grup untuk mulai chat</p>
            </div>
        </div>
    </div>

    {{-- Modal Buat Grup --}}
    <div id="modal-create" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
        <div class="bg-white rounded-xl p-6 w-96 shadow-xl">
            <h3 class="font-semibold text-gray-800 mb-4">Buat Grup Baru</h3>
            <form action="{{ route('groups.store') }}" method="POST">
                @csrf
                <input name="name" placeholder="Nama grup" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-3 text-sm">
                <textarea name="description" placeholder="Deskripsi (opsional)" rows="3"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 mb-4 text-sm"></textarea>
                <div class="flex gap-2">
                    <button type="button"
                            onclick="document.getElementById('modal-create').classList.add('hidden')"
                            class="flex-1 border border-gray-300 text-gray-700 py-2 rounded-lg text-sm">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 bg-indigo-600 text-white py-2 rounded-lg text-sm hover:bg-indigo-700">
                        Buat
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>