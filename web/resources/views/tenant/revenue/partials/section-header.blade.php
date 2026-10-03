<div @class(['border-b px-5 py-4 sm:px-6 sm:py-5', $headerClass ?? ''])>
    <div class="flex flex-col gap-0.5">
        <h2 class="text-base font-semibold leading-snug text-black">{{ $title }}</h2>
        <p class="text-sm leading-snug text-slate-500">{{ $subtitle }}</p>
    </div>
</div>
