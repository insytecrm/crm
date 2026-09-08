<?php

namespace App\Enums;

enum AiOsTab: string
{
    case Chat = 'chat';
    case LeadInsights = 'lead-insights';
    case Content = 'content';
    case TaskAssistant = 'task-assistant';
    case Reports = 'reports';

    public function label(): string
    {
        return match ($this) {
            self::Chat => __('Chat'),
            self::LeadInsights => __('Lead Insights'),
            self::Content => __('Content & Scripts'),
            self::TaskAssistant => __('Task Assistant'),
            self::Reports => __('Reports & Analysis'),
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Chat => 'chat',
            self::LeadInsights => 'insights',
            self::Content => 'content',
            self::TaskAssistant => 'task',
            self::Reports => 'reports',
        };
    }

    public function accent(): string
    {
        return match ($this) {
            self::Chat => 'violet',
            self::LeadInsights => 'sky',
            self::Content => 'emerald',
            self::TaskAssistant => 'amber',
            self::Reports => 'cyan',
        };
    }

    public function isReady(): bool
    {
        return $this === self::Chat;
    }

    public function comingSoonDescription(): string
    {
        return match ($this) {
            self::Chat => '',
            self::LeadInsights => __('Lead Insights will rank who to call next using your existing priority leads and overdue follow-ups. Use Chat for now, or open Priority Leads.'),
            self::Content => __('Content & Scripts will draft WhatsApp replies and call scripts from lead history. Ask Chat to write a message in the meantime.'),
            self::TaskAssistant => __('Task Assistant will help plan and complete work from your Tasks list. Ask Chat to create or complete a task for now.'),
            self::Reports => __('Reports & Analysis will summarize the numbers already on Reports and Analytics. Those pages stay the source of truth.'),
        };
    }
}
