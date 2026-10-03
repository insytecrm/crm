<?php

namespace App\Support\Ai;

class InsyteAiPageContent
{
    /**
     * @return list<array{title: string, prompt: string, accent: string, icon: string}>
     */
    public static function examples(): array
    {
        return [
            [
                'title' => __('Schedule a site visit'),
                'prompt' => __('Schedule a site visit for Rahul Sharma on 12 Sep at 4 PM'),
                'accent' => 'sky',
                'icon' => 'site-visit',
            ],
            [
                'title' => __('Write a WhatsApp reply'),
                'prompt' => __('Write a WhatsApp reply for my latest follow-up lead'),
                'accent' => 'emerald',
                'icon' => 'message',
            ],
            [
                'title' => __('Prepare next call'),
                'prompt' => __('What should I cover on my next call today?'),
                'accent' => 'amber',
                'icon' => 'call',
            ],
            [
                'title' => __('Analyze remarks'),
                'prompt' => __('Summarize open follow-ups that are overdue'),
                'accent' => 'violet',
                'icon' => 'analyze',
            ],
            [
                'title' => __('Find leads'),
                'prompt' => __('Find leads named Rahul'),
                'accent' => 'cyan',
                'icon' => 'search',
            ],
            [
                'title' => __('Create a task'),
                'prompt' => __('Create a task to send the brochure to my latest lead'),
                'accent' => 'rose',
                'icon' => 'task',
            ],
        ];
    }

    /**
     * @return list<array{label: string, href: string, accent: string, icon: string}>
     */
    public static function quickActions(): array
    {
        return [
            [
                'label' => __('Add Lead'),
                'href' => route('tenant.leads.index', ['add' => 1]),
                'accent' => 'sky',
                'icon' => 'lead',
            ],
            [
                'label' => __('Schedule Follow-up'),
                'href' => route('tenant.follow-ups.index'),
                'accent' => 'amber',
                'icon' => 'follow-up',
            ],
            [
                'label' => __('Schedule Site Visit'),
                'href' => route('tenant.site-visits.index'),
                'accent' => 'emerald',
                'icon' => 'site-visit',
            ],
            [
                'label' => __('Update Lead'),
                'href' => route('tenant.leads.index'),
                'accent' => 'cyan',
                'icon' => 'edit',
            ],
            [
                'label' => __('Write Message'),
                'href' => route('tenant.ai.index', ['prompt' => __('Write a WhatsApp reply for my latest follow-up lead')]),
                'accent' => 'violet',
                'icon' => 'message',
            ],
            [
                'label' => __('Prepare Call Script'),
                'href' => route('tenant.ai.index', ['prompt' => __('Prepare a call script for my next follow-up today')]),
                'accent' => 'rose',
                'icon' => 'call',
            ],
        ];
    }

    public static function tip(): string
    {
        $tips = [
            __('Leads contacted within 5 minutes are much more likely to convert.'),
            __('Complete today\'s follow-ups before adding new leads.'),
            __('After a site visit, log the outcome while the conversation is fresh.'),
            __('A short WhatsApp note after a missed call keeps the lead warm.'),
        ];

        return $tips[now()->dayOfYear % count($tips)];
    }
}
