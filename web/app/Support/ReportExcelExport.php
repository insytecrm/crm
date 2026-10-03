<?php

namespace App\Support;

class ReportExcelExport
{
    /**
     * @param  array{
     *     kpis: array<string, int>,
     *     new_leads: list<array{key: string, label: string, count: int}>,
     *     activity_trend: list<array{key: string, label: string, count: int}>,
     *     by_status: list<array{key: string, label: string, count: int, percentage: float, color: string}>,
     *     by_property_type: list<array{key: string, label: string, count: int, percentage: float, color: string}>,
     *     by_user: list<array{key: string, label: string, count: int, percentage: float, color: string}>,
     *     agent_performance: list<array{key: string, agent: string, total_leads: int, active: int, converted: int, lost: int, conversion_ratio: float}>,
     * }  $analytics
     */
    public function __construct(
        private readonly array $analytics,
        private readonly string $periodLabel,
        private readonly string $generatedAt,
    ) {}

    public function toSpreadsheetXml(): string
    {
        $sheets = [
            $this->sheet('Summary', [
                ['Period', $this->periodLabel],
                ['Generated At', $this->generatedAt],
                [],
                ['Metric', 'Value'],
                [__('Total Leads'), $this->analytics['kpis']['total_leads']],
                [__('Converted Leads'), $this->analytics['kpis']['converted_leads']],
                [__('Active'), $this->analytics['kpis']['active_leads']],
                [__('Total Activities'), $this->analytics['kpis']['total_activities']],
                [__('Today’s Activities'), $this->analytics['kpis']['todays_activities']],
                [__('Lost Leads'), $this->analytics['kpis']['lost_leads']],
            ]),
            $this->sheet('New Leads', $this->trendRows($this->analytics['new_leads'])),
            $this->sheet('Activity Trend', $this->trendRows($this->analytics['activity_trend'])),
            $this->sheet('Leads by Status', $this->segmentRows($this->analytics['by_status'])),
            $this->sheet('By Property Type', $this->segmentRows($this->analytics['by_property_type'])),
            $this->sheet('Leads by User', $this->segmentRows($this->analytics['by_user'])),
            $this->sheet('Agent Performance', $this->agentPerformanceRows($this->analytics['agent_performance'] ?? [])),
        ];

        return '<?xml version="1.0" encoding="UTF-8"?>'."\n"
            .'<?mso-application progid="Excel.Sheet"?>'."\n"
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:o="urn:schemas-microsoft-com:office:office"'
            .' xmlns:x="urn:schemas-microsoft-com:office:excel"'
            .' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'
            .' xmlns:html="http://www.w3.org/TR/REC-html40">'
            .implode('', $sheets)
            .'</Workbook>';
    }

    /**
     * @param  list<array{key: string, label: string, count: int}>  $points
     * @return list<list<string|int|float>>
     */
    private function trendRows(array $points): array
    {
        $rows = [['Period', 'Count']];

        foreach ($points as $point) {
            $rows[] = [$point['label'], $point['count']];
        }

        return $rows;
    }

    /**
     * @param  list<array{key: string, label: string, count: int, percentage: float, color: string}>  $segments
     * @return list<list<string|int|float>>
     */
    private function segmentRows(array $segments): array
    {
        $rows = [['Label', 'Count', 'Percentage']];

        foreach ($segments as $segment) {
            $rows[] = [$segment['label'], $segment['count'], $segment['percentage']];
        }

        return $rows;
    }

    /**
     * @param  list<array{key: string, agent: string, total_leads: int, active: int, converted: int, lost: int, conversion_ratio: float}>  $rows
     * @return list<list<string|int|float>>
     */
    private function agentPerformanceRows(array $rows): array
    {
        $exportRows = [[
            __('Agent'),
            __('Total Leads'),
            __('Active'),
            __('Converted'),
            __('Lost'),
            __('Conversion Ratio'),
        ]];

        foreach ($rows as $row) {
            $exportRows[] = [
                $row['agent'],
                $row['total_leads'],
                $row['active'],
                $row['converted'],
                $row['lost'],
                $row['conversion_ratio'],
            ];
        }

        return $exportRows;
    }

    /**
     * @param  list<list<string|int|float|null>>  $rows
     */
    private function sheet(string $name, array $rows): string
    {
        $xml = '<Worksheet ss:Name="'.$this->escapeAttribute($name).'"><Table>';

        foreach ($rows as $row) {
            $xml .= '<Row>';

            foreach ($row as $cell) {
                if ($cell === null || $cell === '') {
                    $xml .= '<Cell/>';

                    continue;
                }

                if (is_int($cell) || is_float($cell)) {
                    $xml .= '<Cell><Data ss:Type="Number">'.$cell.'</Data></Cell>';

                    continue;
                }

                $xml .= '<Cell><Data ss:Type="String">'.$this->escape((string) $cell).'</Data></Cell>';
            }

            $xml .= '</Row>';
        }

        return $xml.'</Table></Worksheet>';
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    private function escapeAttribute(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
