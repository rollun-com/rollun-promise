<?php

namespace rollun\promise\Promise;

use Zend\Db\Adapter\AdapterInterface;
use rollun\dic\InsideConstruct;
use rollun\promise\Entity\Store as EntityStore;
use rollun\utils\Php\Serializer as PhpSerializer;
use rollun\utils\Json\Serializer as JsonSerializer;

/**
 * Store
 *
 * @category   async
 * @package    zaboy
 */
class Store extends EntityStore
{

    const TABLE_NAME = 'promise';
    //PROMISE_ADAPTER_DATA_STORE
    //
    //'id' - unique id of promise: promise_id_123456789qwerty
    //const ID = 'id';
    const STATE = 'state';
    const RESULT = 'result';
    const PARENT_ID = 'parent_id';
    const ON_FULFILLED = 'on_fulfilled';
    const ON_REJECTED = 'on_rejected';

    public function __construct(AdapterInterface $promiseDbAdapter = null)
    {
        //set as $cotainer->get('promiseDbAdapter');
        $services = InsideConstruct::setConstructParams();
        $adapter = $services['promiseDbAdapter'];
        parent::__construct($adapter);
    }

    protected function prepareData(array $data, $fild = null)
    {
        if ($fild === self::ON_FULFILLED || $fild === self::ON_REJECTED) {
            return [$fild => PhpSerializer::phpSerialize($data[$fild])];
        }
        if ($fild === self::RESULT) {
            return [$fild => JsonSerializer::jsonSerialize($data[$fild])];
        }
        return parent::prepareData($data, $fild);
    }

    protected function restoreData(array $data, $columnName = null)
    {
        if ($columnName === self::ON_FULFILLED || $columnName === self::ON_REJECTED) {
            return [$columnName => PhpSerializer::phpUnserialize($data[$columnName])];
        }
        if ($columnName === self::RESULT) {
            return [$columnName => JsonSerializer::jsonUnserialize($data[$columnName])];
        }
        return parent::restoreData($data, $columnName);
    }

}
