@php
    use App\Enums\DomainPurpose;
    use App\Enums\SettingsTab;
@endphp

<div class="space-y-8">
    <div>
        <h2 class="text-lg font-semibold text-black">{{ SettingsTab::Domains->label() }}</h2>
        <p class="mt-1 text-sm text-slate-500">{{ SettingsTab::Domains->description() }}</p>
    </div>

    @foreach ([
        ['purpose' => DomainPurpose::Crm, 'domain' => $crmDomain],
        ['purpose' => DomainPurpose::Website, 'domain' => $websiteDomain],
    ] as $domainCard)
        @php
            /** @var \App\Enums\DomainPurpose $purpose */
            $purpose = $domainCard['purpose'];
            $domain = $domainCard['domain'];
        @endphp
        <section class="rounded-2xl border border-slate-100 bg-slate-50/50 p-4 sm:p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="text-base font-semibold text-black">{{ $purpose->label() }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $purpose->description() }}</p>
                </div>

                @if ($domain?->isVerified())
                    <span class="shrink-0 rounded-full bg-emerald-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-emerald-700 ring-1 ring-emerald-100">
                        {{ __('Verified') }}
                    </span>
                @elseif ($domain)
                    <span class="shrink-0 rounded-full bg-amber-50 px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-amber-700 ring-1 ring-amber-100">
                        {{ __('Pending DNS') }}
                    </span>
                @else
                    <span class="shrink-0 rounded-full bg-white px-2.5 py-0.5 text-[11px] font-semibold uppercase tracking-wide text-slate-400 ring-1 ring-slate-200">
                        {{ __('Not configured') }}
                    </span>
                @endif
            </div>

            @if ($canManageDomains)
                <form method="POST" action="{{ route('tenant.settings.domains.upsert') }}" class="mt-4 space-y-3">
                    @csrf
                    <input type="hidden" name="purpose" value="{{ $purpose->value }}">

                    <div>
                        <x-input-label :for="'domain_'.$purpose->value" :value="__('Hostname')" />
                        <x-text-input
                            :id="'domain_'.$purpose->value"
                            name="domain"
                            type="text"
                            class="mt-1 block w-full"
                            :value="old('purpose') === $purpose->value ? old('domain', $domain?->domain) : ($domain?->domain ?? '')"
                            :placeholder="$purpose->placeholder()"
                            required
                        />
                        @if ($errors->has('domain') && old('purpose') === $purpose->value)
                            <x-input-error class="mt-2" :messages="$errors->get('domain')" />
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <x-ui.button type="submit" variant="default" size="sm">
                            {{ $domain ? __('Update domain') : __('Save domain') }}
                        </x-ui.button>
                    </div>
                </form>
            @elseif ($domain)
                <p class="mt-4 text-sm font-medium text-black">{{ $domain->domain }}</p>
            @else
                <p class="mt-4 text-sm text-slate-500">{{ __('Ask a workspace admin to connect this domain.') }}</p>
            @endif

            @if ($domain)
                <div class="mt-5 space-y-3 rounded-xl border border-slate-100 bg-white p-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ __('DNS records') }}</p>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-xs uppercase tracking-wide text-slate-400">
                                <tr>
                                    <th class="pb-2 pr-4 font-semibold">{{ __('Type') }}</th>
                                    <th class="pb-2 pr-4 font-semibold">{{ __('Name') }}</th>
                                    <th class="pb-2 font-semibold">{{ __('Value') }}</th>
                                </tr>
                            </thead>
                            <tbody class="text-slate-700">
                                <tr class="border-t border-slate-100">
                                    <td class="py-2 pr-4 font-medium">CNAME</td>
                                    <td class="py-2 pr-4 font-mono text-xs">{{ $domain->domain }}</td>
                                    <td class="py-2 font-mono text-xs break-all">{{ $domain->cnameTarget() }}</td>
                                </tr>
                                <tr class="border-t border-slate-100">
                                    <td class="py-2 pr-4 font-medium">TXT</td>
                                    <td class="py-2 pr-4 font-mono text-xs">{{ $domain->txtRecordName() }}</td>
                                    <td class="py-2 font-mono text-xs break-all">{{ $domain->txtRecordValue() }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <p class="text-xs text-slate-500">
                        {{ __('Add both records at your DNS provider. Verification looks for the TXT value on this hostname.') }}
                    </p>

                    @if ($domain->isVerified())
                        <div class="rounded-lg border border-emerald-100 bg-emerald-50 px-3 py-2 text-sm text-emerald-800">
                            <p class="font-medium">{{ __('Live at') }}</p>
                            <a href="{{ $domain->accessUrl() }}" class="mt-1 block truncate font-medium text-navy hover:underline" target="_blank" rel="noopener noreferrer">
                                {{ $domain->accessUrl() }}
                            </a>
                            @if ($purpose === DomainPurpose::Website)
                                <p class="mt-1 text-xs text-emerald-700">
                                    {{ __('Property microsites publish at :path on this domain.', ['path' => '/projects/{slug}']) }}
                                </p>
                            @else
                                <p class="mt-1 text-xs text-emerald-700">
                                    {{ __('Opening this domain takes your team into the CRM for this workspace.') }}
                                </p>
                            @endif
                        </div>
                    @elseif ($canManageDomains)
                        <div class="flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('tenant.settings.domains.verify') }}">
                                @csrf
                                <input type="hidden" name="purpose" value="{{ $purpose->value }}">
                                <x-ui.button type="submit" variant="default" size="sm">
                                    {{ __('Verify DNS') }}
                                </x-ui.button>
                            </form>
                        </div>
                    @endif

                    @if ($canManageDomains)
                        <form
                            method="POST"
                            action="{{ route('tenant.settings.domains.destroy') }}"
                            onsubmit="return confirm(@js(__('Remove this domain? Microsite publishing may be affected.')))"
                        >
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="purpose" value="{{ $purpose->value }}">
                            <button type="submit" class="text-xs font-medium text-rose-600 hover:underline">
                                {{ __('Remove domain') }}
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </section>
    @endforeach
</div>
