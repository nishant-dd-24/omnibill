<?php

arch('Subscription module respects architectural boundaries')
    ->expect('Modules\Subscription')
    ->toOnlyUse([
        'Modules\Subscription',
        'Modules\Shared',
        'Modules\Tenant',
        'Illuminate',
        'Database\Factories',
        'App',
        'response',
    ])
    ->ignoring([
        'Tests',
    ]);
