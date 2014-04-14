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
interface ModelInterface extends ExtractInterface
{
    /**
     * Fill model with data from associative array
     *
     * @param array $data
     * @return void
     */
    public function load(array $data);

    /**
     * Extract data from model
     *
     * Model must return an array with attribute names as keys and extracted
     * content as values.
     *
     * @param int $context CRUD context for this extract. One of
     *     'self::CONTEXT_CREATE', 'self::CONTEXT_READ', 'self::CONTEXT_UPDATE' or
     *     'self::CONTEXT_DELETE'.
     * @param array $using List of model attributes to be extracted. Models does
     *     not have to honor tis list, as unvanted attributes are removed after
     *     extraction is complete.
     * @return array
     */
    public function extract($context, array $using);
}
