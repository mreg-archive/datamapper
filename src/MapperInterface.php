<?php
/**
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/ for more details.
 */

namespace datamapper;

/**
 * @author Hannes Forsgård <hannes.forsgard@fripost.org>
 */
interface MapperInterface extends ExtractInterface
{
    /**
     * Persistently store model
     *
     * @param ModelInterface $model
     * @return int Number of affected rows
     */
    public function save(ModelInterface $model);

    /**
     * Delete model from persistent storage
     *
     * @param ModelInterface $model
     * @return int Number of affected rows
     */
    public function delete(ModelInterface $model);

    /**
     * Read data from persistent storage
     *
     * @param array $conditions
     * @return ModelInterface
     * @throws NotFoundException if nothing was found
     */
    public function find(array $conditions);

    /**
     * Get iterator containing multiple models based on search
     *
     * @param array $conditions
     * @param SearchInterface $search
     * @return \Iterator
     */
    public function findMany(array $conditions, SearchInterface $search);
}
