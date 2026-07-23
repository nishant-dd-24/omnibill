<?php

declare(strict_types=1);

arch('customer module domain does not depend on infrastructure or http')
    ->expect('Modules\Customer\Domain')
    ->not->toUse([
        'Modules\Customer\Infrastructure',
        'Modules\Customer\Http',
    ]);

arch('customer module application does not depend on infrastructure or http')
    ->expect('Modules\Customer\Application')
    ->not->toUse([
        'Modules\Customer\Infrastructure',
        'Modules\Customer\Http',
    ]);

arch('customer module infrastructure only used in application or service providers')
    ->expect('Modules\Customer\Infrastructure')
    ->toOnlyBeUsedIn([
        'Modules\Customer\Application',
        'Modules\Customer\Providers',
    ]);
