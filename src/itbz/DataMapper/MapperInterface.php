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
 */
namespace itbz\DataMapper;


/**
 * Basic mapper interface
 *
 * @package DataMapper
 */
interface MapperInterface
{

    /**
     * Extract context for data creation
     *
     * @const CONTEXT_CREATE
     */
    const CONTEXT_CREATE = 1;


    /**
     * Extract context for data reads
     *
     * @const CONTEXT_READ
     */
    const CONTEXT_READ = 2;

    /**
     * Extract context for data updates
     *
     * @const CONTEXT_UPDATE
     */
    const CONTEXT_UPDATE = 3;

    /**
     * Extract context for data deletes
     *
     * @const CONTEXT_DELETE
     */
    const CONTEXT_DELETE = 4;


    /**
     * Persistently store model
     *
     * @param ModelInterface $model
     *
     * @return int Number of affected rows
     */
    public function save(ModelInterface $model);


    /**
     * Delete model from persistent storage
     *
     * @param ModelInterface $model
     *
	 * @return int Number of affected rows
     */
    public function delete(ModelInterface $model);


    /**
     * Read data from persistent storage
     *
     * @param array $conditions
     *
     * @return ModelInterface
     *
     * @throws NotFoundException if nothing was found
     */
    public function find(array $conditions);


    /**
     * Get iterator containing multiple models based on search
     *
     * @param array $conditions
     *
     * @param SearchInterface $search
     *
     * @return \Iterator
     */
    public function findMany(array $conditions, SearchInterface $search);

}
