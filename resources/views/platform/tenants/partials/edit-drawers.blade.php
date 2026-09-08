@php
    $statuses = \App\Enums\TenantStatus::cases();
@endphp

@push('drawers')
    @foreach ($tenants as $tenant)
        @php
            $drawerName = 'edit-partner-'.$tenant->id;
            $shouldOpen = $errors->any() && old('_editing_tenant') === $tenant->id;
        @endphp

        <div
            x-data="{ open: @js($shouldOpen) }"
            x-cloak
            x-on:keydown.escape.window="if (open) open = false"
            x-on:open-edit-partner.window="if (String($event.detail) === @js((string) $tenant->id)) open = true"
        >
            <div
                x-show="open"
                x-transition:enter="transition-opacity ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition-opacity ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[70] bg-navy/40 backdrop-blur-sm lg:start-[var(--sidebar-width,16rem)]"
                x-on:click="open = false"
                style="display: none;"
            ></div>

            <div
                x-show="open"
                x-transition:enter="transform transition ease-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in duration-200"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="fixed inset-y-0 end-0 z-[70] flex w-full max-w-lg"
                style="display: none;"
            >
                <div
                    class="pointer-events-auto flex h-full w-full flex-col overflow-hidden border-s border-slate-200 bg-white shadow-2xl"
                    role="dialog"
                    aria-modal="true"
                    x-on:click.stop
                >
                    <div class="flex shrink-0 items-start justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <div class="min-w-0">
                            <h2 class="text-lg font-semibold leading-none tracking-tight text-black">{{ __('Edit Channel Partner') }}</h2>
                            <p class="mt-1 text-sm text-slate-500">{{ $tenant->name }}</p>
                        </div>
                        <button
                            type="button"
                            class="rounded-md p-1.5 text-slate-400 hover:bg-slate-100 hover:text-black"
                            x-on:click="open = false"
                            aria-label="{{ __('Close') }}"
                        >
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <form
                        id="edit-partner-form-{{ $tenant->id }}"
                        method="POST"
                        action="{{ route('tenants.update', $tenant) }}"
                        class="min-h-0 flex-1 overflow-y-auto"
                    >
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_editing_tenant" value="{{ $tenant->id }}">
                        <input type="hidden" name="_return_to" value="index">

                        <div class="space-y-4 px-5 py-4">
                            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                                <h3 class="mb-3 text-base font-semibold text-black">{{ __('Company Information') }}</h3>
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label :for="'drawer-name-'.$tenant->id" :value="__('Company Name')" />
                                        <x-text-input :id="'drawer-name-'.$tenant->id" name="name" type="text" class="mt-1 block w-full" :value="old('name', $tenant->name)" required />
                                        @if ($shouldOpen)
                                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                        @endif
                                    </div>
                                    <div>
                                        <x-input-label :for="'drawer-slug-'.$tenant->id" :value="__('Slug')" />
                                        <x-text-input :id="'drawer-slug-'.$tenant->id" type="text" class="mt-1 block w-full bg-slate-50" :value="$tenant->id" disabled />
                                    </div>
                                    <div>
                                        <x-input-label :for="'drawer-location-'.$tenant->id" :value="__('Location')" />
                                        <x-text-input :id="'drawer-location-'.$tenant->id" name="location" type="text" class="mt-1 block w-full" :value="old('location', $tenant->location)" />
                                        @if ($shouldOpen)
                                            <x-input-error class="mt-2" :messages="$errors->get('location')" />
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                                <h3 class="mb-3 text-base font-semibold text-black">{{ __('Account Information') }}</h3>
                                <div>
                                    <x-input-label :for="'drawer-status-'.$tenant->id" :value="__('Status')" />
                                    <select
                                        id="drawer-status-{{ $tenant->id }}"
                                        name="status"
                                        required
                                        class="mt-1 block h-10 w-full rounded-lg border border-slate-200 bg-white px-3 text-sm text-black shadow-sm focus:border-navy focus:ring-navy"
                                    >
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->value }}" @selected(old('status', $tenant->status->value) === $status->value)>
                                                {{ ucfirst($status->value) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($shouldOpen)
                                        <x-input-error class="mt-2" :messages="$errors->get('status')" />
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                                <h3 class="mb-3 text-base font-semibold text-black">{{ __('Contact Information') }}</h3>
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label :for="'drawer-owner-'.$tenant->id" :value="__('Owner Name')" />
                                        <x-text-input :id="'drawer-owner-'.$tenant->id" name="owner_name" type="text" class="mt-1 block w-full" :value="old('owner_name', $tenant->owner_name)" />
                                        @if ($shouldOpen)
                                            <x-input-error class="mt-2" :messages="$errors->get('owner_name')" />
                                        @endif
                                    </div>
                                    <div>
                                        <x-input-label :for="'drawer-email-'.$tenant->id" :value="__('Email')" />
                                        <x-text-input :id="'drawer-email-'.$tenant->id" name="email" type="email" class="mt-1 block w-full" :value="old('email', $tenant->email)" />
                                        @if ($shouldOpen)
                                            <x-input-error class="mt-2" :messages="$errors->get('email')" />
                                        @endif
                                    </div>
                                    <div>
                                        <x-input-label :for="'drawer-phone-'.$tenant->id" :value="__('Phone')" />
                                        <x-text-input :id="'drawer-phone-'.$tenant->id" name="phone" type="text" class="mt-1 block w-full" :value="old('phone', $tenant->phone)" />
                                        @if ($shouldOpen)
                                            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="flex shrink-0 items-center justify-between gap-2 border-t border-slate-100 px-5 py-4">
                        <div>
                            @if ($tenant->can_be_deleted)
                                <form
                                    method="POST"
                                    action="{{ route('tenants.destroy', $tenant) }}"
                                    onsubmit="return confirm(@js(__('Delete this channel partner and its database?')));"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <x-ui.button type="submit" variant="destructive">
                                        {{ __('Delete') }}
                                    </x-ui.button>
                                </form>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <x-ui.button type="button" variant="outline" x-on:click="open = false">
                                {{ __('Cancel') }}
                            </x-ui.button>
                            <x-ui.button type="submit" variant="default" :form="'edit-partner-form-'.$tenant->id">
                                {{ __('Save') }}
                            </x-ui.button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endpush
