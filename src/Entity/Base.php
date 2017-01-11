<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace rollun\promise\Entity;

/**
 * Base
 *
 * Parent (base) class for all Clients and Entities
 *
 * @category   async
 * @package    zaboy
 */
class Base
{

    const EXCEPTION_CLASS = '\Exception';
    const ID_SEPARATOR = '_';

    // id has specific structoure - prefix__1234567890_12346__jljkHU6h4sgvYu...n67_
    protected $idPattern;

    /**
     * Creates ID for the entity.
     *
     * An algorithm of creation ID is common for the all entities except for prefix string.
     *
     * For example for Promise it will be 'promise_', for Task - 'task_' etc.
     *
     * @return string
     */
    protected function makeId()
    {
        $time = sprintf('%0.6f', (microtime(1) - date('Z')));
        $idWithDot = uniqid(
                $this->getPrefix() . self::ID_SEPARATOR . self::ID_SEPARATOR
                . $time . self::ID_SEPARATOR . self::ID_SEPARATOR
                , true
        );
        $id = str_replace('.', self::ID_SEPARATOR, $idWithDot);

        return $id;
    }

    /**
     * Checks string for the match ID.
     *
     * @param string $param
     * @return bool
     */
    public function isId($param)
    {
        $array = [];
        $regExp = $this->getIdPattern();
        if (is_string($param) && preg_match_all($regExp, $param, $array)) {
            return $array[0][0] == $param;
        } else {
            return false;
        }
    }

    /**
     * Returns the Prefix for Id
     *
     * @return string
     */
    public function getPrefix()
    {
        return strtolower(explode('\\', get_class($this))[2]);
    }

    /**
     * Returns Id Pattern
     *
     *  id has specific structoure - prefix__1234567890_12346__jljkHU6h4sgvYu...n67_
     *
     * @return string
     */
    public function getIdPattern()
    {
        return '/(' . $this->getPrefix() . '__[0-9]{10}_[0-9]{6}__[a-zA-Z0-9_]{23})/';
    }

    /**
     *
     *
     * @param $stringOrException
     * @param array $idArray
     * @return array
     */
    public function extractId($stringOrException, $idArray = [])
    {
        if (is_null($stringOrException)) {
            return $idArray;
        }
        if ($stringOrException instanceof \Exception) {
            $array = $this->extractId($stringOrException->getPrevious(), $idArray);
            $idArray = $this->extractId($stringOrException->getMessage(), $array);
            return $idArray;
        }
        $array = [];
        if (preg_match_all($this->getIdPattern(), $stringOrException, $array)) {
            return array_merge(array_reverse($array[0]), $idArray);
        } else {
            return [];
        }
    }

}
