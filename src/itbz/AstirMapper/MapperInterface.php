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
 */
namespace itbz\AstirMapper;
use Iterator;


/**
 *
 * Basic mapper interface
 *
 * @package AstirMapper
 *
 */
interface MapperInterface
{

    /**
     *
     * Persistently store record
     *
     * @param ModelInterface $record
     *
     * @return int Number of affected rows
     *
     */
    public function save(ModelInterface $record);


    /**
     *
     * Delete record from persistent storage
     *
     * @param ModelInterface $record
     *
	 * @return int Number of affected rows
	 *
     */
    public function delete(ModelInterface $record);


    /**
     *
     * Find record based on current record data
     *
     * @param ModelInterface $record
     *
     * @return ModelInterface
     *
     * @throws NotFoundException if no record was found
     *
     */
    public function find(ModelInterface $record);


    /**
     *
     * Get iterator containing multiple racords based on search
     *
     * @param ModelInterface $record
     *
     * @param SearchInterface $search
     *
     * @return Iterator
     *
     */
    public function findMany(ModelInterface $record, SearchInterface $search);

}
