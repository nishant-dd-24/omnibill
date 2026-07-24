<?php

arch('globals')
    ->expect(['dd', 'dump', 'ray', 'die', 'var_dump'])
    ->not->toBeUsed()
    ->group('arch');

arch('domain_isolation')
    ->expect('Modules\*\Domain')
    ->not->toUse([
        'Modules\*\Application',
        'Modules\*\Infrastructure',
        'Modules\*\Http'
    ])
    ->group('arch');

arch('application_isolation')
    ->expect('Modules\*\Application')
    ->not->toUse([
        'Modules\*\Http',
        'Modules\*\Infrastructure\Adapters',
        'Modules\*\Infrastructure\Database',
        'Modules\*\Infrastructure\Mail',
        'Modules\*\Infrastructure\Repositories',
    ])
    ->group('arch');

arch('http_isolation')
    ->expect('Modules\*\Http')
    ->not->toUse([
        'Modules\*\Infrastructure\Adapters',
        'Modules\*\Infrastructure\Database',
        'Modules\*\Infrastructure\Mail',
        'Modules\*\Infrastructure\Repositories',
    ])
    ->group('arch');

arch('no_direct_controller_db_access')
    ->expect('Modules\*\Http\Controllers')
    ->not->toUse(['Illuminate\Support\Facades\DB', 'Illuminate\Database\Eloquent\Model'])
    ->group('arch');

arch('strict_types')
    ->expect('Modules')
    ->toUseStrictTypes()
    ->group('arch');
