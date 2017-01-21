<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Promise\Promise;

use rollun\logger\Exception\LoggedException;
use rollun\promise\Promise\Promise;
use rollun\promise\Promise\Store as PromiseStore;
use rollun\promise\Promise\Promise\Fulfilled as FulfilledPromise;
use rollun\promise\Promise\Promise\Rejected as RejectedPromise;
use rollun\promise\Promise\Promise\Dependent as DependentPromise;
use rollun\promise\Entity\Entity;
use rollun\promise\Promise\PromiseInterface;
use rollun\promise\Promise\Exception\TimeIsOutException;
use rollun\promise\Promise\Exception as PromiseException;

/**
 * Pending Promise
 *
 * @category   async
 * @package    zaboy
 */
class Pending extends Entity implements PromiseInterface
{

    /**
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
//$id = isset($data[PromiseStore::ID]) ? $data[PromiseStore::ID] : null;
        parent::__construct($data);
        $this[PromiseStore::STATE] = PromiseInterface::PENDING;
        $this[PromiseStore::RESULT] = null;
        $this[PromiseStore::ON_FULFILLED] = null;
        $this[PromiseStore::ON_REJECTED] = null;
        $this[PromiseStore::PARENT_ID] = null;
    }

    public function resolve($value)
    {
//If promise and x refer to the same object, reject promise with a TypeError as the reason.
        if (is_object($value) && $value == $this) {
            $exc = new \UnexpectedValueException('TypeError. ID = ' . $this->getId());
            $this[PromiseStore::RESULT] = $exc;
            return new RejectedPromise($this->getData());
        }

//If then is not a function, fulfill promise with x.
        if (!is_object($value) || !$value instanceof PromiseInterface) {
            $this[PromiseStore::RESULT] = $value;
            return new FulfilledPromise($this->getData());
        }

//If x is pending, promise must remain pending until x is fulfilled or rejected.
//If/when x is fulfilled, fulfill promise with the same value.
//If/when x is rejected, reject promise with the same reason
        $state = $value->getState();
        if ($state === PromiseInterface::PENDING) {
            $lockedPromise = new Promise($value->getId());
            $state = $lockedPromise->getState();
        }
        switch ($state) {
            case PromiseInterface::PENDING:
                $this[PromiseStore::PARENT_ID] = $value->getId();
                return new DependentPromise($this->getData());
            case PromiseInterface::FULFILLED:
                $this[PromiseStore::RESULT] = $value->wait(false);
                return new FulfilledPromise($this->getData());
            case PromiseInterface::REJECTED:
                $this[PromiseStore::RESULT] = $value->wait(false);
                return new RejectedPromise($this->getData());
            default:
                throw new LoggedException('Wrong state: ' . $state . '. ID = ' . $this->getId());
        }
    }

    public function reject($reason)
    {
        if ((is_object($reason) && $reason instanceof PromiseInterface)) {
            $state = $reason->getState();
            switch ($state) {
                case PromiseInterface::PENDING:
                    $reason = 'Reason is pending promise. ID = ' . $reason->getId();
                    break;
                case PromiseInterface::FULFILLED:
                case PromiseInterface::REJECTED:
                    $reason = $reason->wait(false);
                    break;
                default:
                    throw new PromiseException('Wrong state: ' . $state . '. ID = ' . $this->getId());
            }
        }
        if (!(is_object($reason) && $reason instanceof \Exception)) {
            set_error_handler(function ($number, $string) {
                throw new \UnexpectedValueException(
                'Reason cannot be converted to string.  ID: ' . $this->getId(), null, null
                );
            });
            try {
                //$reason can be converted to string
                $reasonStr = strval($reason);
                $reason = new PromiseException($reasonStr);
            } catch (\Exception $exc) {
                //$reason can not be converted to string
                $reason = $exc;
            }
            restore_error_handler();
        }
        $this[PromiseStore::RESULT] = $reason;
        $rejectedPromise = new RejectedPromise($this->getData());
        return $rejectedPromise;
    }

    public function getState($dependentAsPending = true)
    {
        $data = $this->getData();
        $state = $dependentAsPending || $data[PromiseStore::STATE] !== PromiseInterface::PENDING ?
                $data[PromiseStore::STATE] :
                (
                $data[PromiseStore::PARENT_ID] ?
                        PromiseInterface::DEPENDENT :
                        PromiseInterface::PENDING
                );
        return $state;
    }

    /**
     *
     * @param bool|int $unwrap false or time in seconds for promise resolving
     * @return mix
     */
    public function wait($unwrap = true)
    {
        $id = $this->getId();
        if (!$unwrap) {
            return new TimeIsOutException('ID: ' . $id);
        }
        $waitingCheckInterval = 1; //1 second;
        $defaultMaxInterval = 2; //2 second;
        $waitingTime = true === $unwrap ? $defaultMaxInterval : (int) $unwrap;
        $endTime = time() + $waitingTime;
        do {
            sleep($waitingCheckInterval);
            $promise = Promise::getInstance($id);
            /* @var $promise \rollun\promise\Promise\Promise */
            $state = $promise->getState();
            switch ($state) {
                case PromiseInterface::FULFILLED:
                    return $promise->wait(false);
                case PromiseInterface::REJECTED:
                    throw $promise->wait(false);
                case PromiseInterface::PENDING:
            }
        } while (time() < $endTime);
        $reason = new TimeIsOutException('ID: ' . $id);
        $promise->reject($reason);
        throw $reason;
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        return new DependentPromise([
            PromiseStore::PARENT_ID => $this->getId(),
            PromiseStore::ON_FULFILLED => $onFulfilled,
            PromiseStore::ON_REJECTED => $onRejected
        ]);
    }

}
