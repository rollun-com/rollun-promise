<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Promise;

use rollun\promise\Entity\Client;
use rollun\promise\Promise\Store as PromiseStore;
use rollun\promise\Promise\PromiseInterface;
use rollun\promise\Promise\Exception as PromiseException;

/**
 * Client
 *
 * @category   async
 * @package    zaboy
 */
class Promise extends Client implements PromiseInterface
{

    /**
     *
     * @var string
     */
    public static $class = null;

    /**
     * Client constructor.
     *
     * @see https://github.com/domenic/promises-unwrapping/blob/master/docs/states-and-fates.md
     * @see https://github.com/promises-aplus/promises-spec
     *
     * @param string|array $data
     * @throws \LogicException
     */
    public function __construct($data = [])
    {
        parent::__construct($data, new PromiseStore());
    }

    public function wait($unwrap = true)
    {
        try {
            return $this->getEntity()->wait($unwrap);
        } catch (\Exception $exc) {
            throw $exc;
        }
    }

    public function resolve($value)
    {
        $id = $this->runTransaction('resolve', [$value]);
        return $id;
    }

    public function reject($value)
    {
        $id = $this->runTransaction('reject', [$value]);
        return $id;
    }

    /**
     * Returns the class name of Entity
     *
     * @return string
     */
    protected function getClass($data = null)
    {
        $namespace = '\\' . __NAMESPACE__ . '\\Promise\\';
        switch (true) {
            case empty($data) || !array_key_exists(PromiseStore::STATE, $data) ||
            $data[PromiseStore::STATE] === PromiseInterface::PENDING &&
            empty($data[PromiseStore::PARENT_ID]):
                return $namespace . 'Pending';
            case $data[PromiseStore::STATE] === PromiseInterface::FULFILLED:
                return $namespace . 'Fulfilled';
            case $data[PromiseStore::STATE] === PromiseInterface::REJECTED:
                return $namespace . 'Rejected';
            default:
                return $namespace . 'Dependent';
        }
    }

    public function getState($dependentAsPending = true)
    {
        return $this->getEntity()->getState($dependentAsPending);
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $id = $this->runTransaction('then', [ $onFulfilled, $onRejected]);
        return static::getInstance($id);
    }

    protected function runTransaction($methodName, $params = [])
    {
        try {
            $this->store->beginTransaction();
            $entity = $this->getEntity();
            $stateBefore = $entity->getState();
            $methodResult = call_user_func_array([$entity, $methodName], $params);
            $resultType = gettype($methodResult);
            switch ($resultType) {
                case 'object':
                    $stateAfter = $methodResult->getState();
                    $data = $methodResult->getData();
                    $id = $methodResult->getId();
                    unset($data[PromiseStore::ID]);
                    //or update
                    $where = [PromiseStore::ID => $id];
                    $number = $this->store->update($data, $where);
                    //or create a new one if absent
                    if (!$number) {
                        $this->store->insert($methodResult->getData());
                    }
                    $this->store->commit();
                    break;
                case 'NULL':
                    $stateAfter = $stateBefore;
                    $this->store->commit();
                    return $this->getId();
                default:
                    throw new \LogicException('Wrong type of result ' . $resultType);
            }
        } catch (PromiseException $exc) {
            $this->store->rollback();
            $reason = $exc->getMessage();
            $prev = $exc->getPrevious();
            throw new $exc($reason, 0, $prev);
        } catch (\Exception $exc) {
            $this->store->rollback();
            $reason = 'Error while method  ' . $methodName . ' is running.' . PHP_EOL .
                    'Reason: ' . $exc->getMessage() . PHP_EOL .
                    ' Id: ' . $this->id;
            throw new \RuntimeException($reason, 0, $exc);
        }
        if ($stateBefore === PromiseInterface::PENDING && $stateAfter !== PromiseInterface::PENDING) {
            $this->resolveDependent();
        }
        return $id;
    }

    /**
     *
     * @todo catch (\Exception $exc) .. drop circle
     */
    protected function resolveDependent()
    {
        //are dependent promises exist?
        $rowset = $this->store->select([PromiseStore::PARENT_ID => $this->getId()]);
        $rowsetArray = $rowset->toArray();
        foreach ($rowsetArray as $dependentPromiseData) {
            $dependentPromiseId = $dependentPromiseData[PromiseStore::ID];
            $dependentPromise = static::getInstance($dependentPromiseId);
            try {
                $dependentPromise->resolve($this);
            } catch (\Exception $exc) {
                throw new \RuntimeException(
                'Cannot resolve dependent Promise. ID: ' . $dependentPromiseId
                , 0, $exc);
            }
        }
    }

    public function __wakeup()
    {
        $this->store = new PromiseStore();
        static::$class = get_class($this);
    }

}
