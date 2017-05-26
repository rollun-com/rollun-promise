<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Promise;

/**
 * Full Interface for Promise
 *
 * @category   async
 * @package    zaboy
 */
interface PromiseInterface
{

    const FULFILLED = 'fulfilled';
    const REJECTED = 'rejected';
    const PENDING = 'pending';
    const DEPENDENT = 'dependent';

    /**
     * Appends fulfillment and rejection handlers to the promise, and returns
     * a new promise resolving to the return value of the called handler.
     *
     * @param callable $onFulfilled Invoked when the promise fulfills.
     * @param callable $onRejected  Invoked when the promise is rejected.
     *
     * @return PromiseInterface
     */
    public function then(callable $onFulfilled = null, callable $onRejected = null);

    /**
     * Get the state of the promise ("pending", "rejected", "fulfilled" or "dependent").
     *
     * The three states can be checked against the constants defined on
     * PromiseInterface: PENDING, FULFILLED, and REJECTED.
     * If $dependentAsPending is false and promise was resolved by pending promise
     * method return DEPENDENT. Also we get dependent promise as result of then().
     *
     * @param bool $dependentAsPending true as default
     * @return string Status
     */
    public function getState($dependentAsPending = true);

    /**
     * Resolve the promise with the given value.
     *
     * @param mixed $value
     * @throws LoggedException if the promise is already resolved.
     */
    public function resolve($value);

    /**
     * Reject the promise with the given reason.
     *
     * @param mixed $reason
     * @throws LoggedException if the promise is already resolved.
     */
    public function reject($reason);

    /**
     * Waits until the promise completes if possible.
     *
     * Pass $unwrap as true to unwrap the result of the promise, either
     * returning the resolved value or throwing the rejected exception.
     *
     * If the promise cannot be waited on, then the promise will be rejected.
     *
     * @param bool $unwrap
     *
     * @return mixed
     * @throws \LogicException if the promise has no wait function or if the
     *                         promise does not settle after waiting.
     */
    public function wait($unwrap = true);

    /**
     * Return id (primary key value in db)
     *
     * @return string promise__1469864422_189511__579c84162e43e4_34952052
     */
    public function getId();
}
