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
use itbz\AstirMapper\MapperInterface;
use itbz\AstirMapper\ModelInterface;
use itbz\AstirMapper\SearchInterface;
use itbz\AstirMapper\Exception\NotFoundException;
use itbz\AstirMapper\Exception;
use itbz\AstirMapper\PDO\Table\Table;


/**
 *
 * PDO mapper object
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
class Mapper implements MapperInterface
{

    /**
     *
     * Table objet to interact with
     *
     * @var Table $_table
     *
     */
    protected $_table;


    /**
     *
     * Prototype model that will be cloned on object creation
     *
     * @var ModelInterface $_prototype
     *
     */
    private $_prototype;


    /**
     *
     * Construct and inject table instance
     *
     * @param Table $table
     *
     * @param ModelInterface $prototype Prototype model that will be cloned when
     * mapper needs a new return object.
     *
     */
    public function __construct(Table $table, ModelInterface $prototype)
    {
        $this->_table = $table;
        $this->_prototype = $prototype;
    }


    /**
     *
     * Get iterator containing multiple racords based on search
     *
     * @param array $where
     *
     * @param SearchInterface $search
     *
     * @return \Iterator
     *
     */
    public function findMany(array $where, SearchInterface $search)
    {
        $stmt = $this->_table->select(
            $search,
            $this->arrayToExprSet($where)
        );

        return $this->getIterator($stmt);
    }


    /**
     *
     * Find models that match current model values.
     *
     * @param array $where
     *
     * @return ModelInterface
     *
     * @throws NotFoundException if no model was found
     *
     */
    public function find(array $where)
    {
        $search = new Search();
        $search->setLimit(1);
        $iterator = $this->findMany($where, $search);

        // Return first object in iterator
        foreach ($iterator as $object) {
            
            return $object;
        }

        // This only happens if iterator is empty
        throw new NotFoundException("No matching records found");        
    }


    /**
     *
     * Find model based on primary key
     *
     * @param mixed $key
     *
     * @return ModelInterface
     *
     */
    public function findByPk($key)
    {
        return $this->find(
            array($this->_table->getPrimaryKey() => $key)
        );
    }


    /**
     *
     * Delete model from persistent storage
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     *
     */
    public function delete(ModelInterface $model)
    {
        $pk = $this->_table->getPrimaryKey();
        $where = $this->extract($model, array($pk));
        $stmt = $this->_table->delete($where);

        return $stmt->rowCount();
    }


    /**
     *
     * Persistently store model
     *
     * If model contains a primary key and that key exists in the database
     * model is updated. Else model is inserted.
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     *
     */
    public function save(ModelInterface $model)
    {
        try {
            $pk = $this->getPk($model);
            if ($pk && $this->findByPk($pk)) {
                // Model has a primary key and that key exists in db

                return $this->update($model);
            }
        } catch (NotFoundException $e) {
            // Do nothing, exception triggers insert, as do models with no PK
        }

        return $this->insert($model);
    }


    /**
     *
     * Get the ID of the last inserted row.
     *
     * The return value will only be meaningful on tables with an auto-increment
     * field and with a PDO driver that supports auto-increment. Must be called
     * directly after an insert.
     *
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->_table->lastInsertId();
    }


    /**
     *
     * Get primary key from model
     *
     * @param ModelInterface $model
     *
     * @return string Empty string if no key was found
     *
     */
    public function getPk(ModelInterface $model)
    {
        $pkName = $this->_table->getPrimaryKey();
        $exprSet = $this->extract($model, array($pkName));
        $pk = '';
        if ($exprSet->isExpression($pkName)) {
            $pk = $exprSet->getExpression($pkName)->getValue();
        }
        
        return $pk;
    }


    /**
     *
     * Get a new prototype clone
     *
     * @return ModelInterface
     *
     */
    public function getModel()
    {
        return clone $this->_prototype;
    }


    /**
     *
     * Insert model into db
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     *
     */
    protected function insert(ModelInterface $model)
    {
        $data = $this->extract($model);
        $stmt = $this->_table->insert($data);

        return $stmt->rowCount();
    }


    /**
     *
     * Update db using primary key as where clause.
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     *
     */
    protected function update(ModelInterface $model)
    {
        $data = $this->extract($model);
        $pk = $this->_table->getPrimaryKey();
        $where = $this->extract($model, array($pk));
        $stmt = $this->_table->update($data, $where);

        return $stmt->rowCount();
    }


    /**
     *
     * Get iterator for PDOStatement
     *
     * @param \PDOStatement $stmt
     *
     * @return \Iterator
     *
     */
    protected function getIterator(\PDOStatement $stmt)
    {
        return new Iterator(
            $stmt,
            $this->_table->getPrimaryKey(),
            $this->_prototype
        );
    }


    /**
     *
     * Extract data from model
     *
     * Property name is converted to a method call on model by prefixing name
     * with 'get' and removing all non alpha-numeric characters. If method
     * exists the return value is extracted. If not property is read directly
     * from model.
     *
     * @param ModelInterface $model
     *
     * @param array $extract List of properties to extract. Defaults to native
     * table column names.
     *
     * @return ExpressionSet
     *
     */
    protected function extract(ModelInterface $model, array $extract = NULL)
    {
        if (!$extract) {
            $extract = $this->_table->getNativeColumns();
        }

        $data = array();
        foreach ($extract as $property) {
            $method = 'get' . preg_replace('/[^0-9a-z]/i', '', $property);
            if (method_exists($model, $method)) {
                $data[$property] = $model->$method();
            } elseif (property_exists($model, $property)) {
                $data[$property] = $model->$property;
            }
        }
        
        return $this->arrayToExprSet($data);
    }


    /**
     *
     * Convert array to ExpressionSet
     *
     * @param array $data
     *
     * @return ExpressionSet
     *
     */
    protected function arrayToExprSet(array $data)
    {
        $exprSet = new ExpressionSet();
        foreach ($data as $name => $expr) {
            if (!$expr instanceof Expression) {
                $expr = new Expression($name, $expr);
            }
            if (!$expr instanceof Ignore) {
                $exprSet->addExpression($expr);
            }
        }
        
        return $exprSet;
    }

}
