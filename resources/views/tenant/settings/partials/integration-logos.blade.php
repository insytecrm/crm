@php
    $logo = $logo ?? 'api';
@endphp

@switch($logo)
    @case('api')
        <svg class="size-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" />
        </svg>
        @break

    @case('google-sheets')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#0F9D58" d="M29 4H14c-1.1 0-2 .9-2 2v36c0 1.1.9 2 2 2h20c1.1 0 2-.9 2-2V13L29 4z"/>
            <path fill="#87CEAC" d="M29 4v9h9L29 4z"/>
            <path fill="#FFF" d="M18 20h12v2H18zm0 5h12v2H18zm0 5h8v2h-8z"/>
            <path fill="#FFF" fill-opacity=".85" d="M16 17h16v14H16z"/>
            <path fill="#0F9D58" d="M18 19h5v4h-5zm6 0h6v4h-6zm-6 5h5v4h-5zm6 0h6v4h-6zm-6 5h5v4h-5zm6 0h6v4h-6z"/>
        </svg>
        @break

    @case('facebook')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <rect width="48" height="48" rx="10" fill="#1877F2"/>
            <path fill="#FFF" d="M26.5 38V25.8h4.1l.6-4.7h-4.7v-3c0-1.4.4-2.3 2.4-2.3h2.6v-4.2c-.4-.1-2-.2-3.8-.2-3.8 0-6.4 2.3-6.4 6.5v3.6H17v4.7h4.3V38h5.2z"/>
        </svg>
        @break

    @case('99acres')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <rect width="48" height="48" rx="10" fill="#1A73E8"/>
            <text x="24" y="30" text-anchor="middle" fill="#fff" font-size="14" font-family="Arial, sans-serif" font-weight="700">99</text>
        </svg>
        @break

    @case('housing')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <rect width="48" height="48" rx="10" fill="#F5C518"/>
            <path fill="#111" d="M24 10 12 20v16h8v-8h8v8h8V20L24 10z"/>
            <path fill="#F5C518" d="M24 16 18 21v9h3v-5h6v5h3v-9l-6-5z"/>
        </svg>
        @break

    @case('magicbricks')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <rect width="48" height="48" rx="10" fill="#E31C23"/>
            <text x="24" y="30" text-anchor="middle" fill="#fff" font-size="16" font-family="Arial, sans-serif" font-weight="700">mb</text>
        </svg>
        @break

    @case('nobroker')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <rect width="48" height="48" rx="10" fill="#FD3752"/>
            <text x="24" y="31" text-anchor="middle" fill="#fff" font-size="22" font-family="Arial, sans-serif" font-weight="700">%</text>
        </svg>
        @break

    @case('whatsapp')
        <svg class="size-7" viewBox="0 0 24 24" aria-hidden="true">
            <path fill="#25D366" d="M12.04 2C6.58 2 2.15 6.4 2.15 11.84c0 1.98.58 3.83 1.58 5.4L2 22l4.9-1.58a9.86 9.86 0 0 0 5.14 1.43h.01c5.46 0 9.89-4.4 9.89-9.84C21.94 6.4 17.5 2 12.04 2Z"/>
            <path fill="#FFF" d="M17.3 14.6c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.14-.42-2.17-1.34-.8-.71-1.34-1.6-1.5-1.87-.16-.27-.02-.41.12-.55.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.84-2.01-.22-.53-.45-.46-.61-.46h-.52c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29s.98 2.66 1.12 2.84c.14.18 1.93 2.95 4.68 4.13.65.28 1.16.45 1.56.58.65.21 1.25.18 1.72.11.52-.08 1.6-.65 1.83-1.28.23-.63.23-1.17.16-1.28-.07-.11-.25-.18-.52-.32Z"/>
        </svg>
        @break

    @case('gmail')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#4285F4" d="M6 10v28h8V22l10 7.5L34 22v16h8V10L24 23.5 6 10z"/>
            <path fill="#EA4335" d="M6 10 24 23.5 14 10H6z"/>
            <path fill="#FBBC05" d="M34 10h8L24 23.5 34 10z"/>
            <path fill="#34A853" d="M14 38V22l-8-6v22h8z"/>
            <path fill="#C5221F" d="M34 38h8V16l-8 6v16z"/>
        </svg>
        @break

    @case('calendar')
        <svg class="size-7" viewBox="0 0 48 48" aria-hidden="true">
            <path fill="#FFF" d="M8 10h32v30H8z"/>
            <path fill="#1A73E8" d="M8 10h32v8H8z"/>
            <path fill="#EA4335" d="M14 6h4v8h-4zm16 0h4v8h-4z"/>
            <path fill="#188038" d="M16 26h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4zm-12 6h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4z"/>
            <path fill="#FBBC04" d="M16 20h4v4h-4zm6 0h4v4h-4zm6 0h4v4h-4z"/>
            <rect x="8" y="10" width="32" height="30" rx="3" fill="none" stroke="#DADCE0" stroke-width="2"/>
        </svg>
        @break

    @default
        <svg class="size-6 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
        </svg>
@endswitch
