<?php

use App\Enums\InsyteAiTool;

test('list today tool encodes properties as a json object', function () {
    $json = json_encode(InsyteAiTool::ListToday->definition(), JSON_THROW_ON_ERROR);

    expect($json)
        ->toContain('"properties":{}')
        ->toContain('"strict":false')
        ->not->toContain('"properties":[]');
});

test('tools with optional fields disable strict mode', function () {
    $json = json_encode(InsyteAiTool::ScheduleFollowUp->definition(), JSON_THROW_ON_ERROR);

    expect($json)
        ->toContain('"strict":false')
        ->toContain('"lead_id"')
        ->toContain('"notes"');
});
