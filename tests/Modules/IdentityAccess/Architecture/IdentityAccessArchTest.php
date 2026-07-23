<?php

arch('IdentityAccess module respects architectural boundaries')
    ->expect('Modules\IdentityAccess')
    ->toOnlyUse([
        'Modules\IdentityAccess',
        'Modules\Shared',
        'Modules\Tenant\Application\Contracts',
        'Illuminate',
        'Laravel\Sanctum',
        'Database\Factories',
        'App',
        'response',
    ])
    ->ignoring([
        'Tests',
    ]);
