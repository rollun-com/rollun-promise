<?php

namespace rollun\test\promise\Entity;

use rollun\promise\Entity\Client as EntityClient;
use zaboy\res\Di\InsideConstruct;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-09-23 at 18:06:20.
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Client
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

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * @covers rollun\promise\Entity\Client::remove
     * @todo   Implement testRemoveEntity().
     */
    public function test_MakeEntity_RemoveEntity()
    {
        $this->object = new EntityClient();
        $this->assertEquals(1, $this->object->remove());
    }

    public function testGetId()
    {
        $this->object = new EntityClient();
        $this->assertTrue($this->object->isId($this->object->getId()));
        $this->object->remove();
    }

}
