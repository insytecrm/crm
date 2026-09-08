@props([
    'name',
])

@php
    $class = 'h-5 w-5 shrink-0';
@endphp

@switch($name)
    @case('dashboard')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M4.5 10.2 12 4l7.5 6.2V19a1.5 1.5 0 0 1-1.5 1.5h-3.75v-5.25h-4.5V20.5H6A1.5 1.5 0 0 1 4.5 19V10.2Z" fill="#60A5FA"/>
            <path d="M9.75 20.5V15.25h4.5V20.5h3.75A1.5 1.5 0 0 0 19.5 19v-8.4L12 5.2 4.5 10.6V19A1.5 1.5 0 0 0 6 20.5h3.75Z" fill="#2563EB"/>
            <path d="M10.2 15.25h3.6V20.5h-3.6V15.25Z" fill="#93C5FD"/>
        </svg>
        @break

    @case('ai')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 3.2 13.4 7.1 17.4 8.4 13.4 9.7 12 13.6 10.6 9.7 6.6 8.4 10.6 7.1 12 3.2Z" fill="#C4B5FD"/>
            <path d="M12 5.1 12.9 7.6 15.5 8.4 12.9 9.2 12 11.7 11.1 9.2 8.5 8.4 11.1 7.6 12 5.1Z" fill="#7C3AED"/>
            <path d="M18.2 13.2 18.9 15.2 21 15.9 18.9 16.6 18.2 18.6 17.5 16.6 15.4 15.9 17.5 15.2 18.2 13.2Z" fill="#A78BFA"/>
            <path d="M6.2 14.4 6.8 16.1 8.6 16.7 6.8 17.3 6.2 19 5.6 17.3 3.8 16.7 5.6 16.1 6.2 14.4Z" fill="#8B5CF6"/>
        </svg>
        @break

    @case('leads')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="9" cy="8.5" r="3.2" fill="#93C5FD"/>
            <path d="M3.8 18.8c.4-3.2 2.7-5 5.2-5s4.8 1.8 5.2 5c.05.4-.25.7-.65.7H4.45c-.4 0-.7-.3-.65-.7Z" fill="#3B82F6"/>
            <circle cx="15.8" cy="9.2" r="2.6" fill="#60A5FA"/>
            <path d="M13.2 18.8c.35-2.2 1.8-3.5 3.5-3.5 1.7 0 3.15 1.3 3.5 3.5.05.35-.2.6-.55.6h-5.9c-.35 0-.6-.25-.55-.6Z" fill="#2563EB"/>
        </svg>
        @break

    @case('priority')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 3.2 14.4 9l6.2.5-4.7 4 1.5 6-5.4-3.3L6.6 19.5l1.5-6-4.7-4L9.6 9 12 3.2Z" fill="#FBBF24"/>
            <path d="M12 5.4 13.7 9.6l4.5.35-3.4 2.9 1.1 4.4L12 14.9l-3.9 2.35 1.1-4.4-3.4-2.9 4.5-.35L12 5.4Z" fill="#F59E0B"/>
        </svg>
        @break

    @case('converted')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" fill="#34D399"/>
            <circle cx="12" cy="12" r="7.2" fill="#10B981"/>
            <path d="M8.2 12.2 10.7 14.7 15.8 9.4" stroke="#ECFDF5" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('lost')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9" fill="#FCA5A5"/>
            <circle cx="12" cy="12" r="7.2" fill="#EF4444"/>
            <path d="M9.2 9.2 14.8 14.8M14.8 9.2 9.2 14.8" stroke="#FEF2F2" stroke-width="2.2" stroke-linecap="round"/>
        </svg>
        @break

    @case('duplicates')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="7.5" y="5" width="11" height="13" rx="2" fill="#93C5FD"/>
            <rect x="4.5" y="7.5" width="11" height="13" rx="2" fill="#3B82F6"/>
            <path d="M7.2 12.2h5.6M7.2 15.2h4.2" stroke="#DBEAFE" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        @break

    @case('activities')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="5" width="17" height="15" rx="3" fill="#F9A8D4"/>
            <rect x="3.5" y="5" width="17" height="4.5" rx="3" fill="#EC4899"/>
            <rect x="3.5" y="7.5" width="17" height="2" fill="#EC4899"/>
            <rect x="7" y="3.5" width="1.8" height="4" rx="0.9" fill="#BE185D"/>
            <rect x="15.2" y="3.5" width="1.8" height="4" rx="0.9" fill="#BE185D"/>
            <circle cx="8.2" cy="13.2" r="1.1" fill="#BE185D"/>
            <circle cx="12" cy="13.2" r="1.1" fill="#BE185D"/>
            <circle cx="15.8" cy="13.2" r="1.1" fill="#BE185D"/>
            <circle cx="8.2" cy="16.6" r="1.1" fill="#F472B6"/>
            <circle cx="12" cy="16.6" r="1.1" fill="#F472B6"/>
        </svg>
        @break

    @case('follow-ups')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7.2 3.8h3.1c.6 0 1.1.4 1.2 1l.5 2.6c.1.4 0 .8-.3 1.1l-1.3 1.3c1.2 2.2 3.1 4.1 5.3 5.3l1.3-1.3c.3-.3.7-.4 1.1-.3l2.6.5c.6.1 1 .6 1 1.2v3.1c0 .7-.5 1.2-1.2 1.2C11.8 20.5 3.5 12.2 3.5 5c0-.7.5-1.2 1.2-1.2h2.5Z" fill="#F472B6"/>
            <path d="M8.4 5.2h1.5l.4 2-1.5 1.5c-.4.4-.5 1-.2 1.5 1.5 2.4 3.5 4.4 5.9 5.9.5.3 1.1.2 1.5-.2l1.5-1.5 2 .4v1.5c0 .2-.1.4-.3.4C12.4 18.2 5.8 11.6 5.8 5.5c0-.2.2-.3.4-.3h2.2Z" fill="#DB2777"/>
        </svg>
        @break

    @case('site-visits')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 21s6.5-5.1 6.5-10.2A6.5 6.5 0 0 0 12 4.3a6.5 6.5 0 0 0-6.5 6.5C5.5 15.9 12 21 12 21Z" fill="#F9A8D4"/>
            <path d="M12 19.2s5.2-4.2 5.2-8.4A5.2 5.2 0 0 0 12 5.6a5.2 5.2 0 0 0-5.2 5.2c0 4.2 5.2 8.4 5.2 8.4Z" fill="#EC4899"/>
            <circle cx="12" cy="10.8" r="2.4" fill="#FCE7F3"/>
            <circle cx="12" cy="10.8" r="1.4" fill="#BE185D"/>
        </svg>
        @break

    @case('tasks')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="9.2" fill="#86EFAC"/>
            <circle cx="12" cy="12" r="7.4" fill="#22C55E"/>
            <path d="M8.1 12.1 10.7 14.7 15.9 9.3" stroke="#F0FDF4" stroke-width="2.3" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('properties')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="8" width="7.5" height="12.5" rx="1.2" fill="#93C5FD"/>
            <rect x="13" y="4.5" width="7.5" height="16" rx="1.2" fill="#3B82F6"/>
            <rect x="5.1" y="10.2" width="1.5" height="1.5" rx="0.3" fill="#EFF6FF"/>
            <rect x="7.6" y="10.2" width="1.5" height="1.5" rx="0.3" fill="#EFF6FF"/>
            <rect x="5.1" y="13.2" width="1.5" height="1.5" rx="0.3" fill="#EFF6FF"/>
            <rect x="7.6" y="13.2" width="1.5" height="1.5" rx="0.3" fill="#EFF6FF"/>
            <rect x="14.6" y="6.8" width="1.5" height="1.5" rx="0.3" fill="#DBEAFE"/>
            <rect x="17.2" y="6.8" width="1.5" height="1.5" rx="0.3" fill="#DBEAFE"/>
            <rect x="14.6" y="9.8" width="1.5" height="1.5" rx="0.3" fill="#DBEAFE"/>
            <rect x="17.2" y="9.8" width="1.5" height="1.5" rx="0.3" fill="#DBEAFE"/>
            <rect x="14.6" y="12.8" width="1.5" height="1.5" rx="0.3" fill="#DBEAFE"/>
            <rect x="17.2" y="12.8" width="1.5" height="1.5" rx="0.3" fill="#DBEAFE"/>
            <rect x="15.5" y="16.8" width="2.5" height="3.7" rx="0.4" fill="#1D4ED8"/>
        </svg>
        @break

    @case('bookings')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M5 4.8c0-.7.6-1.3 1.3-1.3h8.2c.3 0 .6.1.8.4l4.4 5.3c.4.5.4 1.2 0 1.7l-4.4 5.3c-.2.3-.5.4-.8.4H6.3C5.6 16.6 5 16 5 15.3V4.8Z" fill="#FB923C"/>
            <path d="M5 4.8c0-.7.6-1.3 1.3-1.3h7.5v13.1H6.3C5.6 16.6 5 16 5 15.3V4.8Z" fill="#F97316"/>
            <circle cx="9.2" cy="9.2" r="2.1" fill="#FFEDD5"/>
            <path d="M9.2 7.7v2.1l1.3.8" stroke="#C2410C" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        @break

    @case('revenue')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <ellipse cx="12" cy="18.2" rx="6.8" ry="2.3" fill="#FCD34D"/>
            <path d="M5.2 10.5c0-2.8 3-5.1 6.8-5.1s6.8 2.3 6.8 5.1v7.2c0 2.8-3 5.1-6.8 5.1s-6.8-2.3-6.8-5.1v-7.2Z" fill="#FBBF24"/>
            <path d="M5.2 10.5c0-2.8 3-5.1 6.8-5.1s6.8 2.3 6.8 5.1c0 2.8-3 5.1-6.8 5.1s-6.8-2.3-6.8-5.1Z" fill="#F59E0B"/>
            <path d="M12 8.4v1.1M12 14.2v1.1M10.2 10.2c.4-.7 1.1-1.1 1.8-1.1 1.1 0 1.9.6 1.9 1.5s-.8 1.4-1.9 1.7c-1.1.3-1.9.8-1.9 1.7 0 .9.9 1.5 2 1.5.8 0 1.5-.3 1.9-1" stroke="#92400E" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break

    @case('invoices')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7 3.5h7.2L18.5 8v12.2c0 .7-.6 1.3-1.3 1.3H7c-.7 0-1.3-.6-1.3-1.3V4.8c0-.7.6-1.3 1.3-1.3Z" fill="#FDE68A"/>
            <path d="M14.2 3.5 18.5 8h-3c-.7 0-1.3-.6-1.3-1.3V3.5Z" fill="#F59E0B"/>
            <path d="M8.4 11.2h7.2M8.4 14h5.5M8.4 16.8h4.2" stroke="#B45309" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break

    @case('reports')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.8" y="12.5" width="4.2" height="7.2" rx="1.1" fill="#34D399"/>
            <rect x="9.9" y="8" width="4.2" height="11.7" rx="1.1" fill="#60A5FA"/>
            <rect x="16" y="4.5" width="4.2" height="15.2" rx="1.1" fill="#C084FC"/>
        </svg>
        @break

    @case('analytics')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M12 4.2a7.8 7.8 0 1 0 7.8 7.8H12V4.2Z" fill="#60A5FA"/>
            <path d="M13.4 4.4A7.8 7.8 0 0 1 19.8 12H13.4V4.4Z" fill="#A78BFA"/>
            <circle cx="12" cy="12" r="2.2" fill="#DBEAFE"/>
        </svg>
        @break

    @case('teams')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="8.2" r="2.8" fill="#C084FC"/>
            <path d="M7.2 18.7c.35-2.8 2.3-4.4 4.8-4.4s4.45 1.6 4.8 4.4c.05.35-.2.6-.55.6H7.75c-.35 0-.6-.25-.55-.6Z" fill="#9333EA"/>
            <circle cx="6.4" cy="9.1" r="2.2" fill="#DDD6FE"/>
            <path d="M3.3 18.2c.25-1.9 1.5-3 3.1-3 .5 0 1 .1 1.4.3-.55.85-.9 1.9-1 3.1-.05.35-.3.6-.6.6H3.85c-.3 0-.55-.25-.55-.6Z" fill="#7E22CE"/>
            <circle cx="17.6" cy="9.1" r="2.2" fill="#DDD6FE"/>
            <path d="M16.2 15.5c.4-.2.9-.3 1.4-.3 1.6 0 2.85 1.1 3.1 3 .05.35-.2.6-.55.6h-2.35c-.3 0-.55-.25-.6-.6-.1-1.2-.45-2.25-1-3.1Z" fill="#7E22CE"/>
        </svg>
        @break

    @case('performance')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="13.5" width="4" height="6.2" rx="1" fill="#DDD6FE"/>
            <rect x="10" y="9.2" width="4" height="10.5" rx="1" fill="#A78BFA"/>
            <rect x="16.5" y="5.5" width="4" height="14.2" rx="1" fill="#7C3AED"/>
            <path d="M5 9.2 10.2 7l4.1 2.4L19 4.8" stroke="#F5F3FF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            <circle cx="19" cy="4.8" r="1.3" fill="#F59E0B"/>
        </svg>
        @break

    @case('automations')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M13.2 2.8 6.4 13.1h4.3L9.8 21.2 18 9.4h-4.5l-.3-6.6Z" fill="#FDBA74"/>
            <path d="M13.2 2.8 8.1 11.2h3.4L10.4 18.8 16.8 9.4h-3.3L13.2 2.8Z" fill="#F59E0B"/>
        </svg>
        @break

    @case('workflows')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.5" y="4.5" width="6" height="4.2" rx="1.2" fill="#FDBA74"/>
            <rect x="14.5" y="9.9" width="6" height="4.2" rx="1.2" fill="#FB923C"/>
            <rect x="3.5" y="15.3" width="6" height="4.2" rx="1.2" fill="#F59E0B"/>
            <path d="M9.5 6.6h3.2c1.3 0 2.3 1 2.3 2.3v2.1M9.5 17.4h3.2c1.3 0 2.3-1 2.3-2.3v-1" stroke="#EA580C" stroke-width="1.7" stroke-linecap="round"/>
            <circle cx="14.5" cy="12" r="1.1" fill="#EA580C"/>
        </svg>
        @break

    @case('templates')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="5" y="3.8" width="14" height="16.4" rx="2" fill="#FDBA74"/>
            <rect x="5" y="3.8" width="14" height="4" rx="2" fill="#F59E0B"/>
            <rect x="5" y="6.2" width="14" height="1.6" fill="#F59E0B"/>
            <path d="M8 11h8M8 14h6M8 17h4.5" stroke="#9A3412" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break

    @case('settings')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M10.1 3.7h3.8l.5 2.1c.5.2 1 .5 1.4.8l2.1-.7 1.9 3.3-1.6 1.4c.1.5.1 1 .1 1.4l1.6 1.4-1.9 3.3-2.1-.7c-.4.4-.9.7-1.4.8l-.5 2.1h-3.8l-.5-2.1a6.3 6.3 0 0 1-1.4-.8l-2.1.7-1.9-3.3 1.6-1.4a6.5 6.5 0 0 1-.1-1.4L4.2 9.2l1.9-3.3 2.1.7c.4-.4.9-.7 1.4-.8l.5-2.1Z" fill="#64748B"/>
            <circle cx="12" cy="12" r="3.2" fill="#CBD5E1"/>
            <circle cx="12" cy="12" r="1.7" fill="#334155"/>
        </svg>
        @break

    @case('partners')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="8.2" r="2.8" fill="#93C5FD"/>
            <path d="M7.2 18.7c.35-2.8 2.3-4.4 4.8-4.4s4.45 1.6 4.8 4.4c.05.35-.2.6-.55.6H7.75c-.35 0-.6-.25-.55-.6Z" fill="#2563EB"/>
            <circle cx="6.4" cy="9.1" r="2.2" fill="#BFDBFE"/>
            <path d="M3.3 18.2c.25-1.9 1.5-3 3.1-3 .5 0 1 .1 1.4.3-.55.85-.9 1.9-1 3.1-.05.35-.3.6-.6.6H3.85c-.3 0-.55-.25-.55-.6Z" fill="#1D4ED8"/>
            <circle cx="17.6" cy="9.1" r="2.2" fill="#BFDBFE"/>
            <path d="M16.2 15.5c.4-.2.9-.3 1.4-.3 1.6 0 2.85 1.1 3.1 3 .05.35-.2.6-.55.6h-2.35c-.3 0-.55-.25-.6-.6-.1-1.2-.45-2.25-1-3.1Z" fill="#1D4ED8"/>
        </svg>
        @break

    @case('plans')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="4" y="4.5" width="16" height="15" rx="2.2" fill="#A5B4FC"/>
            <rect x="4" y="4.5" width="16" height="4.2" rx="2.2" fill="#6366F1"/>
            <rect x="4" y="7" width="16" height="1.7" fill="#6366F1"/>
            <path d="M7.2 11.2h9.6M7.2 14.2h6.8M7.2 17.2h4.5" stroke="#312E81" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break

    @case('quotations')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M7 3.5h7.2L18.5 8v12.2c0 .7-.6 1.3-1.3 1.3H7c-.7 0-1.3-.6-1.3-1.3V4.8c0-.7.6-1.3 1.3-1.3Z" fill="#99F6E4"/>
            <path d="M14.2 3.5 18.5 8h-3c-.7 0-1.3-.6-1.3-1.3V3.5Z" fill="#14B8A6"/>
            <path d="M8.4 11.2h7.2M8.4 14h5.5M8.4 16.8h4.2" stroke="#0F766E" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
        @break

    @case('integrations')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <rect x="3.8" y="3.8" width="7.2" height="7.2" rx="1.8" fill="#67E8F9"/>
            <rect x="13" y="3.8" width="7.2" height="7.2" rx="1.8" fill="#22D3EE"/>
            <rect x="3.8" y="13" width="7.2" height="7.2" rx="1.8" fill="#06B6D4"/>
            <rect x="13" y="13" width="7.2" height="7.2" rx="1.8" fill="#0891B2"/>
            <path d="M11 7.4h2M7.4 11v2M16.6 11v2M11 16.6h2" stroke="#ECFEFF" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
        @break

    @case('utilities')
        <svg {{ $attributes->class($class) }} viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <path d="M14.2 3.8c.4 1.8 1.8 3.2 3.6 3.6L15.5 9.7a5.8 5.8 0 0 1-7.2 7.2L4.8 20.4 3.6 19.2l3.5-3.5a5.8 5.8 0 0 1 7.2-7.2l2.3-2.3c-.4-.4-.8-.9-1.1-1.4l-1.3-.9Z" fill="#FDBA74"/>
            <path d="M16.4 5.2c.7 1.2 1.8 2.2 3.1 2.7l-1.5 1.5a4.4 4.4 0 0 1-5.5 5.5L9.4 18l-1.2-1.2 3.1-3.1a4.4 4.4 0 0 1 5.5-5.5l1.5-1.5c-.4-.5-.8-1-1.1-1.5l-.8-.5Z" fill="#F59E0B"/>
            <circle cx="17.8" cy="6.2" r="1.2" fill="#FFEDD5"/>
        </svg>
        @break

    @default
        <span {{ $attributes->class($class.' inline-block rounded bg-slate-200') }} aria-hidden="true"></span>
@endswitch
