<?php

use App\Support\RenderMessageTemplate;

test('renderer replaces known template variables', function () {
    $text = (new RenderMessageTemplate)->handle(
        'Hi {{lead.name}}, this is {{user.name}} from {{company.name}} about {{property.project_name}}.',
        [
            'lead.name' => 'Rahul Sharma',
            'user.name' => 'Amit Desai',
            'company.name' => 'Acme Realty',
            'property.project_name' => 'Riverfront Residences',
        ],
    );

    expect($text)->toBe('Hi Rahul Sharma, this is Amit Desai from Acme Realty about Riverfront Residences.');
});

test('renderer leaves unknown tokens and blanks null values', function () {
    $text = (new RenderMessageTemplate)->handle(
        'Hi {{lead.name}}, phone {{lead.phone}} extra {{lead.secret}}.',
        [
            'lead.name' => 'Rahul Sharma',
            'lead.phone' => null,
        ],
    );

    expect($text)->toBe('Hi Rahul Sharma, phone  extra {{lead.secret}}.');
});
