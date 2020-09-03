<?php

namespace Sebastienheyd\BoilerplatePackager\Tests;

use Illuminate\Support\Facades\Artisan;

class IntegratedTest extends TestCase
{
    public function test_get_package()
    {
        // php artisan boilerplate:packager get https://gitlab.com/aca-packages/boilerplate-cms.git
        Artisan::call('boilerplate:packager', ['action' => 'get', 'name' => 'https://gitlab.com/aca-packages/boilerplate-cms.git']);
    }
}
