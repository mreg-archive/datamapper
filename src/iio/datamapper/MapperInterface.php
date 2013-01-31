<?php
/**
 * This file is part of the datamapper package
 *
 * Copyright (c) 2012 Hannes Forsgård
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @package datamapper
 */

namespace iio\datamapper;

/**
 * Basic mapper interface
 *
 * @package datamapper
 */
interface MapperInterface extends ExtractInterface
{
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
     * @param SearchInterface $search
     *
     * @return \Iterator
     */
    public function findMany(array $conditions, SearchInterface $search);
}
