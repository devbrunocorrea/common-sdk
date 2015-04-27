<?php

/*
 * This file is part of gpupo/common-sdk
 *
 * (c) Gilmar Pupo <g@g1mr.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Gpupo\CommonSdk\Entity;

use Gpupo\CommonSdk\Exception\InvalidArgumentException;

// Hack for old php versions (<5.5) to use boolval()
if (!function_exists('boolval')) {
    function boolval($val)
    {
        return (bool) $val;
    }
}

class EntityTools
{
    public static function getInitValue($data, $key, $default = '')
    {
        if (is_array($data) && array_key_exists($key, $data)) {
            $fill = $data[$key];
            if (is_array($default) && !is_array($fill)) {
                $fill = [$key => $fill];
            }
        }

        return (isset($fill)) ? $fill : $default;
    }

    public static function normalizeType($data, $type)
    {
        switch ($type) {
            case 'integer':
                return intval($data);
            case 'boolean':
                return boolval($data);
            case 'number':
            case 'float':
                return floatval($data);
            default:
                return $data;
        }
    }

    protected static function returnInvalid($key, $current, $value)
    {
        throw new InvalidArgumentException($key
            .' should have value of type '.$value
            .' valid.['.$current.'] received.');
    }

    protected static function isEmptyValue($value, $required = false)
    {
        return ($required) ? false : empty($value);
    }

    public static function validate($key, $current, $value, $required = false)
    {
        if (self::isEmptyValue($current)) {
            return true;
        }

        foreach (['Integer', 'Number', 'String'] as $type) {
            $testMethod = 'test'.$type;
            self::$testMethod($key, $current, $value);
        }

        return true;
    }

    protected static function testInteger($key, $current, $value)
    {
        if ($value === 'integer' && intval($current) !== $current) {
            self::returnInvalid($key, $current, $value);
        }
    }

    protected static function testNumber($key, $current, $value)
    {
        if ($value === 'number' && !is_numeric($current)) {
            self::returnInvalid($key, $current, $value);
        }
    }

    protected static function testString($key, $current, $value)
    {
        if ($value === 'string' && strlen($current) < 1) {
            self::returnInvalid($key, $current, $value);
        }
    }
}
