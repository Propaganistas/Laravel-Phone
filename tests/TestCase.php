<?php namespace Propaganistas\LaravelPhone\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Propaganistas\LaravelPhone\PhoneServiceProvider;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param \Illuminate\Foundation\Application $application
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return [PhoneServiceProvider::class];
    }

    /**
     * Fallback for PHPUnit < 5.2.
     *
     * @param string $exception
     */
    public function expectException($exception)
    {
        if (method_exists(parent::class, 'expectException')) {
            parent::expectException($exception);
        } else {
            $this->setExpectedException($exception);
        }
    }

    /**
     * Fallback for PHPUnit < 5.2.
     *
     * @param string $exception
     */
    public function expectExceptionMessage($message)
    {
        if (method_exists(parent::class, 'expectExceptionMessage')) {
            parent::expectExceptionMessage($message);
        } else {
            $this->setExpectedException($this->getExpectedException(), $message);
        }
    }
}
