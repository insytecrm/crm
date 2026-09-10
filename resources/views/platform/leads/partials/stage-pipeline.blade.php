@props([
    'lead',
    'stages',
])

@php
    use App\Enums\PlatformLeadStage;

    $currentIndex = $lead->stage->orderIndex();
@endphp

<div class="overflow-x-auto">
    <ol class="flex min-w-max items-center gap-1 text-xs sm:text-sm">
        @foreach ($stages as $index => $stage)
            @php
                $isCurrent = $stage === $lead->stage;
                $isComplete = $index < $currentIndex;
            @endphp
            <li class="contents">
                <form method="POST" action="{{ route('platform.leads.stage.update', $lead) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="stage" value="{{ $stage->value }}">
                    <button
                        type="submit"
                        @class([
                            'rounded-full px-3 py-1.5 font-semibold transition-colors',
                            'bg-navy text-white shadow-sm' => $isCurrent,
                            'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' => $isComplete && ! $isCurrent,
                            'bg-slate-100 text-slate-500 hover:bg-slate-200 hover:text-black' => ! $isComplete && ! $isCurrent,
                        ])
                    >
                        {{ $stage->label() }}
                    </button>
                </form>
                @if (! $loop->last)
                    <span class="px-1 text-slate-300" aria-hidden="true">→</span>
                @endif
            </li>
        @endforeach
    </ol>
</div>

@if ($lead->stage === PlatformLeadStage::DemoScheduled)
    <form method="POST" action="{{ route('platform.leads.stage.update', $lead) }}" class="mt-4 flex flex-wrap items-end gap-3">
        @csrf
        @method('PATCH')
        <input type="hidden" name="stage" value="{{ PlatformLeadStage::DemoScheduled->value }}">
        <div>
            <x-input-label for="demo_date" :value="__('Demo Date')" />
            <x-text-input id="demo_date" name="demo_date" type="date" class="mt-1 block w-full" :value="old('demo_date', $lead->demo_date?->toDateString())" required />
        </div>
        <div>
            <x-input-label for="demo_time" :value="__('Demo Time')" />
            <x-text-input id="demo_time" name="demo_time" type="time" class="mt-1 block w-full" :value="old('demo_time', $lead->demo_time ? \Illuminate\Support\Carbon::parse($lead->demo_time)->format('H:i') : '')" required />
        </div>
        <x-ui.button type="submit" variant="outline" size="sm">{{ __('Save Demo') }}</x-ui.button>
    </form>
@endif
