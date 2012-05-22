<?php
/**
 *
 * This file is part of the AstirMapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
namespace itbz\AstirMapper\PDO;
use itbz\AstirMapper\Exception\PdoException;


/**
 *
 * Tool for querying database using variable operators
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
class Operator extends Attribute
{

    /**
     *
     * Map of valid operators and their inversions
     *
     * @var array $_operators
     *
     */
    static private $_operators = array(
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
     *
     * Attribute operator
     *
     * @var string $_operator
     *
     */
    private $_operator;

    
    /**
     *
     * Construct and set operator
     *
     * Set operator to one of <=>, =, >=, >, IS NOT, IS, <=, <, LIKE, !=, <>,
     * NOT LIKE, NOT REGEXP, REGEXP, RLIKE or SOUNDS LIKE
     *
     * @param string $name The name of the attribute
     *
     * @param mixed $value
     *
     * @param string $operator
     *
     * @throws PdoException if unable to convert value to string
     *
     * @throws PdoException if operator is not a valid
     * 
     */
    public function __construct($name, $value, $operator)
    {
        parent::__construct($name, $value);

        if (!isset(self::$_operators[$operator])) {
            $msg = "'$operator' is not a valid SQL operator";
            throw new PdoException($msg);
        }

        $this->_operator = $operator;
    }


    /**
     *
     * Invert operator
     *
     */
    public function invert()
    {
        $this->_operator = self::$_operators[$this->_operator];
    }


    /**
     *
     * Get current operator
     *
     * @return string
     *
     */
    public function getOperator()
    {
        return $this->_operator;
    }

}
