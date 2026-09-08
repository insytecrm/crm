@php
    $initialMessages = $conversation
        ? $conversation->messages->map(fn ($message): array => [
            'role' => $message->role,
            'content' => $message->content,
            'actions' => $message->actions ?? [],
        ])->values()->all()
        : [];
@endphp

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('insyteAiChat', (config) => ({
            chatUrl: config.chatUrl,
            conversationId: config.conversationId,
            prompt: config.starterPrompt || '',
            messages: config.messages || [],
            sending: false,
            error: null,

            useExample(text) {
                this.prompt = text;
                this.$nextTick(() => this.$refs.input?.focus());
            },

            async send() {
                const message = (this.prompt || '').trim();

                if (message === '' || this.sending) {
                    return;
                }

                this.sending = true;
                this.error = null;
                this.messages.push({ role: 'user', content: message, actions: [] });
                this.prompt = '';

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                try {
                    const response = await fetch(this.chatUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            message,
                            conversation_id: this.conversationId,
                        }),
                    });

                    const data = await response.json().catch(() => ({}));

                    if (! response.ok) {
                        this.error = data.message || data.errors?.message?.[0] || @json(__('Could not send that prompt.'));
                        this.sending = false;

                        return;
                    }

                    this.conversationId = data.conversation_id;
                    this.messages.push({
                        role: 'assistant',
                        content: data.reply,
                        actions: data.actions || [],
                    });
                } catch {
                    this.error = @json(__('Could not send that prompt.'));
                } finally {
                    this.sending = false;
                }
            },
        }));
    });
</script>

<div
    class="overflow-hidden rounded-2xl border border-violet-100 bg-gradient-to-b from-violet-50/50 via-white to-white shadow-sm shadow-slate-200/40"
    x-data="insyteAiChat({
        chatUrl: @js($chatUrl),
        conversationId: @js($conversation?->id),
        starterPrompt: @js($starterPrompt),
        messages: @js($initialMessages),
    })"
    @insyte-ai-example.window="useExample($event.detail)"
>
    <div class="flex flex-col gap-3 px-4 pt-4 sm:px-5" x-show="messages.length > 0" x-cloak>
        <template x-for="(item, index) in messages" :key="index">
            <div :class="item.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
                <div
                    :class="item.role === 'user'
                        ? 'max-w-[85%] rounded-2xl bg-navy px-3 py-2 text-sm text-white shadow-sm'
                        : 'max-w-[85%] rounded-2xl border border-violet-100 bg-white px-3 py-2 text-sm text-slate-800 shadow-sm'"
                >
                    <p class="whitespace-pre-wrap" x-text="item.content"></p>
                    <template x-if="item.actions && item.actions.length">
                        <div class="mt-2 flex flex-col gap-1">
                            <template x-for="(action, actionIndex) in item.actions" :key="actionIndex">
                                <a
                                    x-show="action.lead_url"
                                    :href="action.lead_url"
                                    class="inline-flex text-xs font-semibold text-brand-accent underline-offset-2 hover:underline"
                                >{{ __('Open lead') }}</a>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <p class="px-4 pt-3 text-sm text-red-600 sm:px-5" x-show="error" x-text="error" x-cloak></p>

    <form class="p-4 sm:p-5" @submit.prevent="send">
        <div class="rounded-2xl border border-violet-100 bg-white shadow-inner shadow-violet-50/80 focus-within:border-navy/40 focus-within:ring-2 focus-within:ring-navy/10">
            <label class="sr-only" for="insyte-ai-prompt">{{ __('Ask InSyte AI OS') }}</label>
            <textarea
                id="insyte-ai-prompt"
                x-ref="input"
                x-model="prompt"
                rows="4"
                maxlength="4000"
                class="block w-full resize-none border-0 bg-transparent px-4 pt-4 text-sm text-black placeholder:text-slate-400 focus:ring-0"
                placeholder="{{ __('Ask me anything or give me a task... e.g. Schedule a site visit for Rahul Sharma on 12 Sep at 4 PM') }}"
                :disabled="sending"
            ></textarea>
            <div class="flex items-center justify-between gap-3 px-3 pb-3">
                <div class="flex items-center gap-1">
                    <button type="button" class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400" disabled aria-hidden="true">
                        @include('tenant.ai.partials.glyph', ['icon' => 'paperclip', 'class' => 'size-4'])
                    </button>
                    <button type="button" class="inline-flex size-9 items-center justify-center rounded-lg text-slate-400" disabled aria-hidden="true">
                        @include('tenant.ai.partials.glyph', ['icon' => 'mic', 'class' => 'size-4'])
                    </button>
                </div>
                <x-ui.button type="submit" x-bind:disabled="sending || ! prompt.trim()">
                    @include('tenant.ai.partials.glyph', ['icon' => 'send', 'class' => 'size-4'])
                    <span x-show="! sending">{{ __('Send') }}</span>
                    <span x-show="sending" x-cloak>{{ __('Working…') }}</span>
                </x-ui.button>
            </div>
        </div>
    </form>
</div>
