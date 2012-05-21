<?php
/**
 * This file is part of Active.
 * @author Hannes Forsgård <hannes.forsgard@gmail.com>
 * @copyright Copyright (c) 2011, Hannes Forsgård
 * @license http://www.gnu.org/licenses/ GNU Public License
 * @package Active
 * @subpackage Field
 */


//TODO frågan är om jag behöver det här
//det hade varit snyggare om en verkligen kuna skicka in NULL som värde...


/**
 * Null class..
 *
 * @package Active
 * @subpackage Field
 */
class Active_Field_Null extends Active_Field_Basic {

	/**
	 * Generic get this field as an sql prepared string. If you need different
	 * sql expression for writes and/or reads se toWriteSql() and toReadSql().
	 * @param bool &$bUse Set to FALSE if this field should be ignored
	 * @param bool &$bEscape Set to true if returned string should be escaped
	 * and enclosed in ''
	 * @return string
	 */
	public function toSql(&$bUse=true, &$bEscape=true){
		$bUse = true;
		$bEscape = FALSE;
		return 'null';
	}

}
