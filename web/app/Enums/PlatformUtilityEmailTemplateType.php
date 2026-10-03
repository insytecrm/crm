<?php

namespace App\Enums;

enum PlatformUtilityEmailTemplateType: string
{
    case PartnerCredentials = 'partner_credentials';
    case TrialStarted = 'trial_started';
    case PartnerAccessReset = 'partner_access_reset';
    case QuotationSent = 'quotation_sent';
    case InvoiceSent = 'invoice_sent';

    public function label(): string
    {
        return match ($this) {
            self::PartnerCredentials => __('Partner login credentials'),
            self::TrialStarted => __('Trial started'),
            self::PartnerAccessReset => __('Partner access reset'),
            self::QuotationSent => __('Quotation sent'),
            self::InvoiceSent => __('Invoice sent'),
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::PartnerCredentials => __('Sent when a Channel Partner workspace is onboarded.'),
            self::TrialStarted => __('Sent when a trial workspace is created from a lead.'),
            self::PartnerAccessReset => __('Sent when a partner user access password is reset.'),
            self::QuotationSent => __('Sent when a quotation is emailed from the platform.'),
            self::InvoiceSent => __('Sent when a billing invoice is emailed from Revenue & Billing.'),
        };
    }

    /**
     * @return list<string>
     */
    public function mergeVariables(): array
    {
        return match ($this) {
            self::PartnerCredentials => [
                'admin.name',
                'admin.email',
                'admin.password',
                'login.url',
                'company.name',
            ],
            self::TrialStarted => [
                'admin.name',
                'admin.email',
                'admin.password',
                'login.url',
                'company.name',
                'plan.name',
                'trial.days',
                'trial.ends_at',
            ],
            self::PartnerAccessReset => [
                'admin.name',
                'admin.email',
                'admin.password',
                'login.url',
                'company.name',
            ],
            self::QuotationSent => [
                'owner.name',
                'company.name',
                'quotation.number',
                'plan.name',
                'billing.cycle',
                'quotation.amount',
                'quotation.total',
                'quotation.valid_until',
                'quotation.trial',
            ],
            self::InvoiceSent => [
                'contact.name',
                'company.name',
                'invoice.number',
                'plan.name',
                'billing.cycle',
                'invoice.total',
                'invoice.due_at',
                'invoice.period_start',
                'invoice.period_end',
            ],
        };
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function defaults(): array
    {
        return match ($this) {
            self::PartnerCredentials => [
                'subject' => 'Your {{company.name}} InSyte CRM login',
                'body' => "Hello {{admin.name}},\n\nYour Channel Partner workspace is ready.\n\nLogin URL: {{login.url}}\nEmail: {{admin.email}}\nTemporary password: {{admin.password}}\n\nPlease sign in and change your password after first login.\n\n— InSyte CRM",
            ],
            self::TrialStarted => [
                'subject' => 'Your {{company.name}} InSyte CRM trial is ready',
                'body' => "Hello {{admin.name}},\n\nYour InSyte CRM trial workspace is ready.\n\nPlan: {{plan.name}}\nTrial period: {{trial.days}} days (ends {{trial.ends_at}})\n\nLogin URL: {{login.url}}\nEmail: {{admin.email}}\nTemporary password: {{admin.password}}\n\nPlease sign in and change your password after first login.\n\n— InSyte CRM",
            ],
            self::PartnerAccessReset => [
                'subject' => 'Your InSyte CRM password was reset',
                'body' => "Hello {{admin.name}},\n\nYour login access for {{company.name}} was reset.\n\nLogin URL: {{login.url}}\nEmail: {{admin.email}}\nTemporary password: {{admin.password}}\n\nPlease sign in and change your password.\n\n— InSyte CRM",
            ],
            self::QuotationSent => [
                'subject' => 'Your InSyte CRM quotation {{quotation.number}}',
                'body' => "Hello {{owner.name}},\n\nPlease find your InSyte CRM quotation details below.\n\nQuotation: #{{quotation.number}}\nCompany: {{company.name}}\nPlan: {{plan.name}}\nBilling: {{billing.cycle}}\nAmount: {{quotation.amount}}\nTotal: {{quotation.total}}\nTrial: {{quotation.trial}}\nValid until: {{quotation.valid_until}}\n\nReply to this email if you have any questions.\n\n— InSyte CRM",
            ],
            self::InvoiceSent => [
                'subject' => 'InSyte CRM invoice {{invoice.number}}',
                'body' => "Hello {{contact.name}},\n\nPlease find your InSyte CRM invoice details below.\n\nInvoice: #{{invoice.number}}\nCompany: {{company.name}}\nPlan: {{plan.name}}\nBilling: {{billing.cycle}}\nTotal: {{invoice.total}}\nDue date: {{invoice.due_at}}\nPeriod: {{invoice.period_start}} – {{invoice.period_end}}\n\nReply to this email if you have any questions.\n\n— InSyte CRM",
            ],
        };
    }
}
