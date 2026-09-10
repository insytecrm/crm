<?php

namespace App\Console\Commands;

use App\Actions\RecalculateLeadScore;
use App\Models\Lead;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('leads:recalculate-scores')]
#[Description('Recalculate auto lead scores from follow-up and site visit outcomes')]
class RecalculateLeadScoresCommand extends Command
{
    public function handle(RecalculateLeadScore $recalculateLeadScore): int
    {
        $count = 0;

        Lead::query()
            ->orderBy('id')
            ->chunkById(100, function ($leads) use ($recalculateLeadScore, &$count): void {
                foreach ($leads as $lead) {
                    $recalculateLeadScore->handle($lead);
                    $count++;
                }
            });

        $this->components->info(__('Recalculated scores for :count lead(s).', ['count' => $count]));

        return self::SUCCESS;
    }
}
