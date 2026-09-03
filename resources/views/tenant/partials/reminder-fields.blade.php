@props([
    'idPrefix' => 'reminder',
    'enabled' => false,
    'hours' => 0,
    'minutes' => 15,
    'seconds' => 0,
])

@php
    $enabled = (bool) old('add_reminder', $enabled);
    $hours = (int) old('reminder_hours', $hours);
    $minutes = (int) old('reminder_minutes', $minutes);
    $seconds = (int) old('reminder_seconds', $seconds);
@endphp

<div
    x-data="{ enabled: @js($enabled) }"
    class="space-y-3 rounded-xl border border-slate-100 bg-slate-50/70 p-3"
>
    <label class="flex cursor-pointer items-center justify-between gap-3">
        <span class="min-w-0">
            <span class="block text-sm font-medium text-black">{{ __('Add reminder') }}</span>
            <span class="mt-0.5 block text-xs text-slate-500">{{ __('Show an on-screen reminder before the scheduled time.') }}</span>
        </span>
        <input type="hidden" name="add_reminder" :value="enabled ? 1 : 0">
        <button
            type="button"
            @click="enabled = ! enabled"
            :aria-pressed="enabled"
            :class="enabled ? 'bg-navy' : 'bg-slate-200'"
            class="relative inline-flex h-6 w-11 shrink-0 items-center rounded-full transition"
        >
            <span
                :class="enabled ? 'translate-x-5' : 'translate-x-1'"
                class="inline-block size-4 rounded-full bg-white shadow transition"
            ></span>
        </button>
    </label>

    <div x-show="enabled" x-cloak class="space-y-2">
        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ __('Remind me before') }}</p>
        <div class="grid grid-cols-3 gap-2">
            <div>
                <label for="{{ $idPrefix }}_hours" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Hours') }}</label>
                <input
                    id="{{ $idPrefix }}_hours"
                    type="number"
                    name="reminder_hours"
                    min="0"
                    max="168"
                    value="{{ $hours }}"
                    :disabled="! enabled"
                    class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy disabled:bg-slate-100"
                >
            </div>
            <div>
                <label for="{{ $idPrefix }}_minutes" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Minutes') }}</label>
                <input
                    id="{{ $idPrefix }}_minutes"
                    type="number"
                    name="reminder_minutes"
                    min="0"
                    max="59"
                    value="{{ $minutes }}"
                    :disabled="! enabled"
                    class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy disabled:bg-slate-100"
                >
            </div>
            <div>
                <label for="{{ $idPrefix }}_seconds" class="mb-1 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">{{ __('Seconds') }}</label>
                <input
                    id="{{ $idPrefix }}_seconds"
                    type="number"
                    name="reminder_seconds"
                    min="0"
                    max="59"
                    value="{{ $seconds }}"
                    :disabled="! enabled"
                    class="block w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-navy focus:ring-navy disabled:bg-slate-100"
                >
            </div>
        </div>
        @error('reminder_hours')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('reminder_minutes')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
        @error('reminder_seconds')
            <p class="text-sm text-rose-600">{{ $message }}</p>
        @enderror
    </div>
</div>
