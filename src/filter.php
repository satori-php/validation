<?php

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2017 Yuriy Davletshin
 * @license   MIT
 */

declare(strict_types=1);

namespace Common\Validation;

/**
 * Filters a string.
 *
 * @param string|null                    $value  The value for filtering.
 * @param array<string, string|int|bool> $params The additional options.
 *
 * @return string|null
 */
function filterString($value, array $params = null)
{
    $required = false;
    $result = $value === null ? '' : $value;
    $result = is_string($result) ? $result : null;
    if ($params) {
        if (isset($params['required']) && is_bool($params['required'])) {
            $result = $params['required'] && $result === '' ? null : $result;
            $required = $params['required'];
        }
        if ($result && isset($params['max']) && is_int($params['max'])) {
            $result = mb_strlen($result) > $params['max'] ? null : $result;
        }
        if ($result && isset($params['min']) && is_int($params['min'])) {
            $result = mb_strlen($result) < $params['min'] ? null : $result;
        }
        if ($result && isset($params['regex']) && is_string($params['regex'])) {
            $result = !preg_match($params['regex'], $result) ? null : $result;
        }
        if (!$result && isset($params['default']) && is_string($params['default'])) {
            $result = $required ? null : $params['default'];
        }
    }

    return $result;
}

/**
 * Filters an integer.
 *
 * @param string|int|null           $value  The value for filtering.
 * @param array<string, int|string> $params The additional options.
 *
 * @return int|null
 */
function filterInt($value, array $params = null)
{
    if ($params) {
        if (isset($params['min']) && is_int($params['min'])) {
            $options['options']['min_range'] = $params['min'];
        }
        if (isset($params['max']) && is_int($params['max'])) {
            $options['options']['max_range'] = $params['max'];
        }
        if (isset($params['allow'])) {
            switch ($params['allow']) {
                case 'oct':
                    $options['flags'] = FILTER_FLAG_ALLOW_OCTAL;
                    break;

                case 'hex':
                    $options['flags'] = FILTER_FLAG_ALLOW_HEX;
                    break;

                case 'hex|oct':
                case 'oct|hex':
                    $options['flags'] = FILTER_FLAG_ALLOW_HEX | FILTER_FLAG_ALLOW_OCTAL;
                    break;
            }
        }
        if (isset($params['default']) && is_int($params['default'])) {
            $options['options']['default'] = $params['default'];
        }
    }
    $result = filter_var($value, FILTER_VALIDATE_INT, $options ?? []);

    return $result === false ? null : $result;
}

/**
 * Filters a float.
 *
 * @param string|float|null           $value  The value for filtering.
 * @param array<string, float|string> $params The additional options.
 *
 * @return float|null
 */
function filterFloat($value, array $params = null)
{
    if ($params) {
        if (isset($params['decimal']) && in_array($params['decimal'], ['.' , ','])) {
            $options['options']['decimal'] = $params['decimal'];
        }
        if (isset($params['default']) && is_float($params['default'])) {
            $options['options']['default'] = $params['default'];
        }
    }
    $result = filter_var($value, FILTER_VALIDATE_FLOAT, $options ?? []);

    return $result === false ? null : $result;
}

/**
 * Filters a boolean.
 *
 * @param string|bool|null    $value  The value for filtering.
 * @param array<string, bool> $params The additional options.
 *
 * @return bool|null
 */
function filterBool($value, array $params = null)
{
    if ($params) {
        if (isset($params['strict_null']) && $params['strict_null'] === true && $value === null) {
            return null;
        }
        if (isset($params['default']) && is_bool($params['default'])) {
            $options['options']['default'] = $params['default'];
        }
    }
    $options['flags'] = FILTER_NULL_ON_FAILURE;

    return filter_var($value, FILTER_VALIDATE_BOOLEAN, $options);
}

/**
 * Filters a string that contains date and time.
 *
 * @param string|null           $value  The value for filtering.
 * @param array<string, string> $params The additional options.
 *
 * @return \DateTime|null
 */
function filterDateTime($value, array $params = null)
{
    $result = date_create((string) $value);
    if ($params) {
        if ($result && isset($params['min']) && is_string($params['min'])) {
            $result = date_diff(date_create($params['min']), $result)->invert ? null : $result;
        }
        if ($result && isset($params['max']) && is_string($params['max'])) {
            $result = date_diff($result, date_create($params['max']))->invert ? null : $result;
        }
        if (!$result && isset($params['default']) && is_string($params['default'])) {
            $result = date_create($params['default']);
        }
    }

    return $result;
}
