@extends('admin.layouts.app')
@section('title', 'Chat — ' . $conversation->visitor->display_name)
@section('chat', '1')

@php
    $visitor = $conversation->visitor;
    $statusClass = $conversation->status === 'active' ? 'bg-green-100 text-green-700' : ($conversation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-slate-100 text-slate-500');
    $replyIcon = '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>';
    $fileIcon  = '<svg class="w-5 h-5 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>';
@endphp

@section('content')
<div class="flex h-full lg:gap-4">

    {{-- Left: conversations list (desktop only; on mobile the Back button returns to the list page) --}}
    <aside class="hidden lg:flex w-80 shrink-0 flex-col bg-white border border-slate-100 rounded-2xl overflow-hidden shadow-sm"
           x-data="convList({{ $conversation->id }})">
        <div class="flex items-center justify-between px-4 h-14 border-b border-slate-100 shrink-0">
            <span class="font-semibold text-slate-900">Live Chats</span>
            <span class="unread-dot" x-show="totalUnread > 0" x-cloak x-text="totalUnread > 9 ? '9+' : totalUnread"></span>
        </div>

        <div x-show="newChatAlert" x-transition x-cloak
             class="mx-3 mt-2 flex items-center gap-2 bg-green-50 border border-green-200 rounded-xl px-3 py-2 text-xs text-green-700 shrink-0">
            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse shrink-0"></span>
            <span>New conversation!</span>
            <button @click="newChatAlert=false" class="ml-auto text-green-500 hover:text-green-700 cursor-pointer">✕</button>
        </div>

        <div class="flex-1 overflow-y-auto thin-scroll">
            <template x-for="conv in conversations" :key="conv.id">
                <a :href="conv.url" class="chat-list-item" :class="conv.id === activeId && 'is-active'">
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 bg-brand-100 text-brand-700 rounded-full grid place-items-center font-semibold text-sm" x-text="conv.initial"></div>
                        <span x-show="conv.is_online" class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-sm truncate" :class="conv.unread > 0 ? 'font-bold text-slate-900' : 'font-semibold text-slate-800'" x-text="conv.name"></span>
                            <span x-show="conv.unread > 0" class="unread-dot" x-text="conv.unread > 9 ? '9+' : conv.unread"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0"
                                  :class="conv.status === 'active' ? 'bg-green-500' : (conv.status === 'pending' ? 'bg-yellow-500' : 'bg-slate-300')"></span>
                            <p class="text-xs text-slate-500 truncate" x-text="conv.last_message || '…'"></p>
                        </div>
                    </div>
                </a>
            </template>
            <div x-show="conversations.length === 0" class="py-10 text-center text-slate-400 text-sm">No conversations</div>
        </div>
    </aside>

<div class="flex flex-1 min-w-0 lg:gap-4" x-data="adminChat({{ $conversation->id }})">

    {{-- Chat panel --}}
    <section class="flex flex-col flex-1 min-w-0 bg-white lg:rounded-2xl lg:border lg:border-slate-100 lg:shadow-sm overflow-hidden">

        {{-- Chat header --}}
        <header class="flex items-center gap-2 sm:gap-3 px-2 sm:px-4 h-14 lg:h-16 border-b border-slate-100 bg-white shrink-0">
            <a href="{{ route('admin.conversations.index') }}" class="icon-btn lg:hidden" aria-label="Back to chats">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <button type="button" @click="showInfo = true" class="flex items-center gap-3 min-w-0 flex-1 text-left cursor-pointer xl:cursor-default">
                <div class="relative shrink-0">
                    <div class="w-10 h-10 bg-brand-100 text-brand-700 rounded-full grid place-items-center font-semibold">
                        {{ strtoupper(substr($visitor->display_name, 0, 1)) }}
                    </div>
                    @if($visitor->is_online)
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-slate-900 text-sm truncate">{{ $visitor->display_name }}</p>
                    <p class="text-xs truncate" :class="visitorTyping ? 'text-brand-600 font-medium' : 'text-slate-500'"
                       x-text="visitorTyping ? 'typing…' : @js($visitor->is_online ? 'Online now' : 'Last seen ' . optional($visitor->last_activity_at)->diffForHumans())"></p>
                </div>
            </button>

            <span class="badge {{ $statusClass }} hidden sm:inline-flex">{{ ucfirst($conversation->status) }}</span>

            @if($conversation->status !== 'closed')
                <button @click="closeChat()" class="btn-secondary !px-3 !py-1.5 !text-xs" title="Close chat">
                    <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span class="hidden sm:inline">Close Chat</span>
                </button>
            @else
                <button @click="reopenChat()" class="btn-secondary !px-3 !py-1.5 !text-xs" title="Reopen chat">
                    <svg class="w-4 h-4 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span class="hidden sm:inline">Reopen</span>
                </button>
            @endif

            <button type="button" @click="showInfo = true" class="icon-btn xl:hidden" aria-label="Visitor info">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </button>
        </header>

        {{-- Messages --}}
        <div class="flex-1 overflow-y-auto thin-scroll chat-wallpaper px-3 sm:px-5 py-4 space-y-2" id="messages-container">
            @foreach($conversation->messages as $msg)
            @php $out = $msg->sender_type === 'admin'; @endphp
            <div class="group flex items-end gap-1.5 {{ $out ? 'justify-end' : 'justify-start' }}">
                @if($out)
                <button onclick="adminSetReply({{ $msg->id }}, {{ json_encode($msg->body ?: ($msg->attachment_name ?? '')) }}, '{{ $msg->sender_type }}')" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>
                @endif

                <div class="min-w-0 max-w-[85%] sm:max-w-[70%] flex flex-col {{ $out ? 'items-end' : 'items-start' }}">
                    @if($msg->body || $msg->replyTo)
                    <div class="{{ $out ? 'bubble-out' : ($msg->sender_type === 'bot' ? 'bubble-bot' : 'bubble-in') }}">
                        @if($msg->replyTo)
                        <span class="quote"><span class="font-semibold">{{ $msg->replyTo->sender_type === 'admin' ? 'You' : 'Visitor' }}</span> · {{ $msg->replyTo->body ? Str::limit($msg->replyTo->body, 60) : ($msg->replyTo->attachment_name ?? '📎 Attachment') }}</span>
                        @endif
                        @if($msg->body)<span class="msg-text">{{ $msg->body }}</span>@endif
                    </div>
                    @endif

                    @if($msg->attachment_url)
                        <div class="mt-1">
                            @if($msg->attachment_type === 'image')
                                <img src="{{ $msg->attachment_url }}" alt="{{ $msg->attachment_name }}"
                                    class="max-w-full sm:max-w-xs rounded-2xl max-h-64 object-cover cursor-pointer shadow-sm hover:opacity-90 transition-opacity"
                                    onclick="window.open('{{ $msg->attachment_url }}', '_blank')">
                            @elseif($msg->attachment_type === 'video')
                                <video src="{{ $msg->attachment_url }}" controls class="w-full sm:max-w-xs rounded-2xl max-h-64 bg-black"></video>
                            @else
                                <a href="{{ $msg->attachment_url }}" target="_blank" class="chat-file">
                                    {!! $fileIcon !!}<span class="truncate text-slate-700">{{ $msg->attachment_name }}</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <div class="msg-time flex items-center gap-1">
                        <span>{{ $msg->created_at->format('H:i') }}</span>
                        @if($out && $msg->is_read)
                            <svg class="w-3 h-3 text-brand-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.707 14.293l-3-3a1 1 0 00-1.414 1.414l3.5 3.5a1 1 0 001.414 0l7-7a1 1 0 00-1.414-1.414L9.707 14.293z"/></svg>
                        @endif
                    </div>
                </div>

                @unless($out)
                <button onclick="adminSetReply({{ $msg->id }}, {{ json_encode($msg->body ?: ($msg->attachment_name ?? '')) }}, '{{ $msg->sender_type }}')" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>
                @endunless
            </div>
            @endforeach

            {{-- New messages from polling --}}
            <template x-for="msg in newMessages" :key="msg.id">
                <div class="group flex items-end gap-1.5" :class="msg.sender_type === 'admin' ? 'justify-end' : 'justify-start'">
                    <button x-show="msg.sender_type === 'admin'" @click="setReply(msg)" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>

                    <div class="min-w-0 max-w-[85%] sm:max-w-[70%] flex flex-col" :class="msg.sender_type === 'admin' ? 'items-end' : 'items-start'">
                        <div x-show="msg.body || msg.reply_to"
                             :class="msg.sender_type === 'admin' ? 'bubble-out' : (msg.sender_type === 'bot' ? 'bubble-bot' : 'bubble-in')"><span x-show="msg.reply_to" class="quote"><span class="font-semibold" x-text="msg.reply_to?.sender_type === 'admin' ? 'You' : 'Visitor'"></span> · <span x-text="msg.reply_to?.body ? msg.reply_to.body.slice(0,60) : (msg.reply_to?.attachment_name || '📎 Attachment')"></span></span><span class="msg-text" x-text="msg.body"></span></div>
                        <div x-show="msg.attachment_url && msg.attachment_type === 'image'" class="mt-1">
                            <img :src="msg.attachment_url" class="max-w-full sm:max-w-xs rounded-2xl max-h-64 object-cover cursor-pointer shadow-sm" @click="window.open(msg.attachment_url, '_blank')" />
                        </div>
                        <div x-show="msg.attachment_url && msg.attachment_type === 'video'" class="mt-1">
                            <video :src="msg.attachment_url" controls class="w-full sm:max-w-xs rounded-2xl max-h-64 bg-black"></video>
                        </div>
                        <div x-show="msg.attachment_url && msg.attachment_type !== 'image' && msg.attachment_type !== 'video'" class="mt-1">
                            <a :href="msg.attachment_url" target="_blank" class="chat-file">
                                {!! $fileIcon !!}<span class="truncate text-slate-700" x-text="msg.attachment_name || 'Attachment'"></span>
                            </a>
                        </div>
                        <p class="msg-time" x-text="formatTime(msg.created_at)"></p>
                    </div>

                    <button x-show="msg.sender_type !== 'admin'" @click="setReply(msg)" class="reply-btn" title="Reply">{!! $replyIcon !!}</button>
                </div>
            </template>

            {{-- Typing indicator --}}
            <div x-show="visitorTyping" x-transition x-cloak class="flex justify-start pt-1">
                <div class="bubble-in !py-3">
                    <div class="flex gap-1">
                        <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce [animation-delay:0ms]"></span>
                        <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce [animation-delay:150ms]"></span>
                        <span class="w-2 h-2 bg-slate-400 rounded-full animate-bounce [animation-delay:300ms]"></span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Composer --}}
        <div class="border-t border-slate-100 bg-white px-2 sm:px-4 pt-2 pb-[max(0.5rem,env(safe-area-inset-bottom))] sm:pb-3 relative shrink-0">

            {{-- Canned replies --}}
            <div x-show="showCanned" x-cloak class="mb-2 border border-slate-200 rounded-2xl overflow-hidden shadow-lg max-h-56 overflow-y-auto thin-scroll bg-white">
                <div class="p-2 border-b border-slate-100 sticky top-0 bg-white">
                    <input type="text" x-model="cannedSearch" @input="searchCanned()" placeholder="Search replies…"
                        class="w-full text-sm px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-brand-500">
                </div>
                <template x-for="reply in cannedResults" :key="reply.id">
                    <button @click="useCanned(reply)" class="w-full text-left px-4 py-2.5 hover:bg-brand-50 transition-colors border-b border-slate-50 last:border-0 cursor-pointer">
                        <p class="text-sm font-medium text-slate-800" x-text="reply.title"></p>
                        <p class="text-xs text-slate-500 truncate" x-text="reply.body"></p>
                    </button>
                </template>
                <div x-show="cannedResults.length === 0" class="px-4 py-3 text-sm text-slate-400 text-center">No replies found</div>
            </div>

            {{-- Reply-to preview --}}
            <div x-show="replyTo" x-cloak class="mb-2 flex items-center gap-2 pl-3 pr-2 py-2 bg-brand-50 border-l-4 border-brand-500 rounded-xl">
                <div class="flex-1 min-w-0">
                    <p class="text-[11px] font-semibold text-brand-600" x-text="replyTo?.sender_type === 'admin' ? 'Replying to yourself' : 'Replying to Visitor'"></p>
                    <p class="text-xs text-slate-600 truncate" x-text="replyTo?.body ? replyTo.body.slice(0,80) : (replyTo?.attachment_name || '📎 Attachment')"></p>
                </div>
                <button @click="clearReply()" class="icon-btn !p-1 hover:!text-red-500" aria-label="Cancel reply">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- File preview + upload progress --}}
            <div x-show="filePreview" x-cloak class="mb-2 p-2.5 bg-slate-50 rounded-xl border border-slate-200">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-brand-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    <span class="text-xs text-slate-700 truncate flex-1" x-text="filePreview"></span>
                    <span x-show="uploadProgress > 0 && uploadProgress < 100" class="text-xs font-semibold text-brand-600 shrink-0" x-text="uploadProgress + '%'"></span>
                    <button @click="clearFile()" x-show="uploadProgress === 0" class="text-slate-400 hover:text-red-500 transition-colors cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div x-show="uploadProgress > 0 && uploadProgress < 100" class="mt-1.5 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full bg-brand-500 rounded-full transition-all duration-200" :style="`width:${uploadProgress}%`"></div>
                </div>
            </div>

            {{-- Input row --}}
            <div class="flex items-end gap-2">
                <div class="composer flex-1 min-w-0">
                    {{-- Emoji --}}
                    <div class="relative shrink-0 self-end">
                        <button @click="showEmoji=!showEmoji" type="button" aria-label="Emoji"
                            :class="showEmoji ? 'bg-brand-50' : 'hover:bg-slate-100'"
                            class="grid place-items-center w-9 h-9 rounded-full text-xl leading-none transition-colors cursor-pointer">😊</button>
                        <div x-show="showEmoji" x-cloak @click.outside="showEmoji=false"
                            class="absolute z-50 bottom-[calc(100%+8px)] left-0 w-[min(300px,calc(100vw-2rem))] border border-slate-200 rounded-2xl shadow-xl bg-white">
                            <div class="flex gap-1 p-2 border-b border-slate-100">
                                <template x-for="cat in emojiCats" :key="cat.key">
                                    <button @click="emojiCategory=cat.key" type="button"
                                        :class="emojiCategory===cat.key ? 'bg-brand-100 text-brand-700' : 'hover:bg-slate-100 text-slate-500'"
                                        class="px-2 py-1 rounded-lg text-base transition-colors cursor-pointer" x-text="cat.icon"></button>
                                </template>
                            </div>
                            <div class="p-2 overflow-y-auto thin-scroll grid grid-cols-8 sm:grid-cols-9 gap-0.5 max-h-40">
                                <template x-for="e in emojiList()" :key="e">
                                    <button @click="insertEmoji(e)" type="button" class="text-xl p-1 rounded hover:bg-slate-100 transition-colors leading-none cursor-pointer" x-text="e"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <textarea x-model="message" x-ref="msgInput"
                        @keydown.enter="if (!$event.shiftKey && window.innerWidth >= 1024) { $event.preventDefault(); send(); }"
                        @input="handleTyping()" @keyup.slash="showCannedMenu()"
                        rows="1" placeholder="Type a message…"
                        class="flex-1 min-w-0 resize-none bg-transparent py-2 text-[15px] sm:text-sm focus:outline-none text-slate-800 placeholder-slate-400 max-h-32 min-h-[2.25rem]"
                        oninput="this.style.height='auto';this.style.height=Math.min(this.scrollHeight,128)+'px'"></textarea>

                    <input type="file" id="file-input" class="hidden" @change="handleFile($event)" accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.xml,.zip,.mp4,.txt">
                    <button @click="document.getElementById('file-input').click()" type="button" aria-label="Attach file"
                        class="grid place-items-center w-9 h-9 shrink-0 self-end rounded-full text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                    </button>
                    <button @click="toggleCanned()" type="button" aria-label="Canned replies" title="Canned replies (or type /)"
                        :class="showCanned ? 'bg-brand-50 text-brand-600' : 'text-slate-400 hover:text-slate-600 hover:bg-slate-100'"
                        class="grid place-items-center w-9 h-9 shrink-0 self-end rounded-full transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h7"/></svg>
                    </button>
                </div>
                <button @click="send()" :disabled="sending" type="button" class="send-btn mb-0.5" aria-label="Send">
                    <svg class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                </button>
            </div>
        </div>
    </section>

    {{-- Visitor details: static column on xl, slide-over drawer below that --}}
    <div x-show="showInfo" x-cloak @click="showInfo = false" x-transition.opacity class="fixed inset-0 z-40 bg-slate-900/40 xl:hidden"></div>
    <aside class="fixed inset-y-0 right-0 z-50 w-[min(20rem,90vw)] bg-slate-50 p-4 overflow-y-auto thin-scroll space-y-4 shadow-2xl transition-transform duration-300 xl:static xl:z-auto xl:w-72 xl:shrink-0 xl:p-0 xl:bg-transparent xl:shadow-none xl:translate-x-0"
           :class="showInfo ? 'translate-x-0' : 'translate-x-full'">

        <div class="flex items-center justify-between xl:hidden">
            <p class="font-semibold text-slate-900">Visitor details</p>
            <button type="button" @click="showInfo = false" class="icon-btn" aria-label="Close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <div class="card !p-5">
            <div class="flex flex-col items-center text-center mb-4">
                <div class="w-16 h-16 bg-brand-100 text-brand-700 rounded-full grid place-items-center text-2xl font-semibold mb-2">
                    {{ strtoupper(substr($visitor->display_name, 0, 1)) }}
                </div>
                <p class="font-semibold text-slate-900">{{ $visitor->display_name }}</p>
                <span class="badge {{ $statusClass }} mt-1">{{ ucfirst($conversation->status) }}</span>
            </div>
            <div class="space-y-2.5 text-sm">
                @foreach([
                    ['IP', $visitor->ip_address],
                    ['Country', $visitor->country],
                    ['City', $visitor->city],
                    ['Browser', $visitor->browser],
                    ['OS', $visitor->os],
                    ['Device', ucfirst($visitor->device)],
                    ['Name', $visitor->name],
                ] as [$label, $value])
                    @if($value)
                    <div class="flex justify-between gap-3">
                        <span class="text-slate-500 shrink-0">{{ $label }}</span>
                        <span class="font-medium text-slate-800 text-right truncate">{{ $value }}</span>
                    </div>
                    @endif
                @endforeach

                @if($visitor->email)
                <div class="flex justify-between gap-3">
                    <span class="text-slate-500 shrink-0">Email</span>
                    <a href="mailto:{{ $visitor->email }}" class="font-medium text-brand-600 break-all text-right">{{ $visitor->email }}</a>
                </div>
                @endif

                @if($visitor->whatsapp)
                <div class="flex justify-between gap-3 items-center">
                    <span class="text-slate-500 shrink-0">WhatsApp</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $visitor->whatsapp) }}" target="_blank" rel="noopener"
                       class="font-medium text-green-600 hover:text-green-700 text-right">{{ $visitor->whatsapp }}</a>
                </div>
                @endif
            </div>
        </div>

        <div class="card !p-5">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Session</h3>
            <div class="space-y-2 text-sm">
                <div>
                    <span class="text-slate-500 block">Landing Page</span>
                    <span class="text-xs text-slate-700 break-all">{{ $visitor->landing_page }}</span>
                </div>
                <div>
                    <span class="text-slate-500 block">Current Page</span>
                    <span class="text-xs text-slate-700 break-all">{{ $visitor->current_page }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Started</span>
                    <span class="text-slate-800">{{ $conversation->created_at->format('M j, H:i') }}</span>
                </div>
            </div>
        </div>

        @if($visitor->logs->isNotEmpty())
        <div class="card !p-5">
            <h3 class="font-semibold text-slate-900 text-sm mb-3">Page History</h3>
            <div class="space-y-1.5">
                @foreach($visitor->logs->take(8) as $log)
                <div class="text-xs">
                    <p class="text-slate-700 truncate">{{ $log->page_title ?: $log->page_url }}</p>
                    <p class="text-slate-400">{{ $log->visited_at->format('H:i') }}</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </aside>

</div>{{-- /adminChat --}}
</div>{{-- /outer flex --}}
@endsection

@push('scripts')
<script>
// Bridge so static Blade buttons can call Alpine's setReply
function adminSetReply(id, body, senderType) {
    const el = document.querySelector('[x-data].__x');
    if (window._adminChatInstance) window._adminChatInstance.setReply({ id, body, sender_type: senderType });
}

function adminChat(conversationId) {
    return {
        message: '',
        showInfo: false,
        sending: false,
        uploadProgress: 0,
        newMessages: [],
        lastMessageId: {{ $conversation->messages->last()?->id ?? 0 }},
        visitorTyping: false,
        typingTimer: null,
        sendingTyping: false,
        showCanned: false,
        cannedSearch: '',
        cannedResults: [],
        fileInput: null,
        selectedFile: null,
        filePreview: '',
        replyTo: null,
        showEmoji: false,
        emojiCategory: 'smileys',
        emojiCats: [
            {key:'smileys', icon:'😊'}, {key:'gestures', icon:'👍'},
            {key:'travel', icon:'✈️'}, {key:'objects', icon:'💼'}, {key:'symbols', icon:'❤️'}
        ],
        emojiData: {
            smileys:  ['😀','😃','😄','😁','😆','😅','😂','🤣','😊','😇','🙂','🙃','😉','😌','😍','🥰','😘','😗','😙','😚','😋','😛','😝','😜','🤪','🤨','🧐','🤓','😎','🥸','🤩','🥳','😏','😒','😞','😔','😟','😕','🙁','☹️','😣','😖','😫','😩','🥺','😢','😭','😤','😠','😡'],
            gestures: ['👍','👎','👌','🤌','🤏','✌️','🤞','🤟','🤘','🤙','👈','👉','👆','👇','☝️','👋','🤚','🖐️','✋','🖖','🤝','🙏','✍️','💪','👀','👅','👄','🫶','🤲','👐'],
            travel:   ['✈️','🚀','🛸','🚁','🛶','⛵','🚢','🚂','🚄','🚗','🚕','🚌','🚎','🏖️','🏔️','🗺️','🧭','🏕️','🌍','🌎','🌏','🗼','🗽','🏰','🏯','🎡','🎢','🎠','⛽','🚦'],
            objects:  ['💼','💻','📱','⌨️','🖥️','📷','📹','🎥','📞','☎️','📺','📻','⏱️','⌚','📦','📫','✏️','📝','📁','📂','📅','💡','🔦','🔋','🔌','🛠️','🔧','🔨','🗝️','🔐'],
            symbols:  ['❤️','🧡','💛','💚','💙','💜','🖤','🤍','💔','❣️','💕','💞','💓','💗','💖','💘','💝','☮️','✝️','☪️','✡️','☯️','⭐','🌟','💫','✨','🔥','💥','🎉','🎊'],
        },

        init() {
            window._adminChatInstance = this;
            this.$nextTick(() => this.scrollToBottom());
            this.startPolling();
            this.loadCannedReplies();
        },

        startPolling() {
            setInterval(() => this.pollMessages(), 3000);
        },

        pollMessages() {
            fetch(`{{ url('/') }}/admin/conversations/${conversationId}/messages?after_id=${this.lastMessageId}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.messages && data.messages.length) {
                    data.messages.forEach(m => {
                        if (!this.newMessages.find(n => n.id === m.id)) {
                            this.newMessages.push(m);
                            this.lastMessageId = Math.max(this.lastMessageId, m.id);
                        }
                    });
                    this.$nextTick(() => this.scrollToBottom());
                    if (data.messages.some(m => m.sender_type === 'visitor')) {
                        this.playSound();
                    }
                }
            })
            .catch(() => {});
        },

        send() {
            if ((!this.message.trim() && !this.selectedFile) || this.sending) return;

            this.sending = true;
            this.uploadProgress = 0;
            const formData = new FormData();
            if (this.message.trim()) formData.append('body', this.message.trim());
            if (this.selectedFile) formData.append('attachment', this.selectedFile);
            if (this.replyTo) formData.append('reply_to_id', this.replyTo.id);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            const self = this;
            const xhr = new XMLHttpRequest();
            xhr.open('POST', `{{ url('/') }}/admin/conversations/${conversationId}/message`);

            xhr.upload.addEventListener('progress', e => {
                if (e.lengthComputable) self.uploadProgress = Math.round((e.loaded / e.total) * 100);
            });

            xhr.onload = function () {
                self.uploadProgress = 0;
                self.sending = false;
                try {
                    const data = JSON.parse(xhr.responseText);
                    if (data.message) {
                        self.newMessages.push({
                            id: data.message.id,
                            sender_type: 'admin',
                            body: data.message.body,
                            attachment_url: data.attachment_url,
                            attachment_name: data.message.attachment_name,
                            attachment_type: data.message.attachment_type,
                            created_at: data.message.created_at,
                            reply_to: self.replyTo ? { id: self.replyTo.id, body: self.replyTo.body, sender_type: self.replyTo.sender_type } : null,
                        });
                        self.lastMessageId = Math.max(self.lastMessageId, data.message.id);
                        self.$nextTick(() => self.scrollToBottom());
                    }
                } catch(e) {}
                self.message = '';
                self.clearFile();
                self.clearReply();
            };
            xhr.onerror = () => { self.sending = false; self.uploadProgress = 0; };
            xhr.send(formData);
        },

        setReply(msg) {
            this.replyTo = { id: msg.id, body: msg.body, sender_type: msg.sender_type, attachment_name: msg.attachment_name };
            this.$nextTick(() => this.$refs.msgInput?.focus());
        },

        clearReply() {
            this.replyTo = null;
        },

        handleTyping() {
            if (!this.sendingTyping) {
                this.sendingTyping = true;
                fetch(`{{ url('/') }}/admin/conversations/${conversationId}/typing`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ typing: true }),
                }).finally(() => { this.sendingTyping = false; });
            }
            clearTimeout(this.typingTimer);
            this.typingTimer = setTimeout(() => {
                fetch(`{{ url('/') }}/admin/conversations/${conversationId}/typing`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ typing: false }),
                });
            }, 2000);
        },

        closeChat() {
            if (!confirm('Close this conversation?')) return;
            fetch(`{{ url('/') }}/admin/conversations/${conversationId}/close`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(() => location.reload());
        },

        reopenChat() {
            fetch(`{{ url('/') }}/admin/conversations/${conversationId}/reopen`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            }).then(() => location.reload());
        },

        loadCannedReplies() {
            fetch('{{ url('/') }}/admin/canned-replies/search')
                .then(r => r.json())
                .then(data => { this.cannedResults = data; });
        },

        searchCanned() {
            fetch(`{{ url('/') }}/admin/canned-replies/search?q=${encodeURIComponent(this.cannedSearch)}`)
                .then(r => r.json())
                .then(data => { this.cannedResults = data; });
        },

        toggleCanned() {
            this.showCanned = !this.showCanned;
            if (this.showCanned) this.loadCannedReplies();
        },

        showCannedMenu() {
            this.showCanned = true;
            this.loadCannedReplies();
        },

        useCanned(reply) {
            this.message = reply.body;
            this.showCanned = false;
        },

        emojiList() {
            return this.emojiData[this.emojiCategory] || [];
        },

        insertEmoji(emoji) {
            const ta = this.$refs.msgInput;
            const start = ta.selectionStart;
            const end = ta.selectionEnd;
            this.message = this.message.slice(0, start) + emoji + this.message.slice(end);
            this.$nextTick(() => {
                ta.selectionStart = ta.selectionEnd = start + emoji.length;
                ta.focus();
            });
            this.showEmoji = false;
        },

        handleFile(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.selectedFile = file;
            this.filePreview = file.name;
        },

        clearFile() {
            this.selectedFile = null;
            this.filePreview = '';
            document.getElementById('file-input').value = '';
        },

        scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) container.scrollTop = container.scrollHeight;
        },

        formatTime(iso) {
            const d = new Date(iso);
            return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },

        playSound() {
            if (!{{ \App\Models\WidgetSetting::get('sound_enabled', 'true') === 'true' ? 'true' : 'false' }}) return;
            try {
                const audio = new Audio('{{ url('/') }}/voice/chat.wav');
                audio.volume = 0.7;
                audio.play().catch(() => {});
            } catch(e) {}
        }
    }
}

function convList(activeId) {
    return {
        activeId,
        conversations: [],
        totalUnread: 0,
        newChatAlert: false,
        prevIds: null,

        init() {
            this.load();
            setInterval(() => this.load(), 5000);
        },

        load() {
            fetch('{{ url('/') }}/admin/conversations/list', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(r => r.json())
                .then(data => {
                    const list = data.conversations || [];

                    if (this.prevIds !== null) {
                        const prevSet = new Set(this.prevIds);
                        const hasNew = list.some(c => !prevSet.has(c.id));
                        if (hasNew) {
                            this.newChatAlert = true;
                            if ({{ \App\Models\WidgetSetting::get('sound_enabled', 'true') === 'true' ? 'true' : 'false' }}) {
                                try { new Audio('{{ url('/') }}/voice/newvisitor.mp3').play().catch(()=>{}); } catch(e) {}
                            }
                            setTimeout(() => { this.newChatAlert = false; }, 6000);
                        }
                    }

                    this.prevIds = list.map(c => c.id);
                    this.conversations = list;
                    this.totalUnread = list.reduce((s, c) => s + (c.unread || 0), 0);
                })
                .catch(() => {});
        }
    }
}
</script>
@endpush
