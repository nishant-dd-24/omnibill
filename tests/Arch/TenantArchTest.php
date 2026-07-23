<?php

test('tenant domain does not depend on application layer', function () {
    expect('Modules\Tenant\Domain')
        ->not->toUse('Modules\Tenant\Application');
});

test('tenant domain does not depend on http layer', function () {
    expect('Modules\Tenant\Domain')
        ->not->toUse('Modules\Tenant\Http');
});

test('tenant application does not depend on http layer', function () {
    expect('Modules\Tenant\Application')
        ->not->toUse('Modules\Tenant\Http');
});

test('tenant module does not depend on other modules domains except shared', function () {
    expect('Modules\Tenant')
        ->not->toUse([
            'Modules\Invoice',
            'Modules\Billing',
            'Modules\Subscription',
            'Modules\IdentityAccess',
        ]);
});
