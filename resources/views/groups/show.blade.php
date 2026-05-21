<x-app-layout>
    <x-slot name="title">{{ $group->name }}</x-slot>

    <div class="flex h-full"
         x-data="chatApp({{ $group->id }}, {{ auth()->id() }}, '{{ csrf_token() }}')"
         x-init="init()">

        {{-- Sidebar anggota --}}
        <div class="w-64 bg-white border-r border-gray-200 flex flex-col">
            <div class="p-3 border-b border-gray-200">
                <h3 class="font-semibold text-sm text-gray-700">{{ $group->name }}</h3>
                <p class="text-xs text-gray-400">{{ $members->count() }} anggota</p>
            </div>
            <div class="flex-1 overflow-y-auto p-2">
                @foreach($members as $member)
                <div class="flex items-center gap-2 px-2 py-2 rounded-lg hover:bg-gray-50"
                     :class="onlineUsers.includes({{ $member->id }}) ? '' : ''">
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600">
                            {{ strtoupper(substr($member->name, 0, 1)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full border-2 border-white"
                              :class="userStatuses[{{ $member->id }}] === 'online' ? 'bg-green-400' :
                                      userStatuses[{{ $member->id }}] === 'away'   ? 'bg-yellow-400' : 'bg-gray-300'">
                        </span>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-gray-700">{{ $member->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $member->pivot->role === 'admin' ? 'Admin' : 'Member' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="p-2 border-t border-gray-200">
                <a href="{{ route('groups.index') }}"
                   class="block text-center text-xs text-gray-400 hover:text-gray-600 py-1">
                    Kembali ke daftar
                </a>
            </div>
        </div>

        {{-- Area chat utama --}}
        <div class="flex-1 flex flex-col bg-gray-50">

            {{-- Header --}}
            <div class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between">
                <div>
                    <h2 class="font-semibold text-gray-800">{{ $group->name }}</h2>
                    <p class="text-xs text-gray-400" x-text="typingText"></p>
                </div>
                <button onclick="document.getElementById('modal-members').classList.remove('hidden')"
                        class="text-xs text-indigo-600 hover:underline">
                    Kelola Anggota
                </button>
            </div>

            {{-- Pesan --}}
            <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-3">
                @foreach($messages as $msg)
                <div class="message-item flex gap-3 {{ $msg->user_id === auth()->id() ? 'flex-row-reverse' : '' }}"
                     data-message-id="{{ $msg->id }}">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 shrink-0">
                        {{ strtoupper(substr($msg->user->name, 0, 1)) }}
                    </div>
                    <div class="max-w-xs lg:max-w-md">
                        @if($msg->replyTo)
                        <div class="text-xs bg-gray-200 rounded px-2 py-1 mb-1 border-l-2 border-indigo-400">
                            <span class="font-medium">{{ $msg->replyTo->user->name }}:</span>
                            {{ Str::limit($msg->replyTo->body, 60) }}
                        </div>
                        @endif
                        <div class="rounded-2xl px-4 py-2 {{ $msg->user_id === auth()->id() ? 'bg-indigo-600 text-white' : 'bg-white text-gray-800 shadow-sm' }}">
                            @if($msg->user_id !== auth()->id())
                            <p class="text-xs font-medium mb-1 {{ $msg->user_id === auth()->id() ? 'text-indigo-200' : 'text-indigo-600' }}">
                                {{ $msg->user->name }}
                            </p>
                            @endif
                            @if($msg->body)
                            <p class="text-sm">{{ $msg->body }}</p>
                            @endif
                            @if($msg->file_path)
                                @if($msg->file_type === 'image')
                                <img src="{{ Storage::url($msg->file_path) }}" class="rounded-lg max-w-full mt-1 max-h-60">
                                @else
                                <a href="{{ Storage::url($msg->file_path) }}" target="_blank"
                                   class="flex items-center gap-1 text-xs underline mt-1">
                                    📎 {{ $msg->file_name }}
                                </a>
                                @endif
                            @endif
                            <p class="text-xs mt-1 {{ $msg->user_id === auth()->id() ? 'text-indigo-200' : 'text-gray-400' }}">
                                {{ $msg->created_at->format('H:i') }}
                            </p>
                        </div>
                        {{-- Reactions --}}
                        <div class="flex flex-wrap gap-1 mt-1 reactions-{{ $msg->id }}">
                            @foreach($msg->reactionsGrouped()->get() as $r)
                            <button onclick="toggleReaction({{ $msg->id }}, '{{ $r->emoji }}')"
                                    class="text-xs bg-white border border-gray-200 rounded-full px-2 py-0.5 hover:bg-gray-50">
                                {{ $r->emoji }} {{ $r->count }}
                            </button>
                            @endforeach
                        </div>
                        {{-- Tombol aksi --}}
                        <div class="flex gap-2 mt-1">
                            @foreach(['👍','❤️','😂','😮','😢'] as $emoji)
                            <button onclick="toggleReaction({{ $msg->id }}, '{{ $emoji }}')"
                                    class="text-xs text-gray-400 hover:text-gray-600">
                                {{ $emoji }}
                            </button>
                            @endforeach
                            <button @click="replyTo = { id: {{ $msg->id }}, body: '{{ addslashes(Str::limit($msg->body, 60)) }}', user: '{{ $msg->user->name }}' }"
                                    class="text-xs text-gray-400 hover:text-indigo-500 ml-1">
                                Balas
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Reply preview --}}
            <div x-show="replyTo" x-cloak
                 class="bg-indigo-50 border-t border-indigo-200 px-4 py-2 flex items-center justify-between">
                <div>
                    <p class="text-xs text-indigo-600 font-medium">
                        Membalas <span x-text="replyTo?.user"></span>
                    </p>
                    <p class="text-xs text-gray-500 truncate" x-text="replyTo?.body"></p>
                </div>
                <button @click="replyTo = null" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            {{-- Input area --}}
            <div class="bg-white border-t border-gray-200 p-3">
                <div class="flex items-end gap-2">
                    <label class="cursor-pointer text-gray-400 hover:text-gray-600 p-2">
                        📎
                        <input type="file" class="hidden" @change="handleFile($event)">
                    </label>
                    <div x-show="selectedFile" class="text-xs text-gray-500 px-2 py-1 bg-gray-100 rounded">
                        <span x-text="selectedFile?.name"></span>
                        <button @click="selectedFile = null" class="ml-1 text-red-400">&times;</button>
                    </div>
                    <textarea
                        x-model="newMessage"
                        @keydown.enter.prevent.exact="sendMessage()"
                        @keydown="onTyping()"
                        placeholder="Ketik pesan... (Enter untuk kirim)"
                        rows="1"
                        class="flex-1 border border-gray-300 rounded-xl px-4 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400">
                    </textarea>
                    <button @click="sendMessage()"
                            class="bg-indigo-600 text-white rounded-xl px-4 py-2 text-sm hover:bg-indigo-700">
                        Kirim
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal kelola anggota --}}
        <div id="modal-members" class="hidden fixed inset-0 bg-black/40 z-50 flex items-center justify-center">
            <div class="bg-white rounded-xl p-6 w-96 shadow-xl max-h-screen overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-semibold text-gray-800">Kelola Anggota</h3>
                    <button onclick="document.getElementById('modal-members').classList.add('hidden')"
                            class="text-gray-400 hover:text-gray-600 text-xl">&times;</button>
                </div>
                <form action="{{ route('groups.members.store', $group) }}" method="POST" class="mb-4">
                    @csrf
                    <div class="flex gap-2">
                        <input name="email" type="email" placeholder="Email pengguna"
                               class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        <button class="bg-indigo-600 text-white px-3 py-2 rounded-lg text-sm">Tambah</button>
                    </div>
                </form>
                <div class="space-y-2">
                    @foreach($members as $member)
                    <div class="flex items-center justify-between py-2 border-b border-gray-100">
                        <div>
                            <p class="text-sm font-medium text-gray-700">{{ $member->name }}</p>
                            <p class="text-xs text-gray-400">{{ $member->pivot->role }}</p>
                        </div>
                        @if($group->created_by === auth()->id() && $member->id !== auth()->id())
                        <div class="flex gap-1">
                            @if($member->pivot->role === 'member')
                            <form action="{{ route('groups.members.promote', [$group, $member]) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="text-xs text-indigo-500 hover:underline">Jadikan admin</button>
                            </form>
                            @endif
                            <form action="{{ route('groups.members.destroy', [$group, $member]) }}" method="POST">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-400 hover:underline ml-2">Hapus</button>
                            </form>
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>