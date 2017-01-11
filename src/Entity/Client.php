<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Entity;

use rollun\promise\Entity\Store as EntityStore;
use rollun\promise\Entity\Base;
use rollun\promise\Entity\Entity;

/**
 * Client
 *
 * @category   async
 * @package    zaboy
 */
class Client extends Base
{

    /**
     *
     * @var string
     */
    public static $class = null;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var string
     */
    protected $id;

    /**
     * Client constructor.
     *
     * @param string|array $data if string - it must to be id
     * @param EntityStore $entityStore
     * @throws \LogicException
     */
    public function __construct($data = [], EntityStore $entityStore = null)
    {
        static::$class = static::class;
        $this->store = $entityStore ? $entityStore : new EntityStore();
        if ($this->isId($data)) {
            $this->id = $data;
            return;
        }
        if (is_array($data)) {
            $entity = $this->makeEntity($data);
            $this->id = $entity->getId();
            return;
        }
        throw new \LogicException('Wrong format of specified data');
    }

    public static function getInstance($data = [])
    {
        static::$class = static::$class ? static::$class : static::class;
        return new static::$class($data);
    }

    public function remove()
    {
        return $this->removeEntity();
    }

    /**
     * Makes an Entity with specified data or a new Entity
     *
     * @param array|null $data
     * @return Entity
     */
    protected function makeEntity($data = null)
    {
        $class = $this->getClass($data);
        $entity = new $class($data);
        try {
            $data = $entity->getData();
            $rowsCount = $this->store->insert($data); //This data may be serialized for DB in Store
            if (!$rowsCount) {
                throw new \RuntimeException('Any records was inserted.');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Can\'t insert Entity. Entity id: ' . $entity->getId(), 0, $e);
        }
        return $entity;
    }

    /**
     *
     * @param string $id
     * @return \rollun\promise\Entity\Entity
     * @throws \RuntimeException
     */
    protected function getEntity($id = null)
    {
        $id = is_null($id) ? $this->getId() : $id;
        if (!$this->isId($id)) {
            throw new \RuntimeException(
            "There is not correct id: $id"
            );
        }
        $data = $this->store->read($id);
        if (empty($data)) {
            throw new \RuntimeException(
            "There is no data in the store for id: $id"
            );
        }
        $entityClass = $this->getClass($data);
        $entity = new $entityClass($data);
        return $entity;
    }

    protected function removeEntity($id = null)
    {
        $id = is_null($id) ? $this->getId() : $id;
        if (!$this->isId($id)) {
            throw new \RuntimeException(
            "There is not correct id: $id"
            );
        }
        $count = $this->store->delete([EntityStore::ID => $id]);
        return $count;
    }

    /**
     * Runs method od Entity with name $methodName and writes down result into the Store.
     *
     * If you don't wont to save result in the Store the method $methodName must return null.
     *
     * The method $methodName can receive not greater then two mixed parameters: $param1 and $param2.
     *
     * @param $methodName
     * @param mixed|null $param1
     * @param mixed|null $params2
     * @return string
     * @throws $this::EXCEPTION_CLASS
     */
    protected function runTransaction($methodName, $params = [])
    {
        try {
            $this->store->beginTransaction();
            $entity = $this->getEntity();
            $methodResult = call_user_func_array([$entity, $methodName], $params);
            $resultType = gettype($methodResult);
            switch ($resultType) {
                case 'object':
                    $methodResult = $methodResult->getData();
                case 'array':
                    $id = $methodResult[EntityStore::ID];
                    unset($methodResult[EntityStore::ID]);
                    //or update
                    $where = [EntityStore::ID => $id];
                    $number = $this->store->update($methodResult, $where);
                    //or create a new one if absent
                    if (!$number) {
                        $methodResult[EntityStore::ID] = $id;
                        $this->store->insert($methodResult);
                    }
                    $this->store->commit();
                case 'NULL':
                    $id = $this->getId();
                    $this->store->commit();
                default:
                    throw new \LogicException('Wrong type of result ' . $resultType);
            }
        } catch (\Exception $exc) {
            $this->store->rollback();
            $reason = 'Error while method  ' . $methodName . ' is running.' . PHP_EOL .
                    'Reason: ' . $exc->getMessage() . PHP_EOL .
                    ' Id: ' . $this->getId();
            throw new \RuntimeException($reason, 0, $exc);
        }
        return $id;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the class name of Entity
     *
     * @return string
     */
    protected function getClass($data = null)
    {
        $class = __NAMESPACE__ . '\\' . ucfirst($this->getPrefix());
        return $class;
    }

    public function __sleep()
    {
        return array('id');
    }

    public function __wakeup()
    {
        $this->store = new EntityStore();
        static::$class = static::class;
    }

}
