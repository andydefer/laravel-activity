<?php

declare(strict_types=1);

namespace AndyDefer\LaravelActivity\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class UnitTestCase extends BaseTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        \Mockery::close();
    }
}
