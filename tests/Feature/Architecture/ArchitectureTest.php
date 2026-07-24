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
        'Modules\*\Http',
    ])
    ->group('arch');

$bannedInfrastructureFolders = ['Adapters', 'Database', 'Mail', 'Repositories'];
$bannedForApplication = ['Modules\*\Http'];
$bannedForHttp = [];

$basePath = dirname(__DIR__, 3);

foreach ($bannedInfrastructureFolders as $folder) {
    $exists = false;
    foreach (glob($basePath . '/Modules/*/Infrastructure/' . $folder) as $dir) {
        if (is_dir($dir)) {
            $exists = true;
            break;
        }
    }
    if ($exists) {
        $bannedForApplication[] = 'Modules\*\Infrastructure\\' . $folder;
        $bannedForHttp[] = 'Modules\*\Infrastructure\\' . $folder;
    }
}

arch('application_isolation')
    ->expect('Modules\*\Application')
    ->not->toUse($bannedForApplication)
    ->group('arch');

if (count($bannedForHttp) > 0) {
    arch('http_isolation')
        ->expect('Modules\*\Http')
        ->not->toUse($bannedForHttp)
        ->group('arch');
}

arch('no_direct_controller_db_access')
    ->expect('Modules\*\Http\Controllers')
    ->not->toUse(['Illuminate\Support\Facades\DB', 'Illuminate\Database\Eloquent\Model'])
    ->group('arch');

arch('strict_types')
    ->expect('Modules')
    ->toUseStrictTypes()
    ->group('arch');
