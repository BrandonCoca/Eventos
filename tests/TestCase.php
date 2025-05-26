<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use \Illuminate\Foundation\Testing\WithFaker;
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('cache:clear');
        $this->artisan('config:clear');
        $this->artisan('view:clear');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
