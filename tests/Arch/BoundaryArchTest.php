<?php

arch('controllers do not depend on infrastructure')
    ->expect('Modules\*\Http\Controllers')
    ->toNotUse('Modules\*\Infrastructure');

arch('domain does not depend on infrastructure or http')
    ->expect('Modules\*\Domain')
    ->toNotUse(['Modules\*\Infrastructure', 'Modules\*\Http']);
