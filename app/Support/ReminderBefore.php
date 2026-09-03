<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class ReminderBefore
{
    public function __construct(
        public int $hours,
        public int $minutes,
        public int $seconds,
    ) {}

    public static function fromParts(int $hours, int $minutes, int $seconds): self
    {
        return new self($hours, $minutes, $seconds);
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(bool $requiredWhenEnabled = true): array
    {
        $required = $requiredWhenEnabled ? ['required_if:add_reminder,1'] : ['nullable'];

        return [
            'add_reminder' => ['sometimes', 'boolean'],
            'reminder_hours' => [...$required, 'integer', 'min:0', 'max:168'],
            'reminder_minutes' => [...$required, 'integer', 'min:0', 'max:59'],
            'reminder_seconds' => [...$required, 'integer', 'min:0', 'max:59'],
        ];
    }

    public static function prepare(Request $request): void
    {
        if ($request->has('add_reminder')) {
            $request->merge([
                'add_reminder' => filter_var($request->input('add_reminder'), FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
            ]);
        }
    }

    public static function fromRequest(Request $request): ?self
    {
        if (! (bool) $request->input('add_reminder')) {
            return null;
        }

        return self::fromParts(
            (int) $request->input('reminder_hours', 0),
            (int) $request->input('reminder_minutes', 0),
            (int) $request->input('reminder_seconds', 0),
        );
    }

    /**
     * @return array{remind_at: CarbonInterface, reminder_before_seconds: int, reminder_dismissed_at: null}|array{remind_at: null, reminder_before_seconds: null, reminder_dismissed_at: null}
     */
    public static function attributesFor(?self $reminder, ?CarbonInterface $baseAt): array
    {
        if ($reminder === null || $baseAt === null) {
            return [
                'remind_at' => null,
                'reminder_before_seconds' => null,
                'reminder_dismissed_at' => null,
            ];
        }

        return [
            'remind_at' => $reminder->remindAt($baseAt),
            'reminder_before_seconds' => $reminder->totalSeconds(),
            'reminder_dismissed_at' => null,
        ];
    }

    public static function afterValidation(Validator $validator, ?CarbonInterface $baseAt, string $baseAttribute = 'scheduled_at'): void
    {
        if (! (bool) data_get($validator->getData(), 'add_reminder')) {
            return;
        }

        $reminder = self::fromParts(
            (int) data_get($validator->getData(), 'reminder_hours', 0),
            (int) data_get($validator->getData(), 'reminder_minutes', 0),
            (int) data_get($validator->getData(), 'reminder_seconds', 0),
        );

        if ($reminder->totalSeconds() <= 0) {
            $validator->errors()->add('reminder_hours', __('Enter a reminder time greater than zero.'));

            return;
        }

        if ($baseAt === null) {
            $validator->errors()->add($baseAttribute, __('Set a date and time before adding a reminder.'));

            return;
        }

        if ($reminder->remindAt($baseAt)->isPast()) {
            $validator->errors()->add('reminder_hours', __('Reminder time must be in the future.'));
        }
    }

    public function totalSeconds(): int
    {
        return ($this->hours * 3600) + ($this->minutes * 60) + $this->seconds;
    }

    public function remindAt(CarbonInterface $baseAt): CarbonInterface
    {
        return $baseAt->copy()->subSeconds($this->totalSeconds());
    }
}
