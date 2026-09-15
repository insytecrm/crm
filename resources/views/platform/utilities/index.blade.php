<x-app-layout :title="__('Utilities') . ' | InSyte CRM'">
    <x-platform.page-header
        :title="__('Utilities')"
        :description="__('Configure SMTP for CRM utility emails such as partner login credentials.')"
    />

    <x-auth-session-status class="mb-4 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700" :status="session('status')" />

    <div class="space-y-6">
        <x-platform.panel :title="__('SMTP configuration')" compact>
            <form method="POST" action="{{ route('platform.utilities.mail.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                @include('platform.utilities.partials.smtp-fields', [
                    'setting' => $setting,
                    'encryptions' => $encryptions,
                    'deliveryModes' => $deliveryModes,
                ])

                <div class="flex justify-end">
                    <x-ui.button type="submit" variant="default">{{ __('Save SMTP settings') }}</x-ui.button>
                </div>
            </form>
        </x-platform.panel>

        @if ($setting?->isConfigured())
            <x-platform.panel :title="__('Send test email')" compact>
                <form method="POST" action="{{ route('platform.utilities.test') }}" class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    @csrf
                    <div class="flex-1">
                        <x-input-label for="test_email" :value="__('Recipient email')" />
                        <x-text-input id="test_email" name="test_email" type="email" class="mt-1 block w-full" :value="old('test_email', auth()->user()->email)" required />
                        <x-input-error class="mt-2" :messages="$errors->get('test_email')" />
                    </div>
                    <x-ui.button type="submit" variant="outline">{{ __('Send test') }}</x-ui.button>
                </form>
            </x-platform.panel>
        @endif

        <x-platform.panel :title="__('Email templates')" compact>
            <p class="mb-4 text-sm text-slate-500">{{ __('These templates are used for platform utility emails only. Lead outreach uses Integrations → Email separately.') }}</p>

            <div class="space-y-6">
                @foreach ($templates as $template)
                    @php
                        $type = $template->type;
                    @endphp
                    <div class="rounded-xl border border-slate-100 p-4">
                        <div class="mb-3">
                            <h3 class="text-sm font-semibold text-black">{{ $type->label() }}</h3>
                            <p class="mt-1 text-xs text-slate-500">{{ $type->description() }}</p>
                            <p class="mt-2 text-xs text-slate-400">
                                {{ __('Variables:') }}
                                {{ collect($type->mergeVariables())->map(fn (string $variable): string => '{'.'{'.$variable.'}'.'}')->implode(', ') }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('platform.utilities.templates.update', $template) }}" class="space-y-3">
                            @csrf
                            @method('PUT')

                            <div>
                                <x-input-label for="subject_{{ $template->id }}" :value="__('Subject')" />
                                <x-text-input id="subject_{{ $template->id }}" name="subject" type="text" class="mt-1 block w-full" :value="old('subject', $template->subject)" required />
                            </div>

                            <div>
                                <x-input-label for="body_{{ $template->id }}" :value="__('Body')" />
                                <textarea id="body_{{ $template->id }}" name="body" rows="8" class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-navy focus:ring-navy" required>{{ old('body', $template->body) }}</textarea>
                            </div>

                            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-navy focus:ring-navy" @checked(old('is_active', $template->is_active))>
                                {{ __('Template active') }}
                            </label>

                            <div class="flex justify-end">
                                <x-ui.button type="submit" variant="outline">{{ __('Save template') }}</x-ui.button>
                            </div>
                        </form>
                    </div>
                @endforeach
            </div>
        </x-platform.panel>
    </div>
</x-app-layout>
