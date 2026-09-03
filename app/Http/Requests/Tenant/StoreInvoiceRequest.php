<?php

namespace App\Http\Requests\Tenant;

use App\Models\Booking;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'booking_id' => ['required', 'integer', 'exists:bookings,id'],
            'invoice_date' => ['required', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $booking = Booking::query()->find($this->integer('booking_id'));

            if ($booking === null || ! $booking->canCreateInvoice()) {
                $validator->errors()->add(
                    'booking_id',
                    __('Select a booking with an agreement that does not already have an invoice.'),
                );
            }
        });
    }
}
