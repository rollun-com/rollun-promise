<?php

namespace rollun\test\promise\Promise;

use rollun\promise\Promise\Promise;
use rollun\promise\Promise\PromiseInterface;
use rollun\dic\InsideConstruct;
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

    //====================== serialize(); ======================================

    public function test_serialize()
    {
        $promise = new Promise;
        $promise = unserialize(serialize($promise));
        $this->assertEquals(PromiseInterface::PENDING, $promise->getState());
    }

    //================== serialize() resolve(); ================================
    /**
     * @dataProvider provider_Types()
     */
    public function test_resolve_anyTypes($in)
    {
        $promise = new Promise;
        $promise->resolve($in);
        $promise = unserialize(serialize($promise));
        $this->assertEquals($in, $promise->wait(false));
    }

    //====================== then(); ===========================================

    public function test_then()
    {
        $masterPromise = new Promise;
        $masterPromise = unserialize(serialize($masterPromise));
        $slavePromise = $masterPromise->then();
        $slavePromise = unserialize(serialize($slavePromise));
        $this->assertEquals(PromiseInterface::PENDING, $slavePromise->getState());
        $this->assertEquals(PromiseInterface::DEPENDENT, $slavePromise->getState(false));
    }

    //========================== getInstance(); ================================

    public function test_getInstance()
    {
        $promise = Promise::getInstance();
        $this->assertContainsOnlyInstancesOf(Promise::class, [$promise]);
        $promiseCopy = Promise::getInstance($promise->getId());
        $this->assertEquals($promise->getId(), $promiseCopy->getId());
    }

}
