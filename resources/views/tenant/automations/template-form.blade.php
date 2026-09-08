@php
    $isEditing = $template !== null;
    $title = $isEditing ? __('Edit template') : __('Create template');
    $activeValue = old('is_active');
    $templateIsActive = $activeValue === null
        ? (bool) ($template?->is_active ?? true)
        : filter_var($activeValue, FILTER_VALIDATE_BOOLEAN);
@endphp

<x-tenant-layout :title="$title . ' | InSyte CRM'">
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('messageTemplateEditor', (config) => ({
                channel: config.channel,
                subject: config.subject || '',
                body: config.body || '',
                lastField: 'body',
                samples: config.samples || {},

                insert(token) {
                    const field = this.lastField === 'subject' && this.channel === 'email' ? 'subject' : 'body';
                    const input = field === 'subject' ? this.$refs.subject : this.$refs.body;
                    const current = this[field] || '';
                    const start = input?.selectionStart ?? current.length;
                    const end = input?.selectionEnd ?? start;

                    this[field] = current.slice(0, start) + token + current.slice(end);

                    this.$nextTick(() => {
                        input?.focus();
                        const position = start + token.length;
                        input?.setSelectionRange(position, position);
                    });
                },

                preview(text) {
                    return (text || '').replace(/\{\{([a-z0-9_.]+)\}\}/g, (match, key) => {
                        return Object.prototype.hasOwnProperty.call(this.samples, key) ? this.samples[key] : match;
                    });
                },
            }));
        });
    </script>

    <div
        x-data="messageTemplateEditor({
            channel: @js(old('channel', $template?->channel->value ?? \App\Enums\MessageTemplateChannel::WhatsApp->value)),
            subject: @js(old('subject', $template?->subject ?? '')),
            body: @js(old('body', $template?->body ?? '')),
            samples: @js($sampleValues),
        })"
        class="flex flex-col gap-5"
    >
        <div class="flex flex-wrap items-center gap-2">
            <a
                href="{{ route('tenant.automations.templates') }}"
                class="inline-flex size-8 shrink-0 items-center justify-center rounded-md text-slate-500 hover:bg-slate-50 hover:text-black"
                aria-label="{{ __('Back to templates') }}"
            >
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <h1 class="text-lg font-semibold text-black">{{ $title }}</h1>
                <p class="text-sm text-slate-500">{{ __('Insert lead, user, company, property, and booking details as variables.') }}</p>
            </div>
        </div>

        <x-auth-session-status class="rounded-lg bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-700" :status="session('status')" />

        <form
            method="POST"
            action="{{ $isEditing ? route('tenant.automations.templates.update', $template) : route('tenant.automations.templates.store') }}"
            class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_20rem]"
        >
            @csrf
            @if ($isEditing)
                @method('PATCH')
            @endif

            <div class="flex flex-col gap-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex flex-col gap-4">
                        <div>
                            <label for="template_name" class="block text-sm font-medium text-black">{{ __('Name') }}</label>
                            <input
                                id="template_name"
                                type="text"
                                name="name"
                                value="{{ old('name', $template?->name) }}"
                                maxlength="255"
                                required
                                class="mt-1 block w-full rounded-lg border-slate-200 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                                placeholder="{{ __('Site visit reminder') }}"
                            >
                            <x-input-error class="mt-1" :messages="$errors->get('name')" />
                        </div>

                        <fieldset>
                            <legend class="text-sm font-medium text-black">{{ __('Channel') }}</legend>
                            <div class="mt-2 grid gap-2 sm:grid-cols-3">
                                @foreach ($channels as $channel)
                                    <label class="cursor-pointer">
                                        <input
                                            type="radio"
                                            name="channel"
                                            value="{{ $channel->value }}"
                                            class="peer sr-only"
                                            x-model="channel"
                                            @checked(old('channel', $template?->channel->value ?? \App\Enums\MessageTemplateChannel::WhatsApp->value) === $channel->value)
                                        >
                                        <span class="flex h-full flex-col gap-1 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 peer-checked:border-navy peer-checked:bg-navy/5 peer-checked:text-navy">
                                            <span class="text-sm font-semibold">{{ $channel->label() }}</span>
                                            <span class="text-xs text-slate-500">{{ $channel->description() }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('channel')" />
                        </fieldset>

                        <div x-show="channel === 'email'" x-cloak>
                            <label for="template_subject" class="block text-sm font-medium text-black">{{ __('Subject') }}</label>
                            <input
                                id="template_subject"
                                x-ref="subject"
                                type="text"
                                name="subject"
                                x-model="subject"
                                maxlength="255"
                                @focus="lastField = 'subject'"
                                class="mt-1 block w-full rounded-lg border-slate-200 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                                placeholder="{{ __('Following up,') }} @{{lead.name}}"
                            >
                            <x-input-error class="mt-1" :messages="$errors->get('subject')" />
                        </div>

                        <div>
                            <label for="template_body" class="block text-sm font-medium text-black">{{ __('Message') }}</label>
                            <textarea
                                id="template_body"
                                x-ref="body"
                                name="body"
                                x-model="body"
                                rows="10"
                                maxlength="5000"
                                required
                                @focus="lastField = 'body'"
                                class="mt-1 block w-full rounded-lg border-slate-200 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                                placeholder="Hi @{{lead.name}}, this is @{{user.name}} from @{{company.name}}."
                            ></textarea>
                            <x-input-error class="mt-1" :messages="$errors->get('body')" />
                        </div>

                        <label class="inline-flex items-center gap-2">
                            <input type="hidden" name="is_active" value="0">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                class="rounded border-slate-300 text-navy focus:ring-navy"
                                @checked($templateIsActive)
                            >
                            <span class="text-sm font-medium text-black">{{ __('Active') }}</span>
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl border border-violet-100 bg-gradient-to-br from-violet-50/70 to-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-black">{{ __('Preview') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Sample values so you can check the wording. Live data is filled when this template is used.') }}</p>
                    <p class="mt-3 text-sm font-medium text-slate-700" x-show="channel === 'email'" x-cloak x-text="preview(subject)"></p>
                    <p class="mt-2 whitespace-pre-wrap text-sm leading-relaxed text-slate-600" x-text="preview(body) || @js(__('Start writing to see a preview.'))"></p>
                </div>
            </div>

            <aside class="flex flex-col gap-3 lg:sticky lg:top-4">
                <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
                    <h2 class="text-sm font-semibold text-black">{{ __('Variables') }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ __('Click a field to insert it at the cursor.') }}</p>

                    <div class="mt-3 flex flex-col gap-3">
                        @foreach ($variableGroups as $group)
                            <div>
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400">{{ $group['label'] }}</p>
                                <div class="mt-1.5 flex flex-wrap gap-1.5">
                                    @foreach ($group['variables'] as $variable)
                                        <button
                                            type="button"
                                            class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-700 hover:border-navy/30 hover:bg-navy/5 hover:text-navy"
                                            title="{{ $variable['token'] }}"
                                            @click="insert(@js($variable['token']))"
                                        >
                                            {{ $variable['label'] }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <x-ui.button :href="route('tenant.automations.templates')" variant="outline">{{ __('Cancel') }}</x-ui.button>
                    <x-ui.button type="submit" variant="default">
                        {{ $isEditing ? __('Save template') : __('Create template') }}
                    </x-ui.button>
                </div>
            </aside>
        </form>
    </div>
</x-tenant-layout>
