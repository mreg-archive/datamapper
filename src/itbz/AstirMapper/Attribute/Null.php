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
namespace itbz\AstirMapper\Attribute;


//TODO frågan är om jag behöver det här
//det hade varit snyggare om en verkligen kuna skicka in NULL som värde...


/**
 *
 * Null class..
 *
 * @package AstirMapper
 *
 */
class Null implements AttributeInterface
{

    /**
     * Generic get this field as an sql prepared string. If you need different
     * sql expression for writes and/or reads se toWriteSql() and toReadSql().
     * @param bool &$bUse Set to FALSE if this field should be ignored
     * @param bool &$bEscape Set to true if returned string should be escaped
     * and enclosed in ''
     * @return string
     */
    public function toSql(&$bUse=true, &$bEscape=true)
    {
        $bUse = true;
        $bEscape = FALSE;
        return 'null';
    }

}
