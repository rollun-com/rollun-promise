<?php

namespace rollun\test\promise\Promise;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-09-24 at 00:05:36.
 */
class DataProvider extends \PHPUnit_Framework_TestCase
{

    public function provider_Types()
    {
        return [
            array(false),
            array(-12345),
            array('foo'),
            array([1, 'foo', [], false]),
            array(new \stdClass()),
            array(new \LogicException('bar')),
        ];
    }

}
