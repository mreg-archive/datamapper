<?php
/**
 * This file is part of the DataMapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 *
 * @package DataMapper
 *
 * @subpackage PDO
 */
namespace itbz\DataMapper\PDO;


/**
 * Internal class for organizing expressions into substatements
 *
 * @package DataMapper
 *
 * @subpackage PDO
 */
class ExpressionSet
{

    /**
     * Array of Expressions
     *
     * @var array
     */
    private $_expressions = array();


    /**
     * Add any number of expressions at construct
     */
    public function __construct()
    {
        foreach (func_get_args() as $expr) {
            $this->addExpression($expr);
        }
    }


    /**
     * Check if container is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_expressions);
    }


    /**
     * Add Expression to collection
     *
     * @param Expression $expr
     *
     * @return void
     */
    public function addExpression(Expression $expr)
    {
        $name = $expr->getName();
        $this->_expressions[$name] = $expr;
    }


    /**
     * Remove the most recently added expression
     *
     * @return Expression
     */
    public function popExpression()
    {
        return array_pop($this->_expressions);
    }


    /**
     * Remove expression by name
     *
     * @param string $name
     *
     * @return void
     */
    public function removeExpression($name)
    {
        unset($this->_expressions[$name]);
    }


    /**
     * Check if expression exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function isExpression($name)
    {
        return isset($this->_expressions[$name]);
    }

    
    /**
     * Get expression by name
     *
     * @param string $name
     *
     * @return Expression If no expression is found FALSE is returned
     */
    public function getExpression($name)
    {
        $return = FALSE;
        if ($this->isExpression($name)) {
            $return = $this->_expressions[$name];
        }

        return $return;
    }


    /**
     * Build where clause from expressions
     *
     * Returns an array with two indices. The first value is a string of
     * expressions constituting the where clause. The second value is an array
     * of values to be used when executing a prepared statement clause. This
     * array is empty if no parameters needs to be replaced.
     *
     * @return array
     */
    public function buildWhereClause()
    {
        $exprs = array();
        $data = array();

        foreach ($this->_expressions as $expr) {
            if ($expr->escapeName()) {
                $name = "`{$expr->getName()}`";
            } else {
                $name = $expr->getName();
            }

            if ($expr->escapeValue()) {
                $value = " ? ";
                $data[] = $expr->getValue();
            } else {
                $value = $expr->getValue();
            }

            $exprs[] = $name . $expr->getOperator() . $value;
        }

        $clause = implode(' AND ', $exprs);

        if (!empty($clause)) {
            $clause = 'WHERE ' . $clause;
        }
        
        return array($clause, $data);
    }


    /**
     * Build set statement from expressions
     *
     * Returns an array with two indices. The first value is a string of
     * expressions constituting the set statement. The second value is an array
     * of values to be used when executing a prepared statement. This array is
     * empty if no parameters needs to be replaced.
     *
     * @return array
     */
    public function buildSetStatement()
    {
        $exprs = array();
        $data = array();

        foreach ($this->_expressions as $expr) {
            if ($expr->escapeName()) {
                $name = "`{$expr->getName()}`";
            } else {
                $name = $expr->getName();
            }

            if ($expr->escapeValue()) {
                $value = " ? ";
                $data[] = $expr->getValue();
            } else {
                $value = $expr->getValue();
            }

            $exprs[] = "$name=$value";
        }

        $clause = implode(',', $exprs);

        if (!empty($clause)) {
            $clause = 'SET ' . $clause;
        }

        return array($clause, $data);
    }


    /**
     * Build a data list from expressions
     *
     * Returns an array with three indices. The first is a string of comma
     * separated list of column names. The second is a string of
     * expressions constituting the values. The third is an array of values to
     * be used when executing a prepared statement. This array is empty if no
     * parameters needs to be replaced.
     *
     * @return array
     */
    public function buildDataList()
    {
        $names = array();
        $exprs = array();
        $data = array();

        foreach ($this->_expressions as $expr) {
            if ($expr->escapeName()) {
                $names[] = "`{$expr->getName()}`";
            } else {
                $names[] = $expr->getName();
            }

            if ($expr->escapeValue()) {
                $exprs[] = " ? ";
                $data[] = $expr->getValue();
            } else {
                $exprs[] = $expr->getValue();
            }
        }

        return array(
            implode(',', $names),
            implode(',', $exprs),
            $data
        );
    }

}
