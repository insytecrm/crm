<x-tenant-layout :title="__('Utilities') . ' | InSyte CRM'">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ session('status') }}
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ route('tenant.settings.index', ['tab' => 'integrations']) }}" class="text-sm font-medium text-slate-500 hover:text-navy">
            ← {{ __('Back to Integrations') }}
        </a>
        <h1 class="mt-3 text-2xl font-semibold text-black">{{ __('Utilities') }}</h1>
        <p class="mt-1 max-w-2xl text-sm text-slate-500">
            {{ __('Configure workspace SMTP for team login credentials and CRM notices. Lead outreach stays under Integrations → Email.') }}
        </p>
    </div>

    @if ($canManage)
        <form method="POST" action="{{ route('tenant.settings.integrations.utilities.mail.update') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid gap-4 lg:grid-cols-2">
                {{-- SMTP Server --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15a4.5 4.5 0 0 0 4.5 4.5H18a3.75 3.75 0 1 0-.257-7.496 5.25 5.25 0 1 0-10.193-2.2A4.5 4.5 0 0 0 2.25 15Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-black">{{ __('SMTP Server') }}</h2>
                            <p class="mt-0.5 text-sm text-slate-500">{{ __('Connection details for this workspace’s outgoing mail server.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div class="grid gap-4 sm:grid-cols-[1fr_7rem]">
                            <div>
                                <label for="host" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('SMTP Host') }} <span class="text-rose-500">*</span></label>
                                <div class="relative mt-1.5">
                                    <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 0 1-3-3m3 3a3 3 0 1 0 0 6h13.5a3 3 0 1 0 0-6m-16.5-3a3 3 0 0 1 3-3h13.5a3 3 0 0 1 3 3m-19.5 0a4.5 4.5 0 0 1 .9-2.7L5.737 5.1a3.375 3.375 0 0 1 2.7-1.35h7.126c1.062 0 2.062.5 2.7 1.35l2.587 3.45a4.5 4.5 0 0 1 .9 2.7m0 0a3 3 0 0 1-3 3m0 3h.008v.008H18.75v-.008Zm-13.5 0h.008v.008H5.25v-.008Z" /></svg>
                                    </span>
                                    <input id="host" name="host" type="text" value="{{ old('host', $setting?->host ?? 'smtp.hostinger.com') }}" required class="block w-full rounded-xl border border-slate-200 py-2.5 ps-10 pe-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy" placeholder="smtp.hostinger.com">
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('host')" />
                            </div>
                            <div>
                                <label for="port" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Port') }} <span class="text-rose-500">*</span></label>
                                <input id="port" name="port" type="number" value="{{ old('port', $setting?->port ?? 587) }}" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                                <x-input-error class="mt-2" :messages="$errors->get('port')" />
                            </div>
                        </div>

                        <div>
                            <label for="encryption" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Encryption') }} <span class="text-rose-500">*</span></label>
                            <select id="encryption" name="encryption" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                                @foreach ($encryptions as $encryption)
                                    <option value="{{ $encryption->value }}" @selected(old('encryption', $setting?->encryption?->value ?? 'tls') === $encryption->value)>
                                        {{ $encryption->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('encryption')" />
                        </div>

                        <div>
                            <label for="username" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('SMTP Username') }} <span class="text-rose-500">*</span></label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                                </span>
                                <input id="username" name="username" type="text" value="{{ old('username', $setting?->username) }}" required class="block w-full rounded-xl border border-slate-200 py-2.5 ps-10 pe-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                            </div>
                            <x-input-error class="mt-2" :messages="$errors->get('username')" />
                        </div>

                        <div>
                            <label for="password" class="text-xs font-semibold uppercase tracking-wider text-slate-500">
                                {{ __('SMTP Password') }}
                                @unless ($setting?->password)
                                    <span class="text-rose-500">*</span>
                                @endunless
                            </label>
                            <div class="relative mt-1.5">
                                <span class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3 text-slate-400">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" /></svg>
                                </span>
                                <input id="password" name="password" type="password" autocomplete="new-password" @required(! $setting?->password) class="block w-full rounded-xl border border-slate-200 py-2.5 ps-10 pe-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                            </div>
                            <p class="mt-1.5 text-xs text-sky-600">{{ __('Stored securely. Leave blank to keep the existing password unchanged.') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('password')" />
                        </div>
                    </div>
                </section>

                {{-- Sender Identity --}}
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-start gap-3">
                        <span class="inline-flex size-11 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z" />
                            </svg>
                        </span>
                        <div>
                            <h2 class="text-base font-semibold text-black">{{ __('Sender Identity') }}</h2>
                            <p class="mt-0.5 text-sm text-slate-500">{{ __('What recipients see when this workspace’s emails arrive.') }}</p>
                        </div>
                    </div>

                    <div class="mt-5 space-y-4">
                        <div>
                            <label for="from_email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('From Email') }} <span class="text-rose-500">*</span></label>
                            <input id="from_email" name="from_email" type="email" value="{{ old('from_email', $setting?->from_email) }}" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy" placeholder="noreply@partnerdomain.com">
                            <x-input-error class="mt-2" :messages="$errors->get('from_email')" />
                        </div>

                        <div>
                            <label for="from_name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('From Name') }} <span class="text-rose-500">*</span></label>
                            <input id="from_name" name="from_name" type="text" value="{{ old('from_name', $setting?->from_name ?? 'InSyte CRM') }}" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                            <x-input-error class="mt-2" :messages="$errors->get('from_name')" />
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="reply_to_email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Reply-To Email') }}</label>
                                <input id="reply_to_email" name="reply_to_email" type="email" value="{{ old('reply_to_email', $setting?->reply_to_email) }}" class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy" placeholder="{{ __('Optional') }}">
                                <x-input-error class="mt-2" :messages="$errors->get('reply_to_email')" />
                            </div>
                            <div>
                                <label for="reply_to_name" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Reply-To Name') }}</label>
                                <input id="reply_to_name" name="reply_to_name" type="text" value="{{ old('reply_to_name', $setting?->reply_to_name) }}" class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy" placeholder="{{ __('Optional') }}">
                                <x-input-error class="mt-2" :messages="$errors->get('reply_to_name')" />
                            </div>
                        </div>

                        <div>
                            <label for="notes" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Notes') }}</label>
                            <textarea id="notes" name="notes" rows="3" class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy" placeholder="{{ __('Internal notes about this configuration...') }}">{{ old('notes', $setting?->notes) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('notes')" />
                        </div>

                        <div>
                            <label for="credentials_delivery_mode" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Credentials delivery') }}</label>
                            <select id="credentials_delivery_mode" name="credentials_delivery_mode" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                                @foreach ($deliveryModes as $mode)
                                    <option value="{{ $mode->value }}" @selected(old('credentials_delivery_mode', $setting?->credentials_delivery_mode?->value ?? 'ask') === $mode->value)>
                                        {{ $mode->label() }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('credentials_delivery_mode')" />
                        </div>

                        <label class="flex items-start gap-3 rounded-xl border border-sky-100 bg-sky-50/70 px-4 py-3">
                            <input type="checkbox" name="is_active" value="1" class="mt-0.5 rounded border-slate-300 text-navy focus:ring-navy" @checked(old('is_active', $setting?->is_active ?? true))>
                            <span>
                                <span class="block text-sm font-semibold text-black">{{ __('Active configuration') }}</span>
                                <span class="mt-0.5 block text-xs text-slate-500">{{ __('Use these settings for this workspace’s outgoing utility emails.') }}</span>
                            </span>
                        </label>
                    </div>
                </section>
            </div>

            <div class="flex justify-end">
                <x-ui.button type="submit" variant="default" class="inline-flex items-center gap-2">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    {{ __('Save Configuration') }}
                </x-ui.button>
            </div>
        </form>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-sm text-slate-500 shadow-sm">
            {{ __('You can view Utilities but need manage permission to edit SMTP settings.') }}
        </div>
    @endif

    @if ($canManage && $setting?->isConfigured())
        <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 class="text-base font-semibold text-black">{{ __('Send test email') }}</h2>
            <form method="POST" action="{{ route('tenant.settings.integrations.utilities.test') }}" class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label for="test_email" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Recipient email') }}</label>
                    <input id="test_email" name="test_email" type="email" value="{{ old('test_email', auth()->user()->email) }}" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                    <x-input-error class="mt-2" :messages="$errors->get('test_email')" />
                </div>
                <x-ui.button type="submit" variant="outline">{{ __('Send test') }}</x-ui.button>
            </form>
        </section>
    @endif

    <section class="mt-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
        <h2 class="text-base font-semibold text-black">{{ __('Email templates') }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ __('Used when sending team user login details from Utilities SMTP.') }}</p>

        <div class="mt-4 space-y-6">
            @foreach ($templates as $template)
                @php $type = $template->type; @endphp
                <div class="rounded-xl border border-slate-100 p-4">
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-black">{{ $type->label() }}</h3>
                        <p class="mt-1 text-xs text-slate-500">{{ $type->description() }}</p>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ __('Variables:') }}
                            {{ collect($type->mergeVariables())->map(fn (string $variable): string => '{'.'{'.$variable.'}'.'}')->implode(', ') }}
                        </p>
                    </div>

                    @if ($canManage)
                        <form method="POST" action="{{ route('tenant.settings.integrations.utilities.templates.update', $template) }}" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label for="subject_{{ $template->id }}" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Subject') }}</label>
                                <input id="subject_{{ $template->id }}" name="subject" type="text" value="{{ old('subject', $template->subject) }}" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">
                            </div>
                            <div>
                                <label for="body_{{ $template->id }}" class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ __('Body') }}</label>
                                <textarea id="body_{{ $template->id }}" name="body" rows="8" required class="mt-1.5 block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm text-black shadow-sm focus:border-navy focus:ring-navy">{{ old('body', $template->body) }}</textarea>
                            </div>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" @checked(old('is_active', $template->is_active))>
                                {{ __('Template active') }}
                            </label>
                            <div class="flex justify-end">
                                <x-ui.button type="submit" variant="outline">{{ __('Save template') }}</x-ui.button>
                            </div>
                        </form>
                    @else
                        <p class="text-sm text-slate-600"><span class="font-medium">{{ __('Subject:') }}</span> {{ $template->subject }}</p>
                        <pre class="mt-2 whitespace-pre-wrap rounded-lg bg-slate-50 p-3 text-sm text-slate-700">{{ $template->body }}</pre>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
</x-tenant-layout>
