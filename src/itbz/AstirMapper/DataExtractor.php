<?php
/**
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
 */
namespace itbz\AstirMapper;
use itbz\AstirMapper\ModelInterface;


/**
 * Extract data from a model instance
 *
 * @package AstirMapper
 */
class DataExtractor
{

    /**
     * Extract data from model
     *
     * Property names are converted to method calls by prefixing name with
     * 'get' and removing all non alpha-numeric characters. If method exists the
     * return value is extracted. If not property is read directly from model.
     *
     * @param object $model
     *
     * @param array $properties List of properties to extract.
     *
     * @return array
     *
     * @throws Exception if model is not an object
     */
    public static function extract($model, array $properties)
    {
        if (!is_object($model)) {
            $type = get_type($model);
            $msg = "Model must be an object, '$type' given";
            throw new Exception($msg);
        }

        $data = array();
        foreach ($properties as $property) {
            $method = 'get' . preg_replace('/[^0-9a-z]/i', '', $property);
            if (method_exists($model, $method)) {
                $data[$property] = $model->$method();
            } elseif (property_exists($model, $property)) {
                $data[$property] = $model->$property;
            }
        }
        
        return $data;
    }

}
