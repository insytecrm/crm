<?php

namespace App\Console\Commands;

use App\Actions\ExpireQuotations;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('quotations:expire')]
#[Description('Mark draft and sent quotations as expired when past valid until')]
class ExpireQuotationsCommand extends Command
{
    public function handle(ExpireQuotations $expireQuotations): int
    {
        $count = $expireQuotations->handle();

        $this->components->info(__('Expired :count quotation(s).', ['count' => $count]));

        return self::SUCCESS;
    }
}
