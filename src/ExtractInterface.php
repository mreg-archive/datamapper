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
 * @todo Create models, load and retreive data in closures
 *     $map = new Mapper(
 *         $adaptor,
 *         Callable $modelFactory,
 *         Callable $modelLoader = null,
 *         Callable $modelRetreiver = null
 *     );
 *     $modelFactory = function () {
 *         return new Model;
 *     };
 *     $modelLoader = function($model, $data) {
 *         foreach ($data as $key => $val) {
 *             $model->$key = $val;
 *         }
 *     };
 */
interface ExtractInterface
{
    /**
     * Extract context for data creation
     */
    const CONTEXT_CREATE = 1;

    /**
     * Extract context for data reads
     */
    const CONTEXT_READ = 2;

    /**
     * Extract context for data updates
     */
    const CONTEXT_UPDATE = 3;

    /**
     * Extract context for data deletes
     */
    const CONTEXT_DELETE = 4;
}
