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
use itbz\DataMapper\MapperInterface;
use itbz\DataMapper\ModelInterface;
use itbz\DataMapper\IgnoreAttributeInterface;
use itbz\DataMapper\SearchInterface;
use itbz\DataMapper\Exception\NotFoundException;
use itbz\DataMapper\Exception;
use itbz\DataMapper\PDO\Table\Table;
use PDOStatement;


/**
 * PDO mapper object
 *
 * @package DataMapper
 *
 * @subpackage PDO
 */
class Mapper implements MapperInterface
{

    /**
     * Table object to interact with
     *
     * @var Table
     */
    protected $_table;


    /**
     * Prototype model that will be cloned on object creation
     *
     * @var ModelInterface
     */
    private $_prototype;


    /**
     * Construct and inject table instance
     *
     * @param Table $table
     *
     * @param ModelInterface $prototype Prototype model that will be cloned when
     * mapper needs a new return object.
     */
    public function __construct(Table $table, ModelInterface $prototype)
    {
        $this->_table = $table;
        $this->_prototype = $prototype;
    }


    /**
     * Get iterator containing multiple racords based on search
     *
     * @param array $conditions
     *
     * @param SearchInterface $search
     *
     * @return \Iterator
     */
    public function findMany(array $conditions, SearchInterface $search)
    {
        $stmt = $this->_table->select(
            $search,
            $this->arrayToExprSet($conditions)
        );

        return $this->getIterator($stmt);
    }


    /**
     * Find models that match current model values.
     *
     * @param array $conditions
     *
     * @return ModelInterface
     *
     * @throws NotFoundException if no model was found
     */
    public function find(array $conditions)
    {
        $search = new Search();
        $search->setLimit(1);
        $iterator = $this->findMany($conditions, $search);

        // Return first object in iterator
        foreach ($iterator as $object) {
            
            return $object;
        }

        // This only happens if iterator is empty
        throw new NotFoundException("No matching records found");        
    }


    /**
     * Find model based on primary key
     *
     * @param mixed $key
     *
     * @return ModelInterface
     */
    public function findByPk($key)
    {
        return $this->find(
            array($this->_table->getPrimaryKey() => $key)
        );
    }


    /**
     * Delete model from persistent storage
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     */
    public function delete(ModelInterface $model)
    {
        $pk = $this->_table->getPrimaryKey();
        $conditions = $this->extractForDelete($model, array($pk));
        $stmt = $this->_table->delete($conditions);

        return $stmt->rowCount();
    }


    /**
     * Persistently store model
     *
     * If model contains a primary key and that key exists in the database
     * model is updated. Else model is inserted.
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
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
     * Get primary key from model
     *
     * @param ModelInterface $model
     *
     * @return string Empty string if no key was found
     */
    public function getPk(ModelInterface $model)
    {
        $pk = $this->_table->getPrimaryKey();
        $exprSet = $this->extractForRead($model, array($pk));

        if ($exprSet->isExpression($pk)) {

            return $exprSet->getExpression($pk)->getValue();
        }
        
        return '';
    }


    /**
     * Get a new prototype clone
     *
     * @return ModelInterface
     */
    public function getNewModel()
    {
        return clone $this->_prototype;
    }


    /**
     * Insert model into db
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     */
    protected function insert(ModelInterface $model)
    {
        $data = $this->extractForCreate($model);
        $stmt = $this->_table->insert($data);

        return $stmt->rowCount();
    }


    /**
     * Update db using primary key as conditions clause.
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     */
    protected function update(ModelInterface $model)
    {
        $data = $this->extractForUpdate($model);
        $pk = $this->_table->getPrimaryKey();
        $conditions = $this->extractForRead($model, array($pk));
        $stmt = $this->_table->update($data, $conditions);

        return $stmt->rowCount();
    }


    /**
     * Get iterator for PDOStatement
     *
     * @param PDOStatement $stmt
     *
     * @return \Iterator
     */
    protected function getIterator(PDOStatement $stmt)
    {
        return new Iterator(
            $stmt,
            $this->_table->getPrimaryKey(),
            $this->_prototype
        );
    }


    /**
     * Extract data from model
     *
     * This method should not be called directly. Use one of 'extractForCreate',
     * 'extractForRead', 'extractForUpdate' or 'extractForDelete' instead.
     *
     * @param ModelInterface $model
     *
     * @param int $context Extract context
     *
     * @param array $use List of model properties to extract. Defaults to table
     * native columns.
     *
     * @return ExpressionSet
     *
     * @throws Exception if extract context is invalid
     *
     * @throws Exception if model extract does not return an array
     */
    protected function extractArray(
        ModelInterface $model,
        $context,
        array $use = NULL
    )
    {
        // @codeCoverageIgnoreStart
        if (!$context) {
            $msg = "Invalid extract context '$context'";
            throw new Exception($msg);
        }
        // @codeCoverageIgnoreEnd

        if (!$use) {
            $use = $this->_table->getNativeColumns();
        }

        $data = $model->extract($context, $use);
        
        if (!is_array($data)) {
            $type = gettype($data);
            $msg = "Model extract must return an array, found '$type'";
            throw new Exception($msg);
        }
        
        $data = array_intersect_key($data, array_flip($use));

        return $data;
    }


    /**
     * Extract data from model for data inserts
     *
     * @param ModelInterface $mod
     *
     * @param array $use
     *
     * @return ExpressionSet
     */
    protected function extractForCreate(ModelInterface $mod, array $use = NULL)
    {
        return $this->arrayToExprSet(
            $this->extractArray($mod, self::CONTEXT_CREATE, $use)
        );
    }


    /**
     * Extract data from model for data read
     *
     * @param ModelInterface $mod
     *
     * @param array $use
     *
     * @return ExpressionSet
     */
    protected function extractForRead(ModelInterface $mod, array $use = NULL)
    {
        return $this->arrayToExprSet(
            $this->extractArray($mod, self::CONTEXT_READ, $use)
        );
    }


    /**
     * Extract data from model for data updates
     *
     * @param ModelInterface $mod
     *
     * @param array $use
     *
     * @return ExpressionSet
     */
    protected function extractForUpdate(ModelInterface $mod, array $use = NULL)
    {
        return $this->arrayToExprSet(
            $this->extractArray($mod, self::CONTEXT_UPDATE, $use)
        );
    }


    /**
     * Extract data from model for data deletes
     *
     * @param ModelInterface $mod
     *
     * @param array $use
     *
     * @return ExpressionSet
     */
    protected function extractForDelete(ModelInterface $mod, array $use = NULL)
    {
        return $this->arrayToExprSet(
            $this->extractArray($mod, self::CONTEXT_DELETE, $use)
        );
    }


    /**
     * Convert array to ExpressionSet
     *
     * @param array $data
     *
     * @return ExpressionSet
     */
    protected function arrayToExprSet(array $data)
    {
        $exprSet = new ExpressionSet();
        foreach ($data as $name => $expr) {
            if (!$expr instanceof Expression) {
                $expr = new Expression($name, $expr);
            }
            if (!$expr instanceof IgnoreAttributeInterface) {
                $exprSet->addExpression($expr);
            }
        }
        
        return $exprSet;
    }

}
