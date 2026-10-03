<?php

namespace App\Actions;

use App\Models\Booking;

class GenerateInvoicePdf
{
    public function handle(Booking $booking): string
    {
        $booking->loadMissing(['property', 'lead']);

        $lines = [
            __('Invoice'),
            $booking->invoice_number ?? Booking::invoiceNumberFor($booking->id),
            '',
            __('Property').': '.$booking->property->project_name,
            __('Unit').': '.$booking->unit_number,
            __('Configuration').': '.$booking->configuration_name,
            __('Lead').': '.($booking->lead?->name ?? '—'),
            __('Agreement Value').': Rs '.number_format($booking->agreement_value),
            __('Payout (%)').': '.($booking->payout_percent ?? '0').'%',
            __('Invoice Amount').': Rs '.number_format($booking->payout_amount ?? 0),
            __('Agreement Date').': '.($booking->agreement_date?->format('M j, Y') ?? '-'),
            __('Invoice Date').': '.($booking->invoice_date?->format('M j, Y') ?? '-'),
        ];

        if (filled($booking->invoice_notes)) {
            $lines[] = '';
            $lines[] = __('Notes').': '.$booking->invoice_notes;
        }

        return $this->buildPdf($lines);
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function buildPdf(array $lines): string
    {
        $streamLines = [
            'BT',
            '/F1 12 Tf',
            '50 750 Td',
        ];

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $streamLines[] = '0 -18 Td';
            }

            $streamLines[] = '('.$this->escapePdfText($line).') Tj';
        }

        $streamLines[] = 'ET';
        $stream = implode("\n", $streamLines);
        $streamLength = strlen($stream);

        $objects = [
            '1 0 obj<< /Type /Catalog /Pages 2 0 R >> endobj',
            '2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >> endobj',
            '3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >> endobj',
            "4 0 obj<< /Length {$streamLength} >> stream\n{$stream}\nendstream endobj",
            '5 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >> endobj',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object."\n";
        }

        $xrefOffset = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($index = 1; $index <= count($objects); $index++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$index]);
        }

        $pdf .= 'trailer<< /Size '.(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefOffset}\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text) ?: $text;

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $ascii);
    }
}
