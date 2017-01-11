<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Promise\Promise;

use rollun\promise\Promise\Store as PromiseStore;
use rollun\promise\Promise\Exception\AlreadyRejectedException;
use rollun\promise\Promise\Promise\Pending as PendingPromise;
use rollun\promise\Promise\PromiseInterface;
use rollun\promise\Promise\Promise\Dependent as DependentPromise;

/**
 * RejectedPromise
 *
 */
class Rejected extends PendingPromise
{

    /**
     *
     * @param array $data
     */
    public function __construct($data = [])
    {
        parent::__construct($data);
        if (!array_key_exists(PromiseStore::RESULT, $data)) {
            throw new \RuntimeException('REJECT reason  must be retriveed. ID = ' . $this->getId());
        }
        if (!$data[PromiseStore::RESULT] instanceof \Exception) {
            throw new \RuntimeException('RESULT type must be an exception. ID = ' . $this->getId());
        }
        $this[PromiseStore::RESULT] = $data[PromiseStore::RESULT];
        $this[PromiseStore::STATE] = PromiseInterface::REJECTED;
        $this[PromiseStore::ON_FULFILLED] = null;
        $this[PromiseStore::ON_REJECTED] = null;
        $this[PromiseStore::PARENT_ID] = null;
    }

    public function resolve($value)
    {
        throw new AlreadyRejectedException('Cannot resolve a rejected promise.  ID: ' . $this->getId());
    }

    public function reject($reason)
    {
        $pendingPromise = new PendingPromise([PromiseStore::ID => $this->getId()]);
        $rejectedPromise = $pendingPromise->reject($reason);
        $reason = $rejectedPromise->wait(false);

        $message = $reason->getMessage() === $this[PromiseStore::RESULT]->getMessage();
        $prev = $reason->getPrevious() == $this[PromiseStore::RESULT]->getPrevious();
        $type = get_class($reason) === get_class($this[PromiseStore::RESULT]);
        if ($message && $prev && $type) {
            return null;
        } else {
            throw new AlreadyRejectedException(
            'Cannot reject a rejected promise.' . ' ID = ' . $this->getId()
            );
        }
    }

    public function wait($unwrap = true)
    {
        if ($unwrap) {
            throw $this[PromiseStore::RESULT];
        }
        return $this[PromiseStore::RESULT];
    }

    public function then(callable $onFulfilled = null, callable $onRejected = null)
    {
        $dependentPromise = new DependentPromise([
            PromiseStore::PARENT_ID => $this->getId(),
            PromiseStore::ON_FULFILLED => null,
            PromiseStore::ON_REJECTED => $onRejected
        ]);
        return $dependentPromise->resolve($this);
    }

}
