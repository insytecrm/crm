<?php

namespace App\Models;

use App\Enums\LandingSubmissionType;
use Illuminate\Database\Eloquent\Model;

class LandingSubmission extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'type',
        'full_name',
        'company',
        'phone',
        'email',
        'team_size',
        'plan',
        'billing_cycle',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => LandingSubmissionType::class,
        ];
    }
}
