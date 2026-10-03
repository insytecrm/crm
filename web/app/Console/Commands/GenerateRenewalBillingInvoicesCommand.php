<?php

namespace App\Console\Commands;

use App\Actions\CreateRenewalBillingInvoices;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('billing:generate-renewal-invoices')]
#[Description('Create renewal invoices one day before subscription billing and mark overdue invoices')]
class GenerateRenewalBillingInvoicesCommand extends Command
{
    public function handle(CreateRenewalBillingInvoices $createRenewalBillingInvoices): int
    {
        $created = $createRenewalBillingInvoices->handle();
        $overdue = $createRenewalBillingInvoices->markOverdueInvoices();

        $this->components->info(__('Created :created renewal invoice(s) and marked :overdue invoice(s) overdue.', [
            'created' => $created,
            'overdue' => $overdue,
        ]));

        return self::SUCCESS;
    }
}
