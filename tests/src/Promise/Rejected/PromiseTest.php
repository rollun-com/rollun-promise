<?php

namespace rollun\test\promise\Promise\Rejected;

use rollun\promise\Promise\Promise;
use rollun\promise\Promise\PromiseInterface;
use rollun\promise\Promise\Exception as PromiseException;
use rollun\promise\Promise\Exception\AlreadyRejectedException;
use zaboy\res\Di\InsideConstruct;
use rollun\test\promise\Promise\DataProvider;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-09-24 at 00:05:36.
 */
class PromiseTest extends DataProvider
{

    /**
     * @var Promise
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $container = include 'config/container.php';
        InsideConstruct::setContainer($container);
    }

    //====================== getState(); =======================================

    public function test_getState()
    {
        $promise = new Promise;
        $promise->reject('foo');
        $this->assertEquals(PromiseInterface::REJECTED, $promise->getState());
    }

    //====================== wait(); ===========================================
    public function test_wait_false()
    {
        $promise = new Promise;
        $promise->reject('foo');
        $this->assertContainsOnlyInstancesOf(PromiseException::class, [$promise->wait(false)]);
        $this->assertEquals('foo', $promise->wait(false)->getMessage());
    }

    public function test_wait_true()
    {
        $promise = new Promise;
        $promise->reject('foo');
        $this->setExpectedException(PromiseException::class, 'foo');
        $promise->wait(true);
    }

    //====================== reject(); =========================================
    public function test_reject_PromiseException_same_reason_exception()
    {
        $promise = new Promise;
        $promise->reject('foo');
        $promise->reject(new PromiseException('foo'));
        $this->setExpectedException(PromiseException::class, 'foo');
        $promise->wait(true);
    }

    public function test_reject_PromiseException_same_reason_string()
    {
        $promise = new Promise;
        $promise->reject('foo');
        $promise->reject('foo');
        $this->setExpectedException(PromiseException::class, 'foo');
        $promise->wait(true);
    }

    /**
     * @dataProvider provider_Types()
     */
    public function test_reject_anyTypes($in)
    {
        $promise = new Promise;
        $promise->reject('bar');
        $this->setExpectedExceptionRegExp(AlreadyRejectedException::class, '|.*Cannot reject a rejected promise|');
        $promise->reject($in);
    }

    //====================== resolve(); ========================================
    /**
     * @dataProvider provider_Types()
     */
    public function test_resolve_anyTypes($in)
    {
        $promise = new Promise;
        $promise->reject('foo');
        $this->setExpectedExceptionRegExp(AlreadyRejectedException::class, '|.*Cannot resolve a rejected promise|');
        $promise->resolve($in);
    }

    //====================== then(); ===========================================

    public function test_then()
    {
        $masterPromise = new Promise;
        $masterPromise->reject('foo');
        $slavePromise = $masterPromise->then();
        $this->assertEquals(PromiseInterface::REJECTED, $slavePromise->getState());
        $this->assertEquals('foo', $slavePromise->wait(false)->getMessage());
        $this->assertContainsOnlyInstancesOf(PromiseException::class, [$slavePromise->wait(false)]);
    }

    public function test_then_with_callbacks()
    {
        $onFulfilled = function($value) {
            return 'After $onFulfilled - ' . $value;
        };
        $onRejected = function($value) {
            return 'After $onRejected - ' . $value->getMessage();
        };
        $masterPromise = new Promise;
        $masterPromise->reject('foo');
        $slavePromise = $masterPromise->then($onFulfilled, $onRejected);
        $this->assertEquals(PromiseInterface::FULFILLED, $slavePromise->getState());
        $this->assertEquals('After $onRejected - foo', $slavePromise->wait(false));
    }

}
