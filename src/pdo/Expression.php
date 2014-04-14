<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace datamapper\pdo;

use datamapper\exception\PdoException;

/**
 * Internal class for modeling SQL expressions
 *
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
 */
class Expression
{
    /**
     * @var array Map of valid operators and inversions
     */
    static private $operators = array(
        '<=>' => '!=',
        '=' => '!=',
        '>=' => '<',
        '>' => '<=',
        'IS NOT' => 'IS',
        'IS' => 'IS NOT',
        '<=' => '>',
        '<' => '>=',
        'LIKE' => 'NOT LIKE',
        '!=' => '=',
        '<>' => '=',
        'NOT LIKE' => 'LIKE',
        'NOT REGEXP' => 'REGEXP',
        'REGEXP' => 'NOT REGEXP',
        'RLIKE' => 'NOT REGEXP',
        'SOUNDS LIKE' => 'SOUNDS LIKE'
    );

    /**
     * @var string The name of this expression
     */
    private $name;

    /**
     * @var string The value of this expression
     */
    private $value;

    /**
     * @var string Expression operator
     */
    private $operator;

    /**
     * @var bool Flag if expression value should be escaped
     */
    private $escapeValue = true;

    /**
     * @var bool Flag if expression name should be escaped
     */
    private $escapeName = true;

    /**
     * Construct and cast value to string
     *
     * The type conversion used differs from standard PHP type juggling rules.
     * Boolean TRUE is converted to '1' and FALSE to '0'. Arrays are imploded
     * using ',' as field delimiter. NULL is converted to the lower case string
     * 'null'. Objects are converted to strings if they implement the __tostring
     * magic method. Integers and floats are converted using the standard type
     * juggling rules.
     *
     * Set operator to one of <=>, =, >=, >, IS NOT, IS, <=, <, LIKE, !=, <>,
     * NOT LIKE, NOT REGEXP, REGEXP, RLIKE or SOUNDS LIKE
     *
     * @param string $name The name of the expression
     * @param mixed $value
     * @param string $operator Defaults to '='
     * @throws PdoException if unable to value object to string
     * @throws PdoException if operator is not a valid
     */
    public function __construct($name, $value, $operator = '=')
    {
        assert('is_string($name)');
        assert('is_string($operator)');
        $this->name = $name;

        switch (gettype($value)) {
            case 'boolean':
                $this->value = $value ? '1' : '0';
                break;
            case 'array':
                $this->value = implode(',', $value);
                break;
            case 'NULL':
                $this->value = 'null';
                $this->setEscapeValue(false);
                break;
            case 'object':
                if (method_exists($value, '__toString')) {
                    $this->value = (string)$value;
                } else {
                    $classname = get_class($value);
                    $msg = "Unable to convert class '$classname' to string";
                    throw new PdoException($msg);
                }
                break;
            default:
                $this->value = (string)$value;
        }

        if (!isset(self::$operators[$operator])) {
            $msg = "'$operator' is not a valid SQL operator";
            throw new PdoException($msg);
        }

        $this->operator = $operator;
    }

    /**
     * Set if expression value should be escaped
     *
     * @param bool $flag
     * @return void
     */
    public function setEscapeValue($flag)
    {
        assert('is_bool($flag)');
        $this->escapeValue = $flag;
    }

    /**
     * Set if expression name should be escaped
     *
     * @param bool $flag
     * @return void
     */
    public function setEscapeName($flag)
    {
        assert('is_bool($flag)');
        $this->escapeName = $flag;
    }

    /**
     * Get name of expression
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get value of expression
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get operator for comparisons
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Invert operator
     *
     * @return void
     */
    public function invertOperator()
    {
        $this->operator = self::$operators[$this->operator];
    }

    /**
     * Check if expression value should be escaped
     *
     * @return bool
     */
    public function escapeValue()
    {
        return $this->escapeValue;
    }

    /**
     * Check if expression name should be escaped
     *
     * @return bool
     */
    public function escapeName()
    {
        return $this->escapeName;
    }
}
