<?php
/**
 * Created by PhpStorm.
 * User: victorsecuring
 * Date: 06.01.17
 * Time: 1:57 PM
 */

namespace rollun\test\promise;


use PHPUnit\Framework\TestCase;

class TestEnvTest extends TestCase
{
    protected $nameEnvVars = [
        'APP_ENV',
        'MACHINE_NAME',
        'HOST',
    ];

    public function testEnv()
    {
        foreach ($this->nameEnvVars as $envVar) {
            $this->assertNotNull(constant($envVar));
        }
    }
}
