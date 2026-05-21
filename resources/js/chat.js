function chatApp(groupId, currentUserId, csrfToken) {
    return {
        groupId,
        currentUserId,
        csrfToken,
        newMessage: '',
        messages: [],
        replyTo: null,
        selectedFile: null,
        typingUsers: {},
        typingText: '',
        userStatuses: {},
        onlineUsers: [],
        typingTimer: null,
        isSending: false, // FIX: flag untuk mencegah double submit

        init() {
            this.scrollToBottom();
            this.listenEcho();
            this.setOnline();
            this.markAllRead();
        },

        setOnline() {
            fetch('/user/status', {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ status: 'online' }),
            });

            window.addEventListener('beforeunload', () => {
                navigator.sendBeacon('/user/status', new Blob([
                    JSON.stringify({ status: 'offline', _method: 'PATCH', _token: this.csrfToken })
                ], { type: 'application/json' }));
            });
        },

        listenEcho() {
            window.Echo.private(`group.${this.groupId}`)
                .listen('MessageSent', (e) => {
                    // FIX: hanya append jika pesan belum ada di DOM (cegah duplikat dari Echo)
                    if (!this.messageExists(e.message.id)) {
                        this.appendMessage(e.message);
                        this.scrollToBottom();
                    }
                })
                .listen('MessageReactionUpdated', (e) => {
                    this.updateReactions(e.message_id, e.reactions);
                })
                .listen('UserTyping', (e) => {
                    if (e.user_id === this.currentUserId) return;
                    if (e.is_typing) {
                        this.typingUsers[e.user_id] = e.user_name;
                    } else {
                        delete this.typingUsers[e.user_id];
                    }
                    this.updateTypingText();
                });

            window.Echo.channel('user-status')
                .listen('UserStatusChanged', (e) => {
                    this.userStatuses[e.user_id] = e.status;
                    if (e.status === 'online') {
                        if (!this.onlineUsers.includes(e.user_id)) {
                            this.onlineUsers.push(e.user_id);
                        }
                    } else {
                        this.onlineUsers = this.onlineUsers.filter(id => id !== e.user_id);
                    }
                });
        },

        // FIX: helper untuk cek apakah pesan sudah ada di DOM
        messageExists(messageId) {
            return !!document.querySelector(`[data-message-id="${messageId}"]`);
        },

        updateTypingText() {
            const names = Object.values(this.typingUsers);
            if (names.length === 0)      this.typingText = '';
            else if (names.length === 1) this.typingText = `${names[0]} sedang mengetik...`;
            else                         this.typingText = `${names.slice(0, -1).join(', ')} dan ${names.at(-1)} sedang mengetik...`;
        },

        onTyping() {
            clearTimeout(this.typingTimer);
            this.broadcastTyping(true);
            this.typingTimer = setTimeout(() => this.broadcastTyping(false), 2000);
        },

        broadcastTyping(isTyping) {
            fetch(`/groups/${this.groupId}/messages/typing`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ is_typing: isTyping }),
            });
        },

        async sendMessage() {
            if (!this.newMessage.trim() && !this.selectedFile) return;

            // FIX: cegah double submit jika tombol ditekan berkali-kali / Enter spam
            if (this.isSending) return;
            this.isSending = true;

            const formData = new FormData();
            if (this.newMessage.trim()) formData.append('body', this.newMessage.trim());
            if (this.replyTo)           formData.append('reply_to_id', this.replyTo.id);
            if (this.selectedFile)      formData.append('file', this.selectedFile);

            // FIX: reset input lebih awal untuk mencegah user kirim ulang saat menunggu
            const messageCopy = this.newMessage;
            const replyToCopy = this.replyTo;
            this.newMessage   = '';
            this.replyTo      = null;
            this.selectedFile = null;

            try {
                const res = await fetch(`/groups/${this.groupId}/messages`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken },
                    body: formData,
                });

                if (!res.ok) {
                    // FIX: kembalikan pesan jika gagal
                    this.newMessage = messageCopy;
                    this.replyTo    = replyToCopy;
                    return;
                }

                const data = await res.json();

                // FIX: append dari HTTP response HANYA jika belum ada (Echo pakai toOthers()
                // sehingga pengirim tidak terima dari Echo, tapi guard ini untuk keamanan ekstra)
                if (!this.messageExists(data.message.id)) {
                    this.appendMessage(data.message);
                }

                this.scrollToBottom();
                this.broadcastTyping(false);
            } finally {
                // FIX: selalu reset flag setelah selesai
                this.isSending = false;
            }
        },

        appendMessage(msg) {
            const isMine = msg.user.id === this.currentUserId;
            const container = document.getElementById('messages-container');
            if (!container) return;

            // FIX: guard duplikat — jangan append jika sudah ada
            if (this.messageExists(msg.id)) return;

            const wrapper = document.createElement('div');
            wrapper.className = `message-item flex gap-3 ${isMine ? 'flex-row-reverse' : ''}`;
            wrapper.dataset.messageId = msg.id;

            const avatar = `<div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-600 shrink-0">
                ${msg.user.name.charAt(0).toUpperCase()}
            </div>`;

            const replyHtml = msg.reply_to ? `
                <div class="text-xs bg-gray-200 rounded px-2 py-1 mb-1 border-l-2 border-indigo-400">
                    <span class="font-medium">${msg.reply_to.user.name}:</span> ${msg.reply_to.body?.substring(0, 60) ?? ''}
                </div>` : '';

            const bubbleClass = isMine ? 'bg-indigo-600 text-white' : 'bg-white text-gray-800 shadow-sm';

            wrapper.innerHTML = `
                ${avatar}
                <div class="max-w-xs lg:max-w-md">
                    ${replyHtml}
                    <div class="rounded-2xl px-4 py-2 ${bubbleClass}">
                        ${!isMine ? `<p class="text-xs font-medium mb-1 text-indigo-600">${msg.user.name}</p>` : ''}
                        ${msg.body ? `<p class="text-sm">${msg.body}</p>` : ''}
                        <p class="text-xs mt-1 ${isMine ? 'text-indigo-200' : 'text-gray-400'}">
                            ${new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-1 mt-1 reactions-${msg.id}"></div>
                    <div class="flex gap-2 mt-1">
                        ${['👍','❤️','😂','😮','😢'].map(e =>
                            `<button onclick="window.toggleReaction(${msg.id},'${e}')" class="text-xs text-gray-400 hover:text-gray-600">${e}</button>`
                        ).join('')}
                    </div>
                </div>`;

            container.appendChild(wrapper);
        },

        updateReactions(messageId, reactions) {
            const container = document.querySelector(`.reactions-${messageId}`);
            if (!container) return;
            container.innerHTML = reactions.map(r => `
                <button onclick="window.toggleReaction(${messageId}, '${r.emoji}')"
                        class="text-xs bg-white border border-gray-200 rounded-full px-2 py-0.5 hover:bg-gray-50">
                    ${r.emoji} ${r.count}
                </button>
            `).join('');
        },

        markAllRead() {
            document.querySelectorAll('[data-message-id]').forEach(el => {
                const id = el.dataset.messageId;
                fetch(`/messages/${id}/read`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken },
                });
            });
        },

        handleFile(e) {
            this.selectedFile = e.target.files[0] ?? null;
        },

        scrollToBottom() {
            this.$nextTick(() => {
                const c = document.getElementById('messages-container');
                if (c) c.scrollTop = c.scrollHeight;
            });
        },
    };
}

// Expose ke window agar Alpine bisa akses dari x-data="chatApp(...)"
window.chatApp = chatApp;

window.toggleReaction = function(messageId, emoji) {
    fetch(`/messages/${messageId}/reactions`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ emoji }),
    });
};