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
 * @subpackage PDO
 *
 */
namespace itbz\AstirMapper\PDO;
use itbz\AstirMapper\MapperInterface;
use itbz\AstirMapper\ModelInterface;
use itbz\AstirMapper\SearchInterface;
use itbz\AstirMapper\Exception\NotFoundException;
use itbz\AstirMapper\PDO\Table\Table;
use PDO;

/*

    - Attribute är vinklde mot SQL, specielt Operator...
    
    Bool, Null och Wrapper behövs antagligen inte: idéer hit...

        eftersom all data skrivs till databasen via queries så är de i PHPs ögon
            strängar
        
        det betyder att allt ska konverteras till strängar
            då kan jag helt enkelt ha speciella regler för det
         
            convertToString()
            
            bool blir 1 eller 0
            nummer blir helt enkelt sin sträng
            NULL blir null...
            objects blir till sträng om __tostring finns
                annars undantag
            array kan bli comma separerad lista..
            
            och that's it folks,
                på detta sätt blir det verkligen enklare
            
            skriv test till PdoStackTest där jag ser att det verkligen
                skrivs till databasen som jag vill att det ska göras...

            ta även bort Bool-test om det här visar sig lyckosamt...

    // --------------------------------------------------

    $mapper = new MemberMapper($authUser); 
    // kan jag implementera rättigheter såhär??

    $membersIterator = $mapper->findFromFaction($faction);

    osv. jag kan nog fundera ut en massa mer hur detta ska fungera
        vad jag annars vill göra med en member kan jaq se i Member.php
            samt i views.php, member-funktionerna
        

    för adresser kan det se ut såhär
        $mapper = new AddressMapper();
        $address = $mapper->findByMember($member);        
        $address = $mapper->findByFaction($faction);
        osv...
        Att spara adresser blir sedan ett arbete en adress i taget...
        snyggt!!

    
    jag har kvar all funktionalitet jag skrivit i Astir
        men jag bryter ut det till flera olika klasser

        * spara/hämta hamnar i Mapper.php
        * allt med att iterera i Record.php hamnar i en Iterator.php istället
        * setId osv hamnar i varje Model istället
        * getPhoneNumber osv. som ni är i Models hamnar i en egen Mapper
        
        * allting får helt enkelt sin egen plats i klass-strukturen
            vilket helt klart blir en tydlig förbättring!!!!


     börja med att implementera de är idéerna i wrapper
        se sedan om jag kan gå vidare till de Records jag redan har...
        jag kan fortfarande kalla dessa för Records, det är bara det att
            de inte behöver ärva en massa skumma klasser längre...

*/


/**
 *
 * PDO mapper object
 *
 * @package AstirMapper
 *
 * @subpackage PDO
 *
 */
class Mapper implements MapperInterface
{

    /**
     *
     * Table objet to interact with
     *
     * @var Table $_table
     *
     */
    private $_table;


    /**
     *
     * Prototype model that will be cloned on object creation
     *
     * @var ModelInterface $_prototype
     *
     */
    private $_prototype;


    /**
     *
     * Construct and inject table instance
     *
     * @param Table $table
     *
     * @param ModelInterface $prototype Prototype model that will be cloned when
     * mapper needs a new return object.
     *
     */
    public function __construct(Table $table, ModelInterface $prototype)
    {
        $this->_table = $table;
        $this->_prototype = $prototype;
    }


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
    public function findMany(ModelInterface $record, SearchInterface $search)
    {
        $where = $this->recordToSearch($record);
        $stmt = $this->_table->select($search, $where);

        $iterator = new Iterator(
            $stmt,
            $this->_table->getPrimaryKey(),
            $this->_prototype
        );

        return $iterator;
    }


    /**
     *
     * Find records that match current values.
     *
     * @param ModelInterface $record
     *
     * @return ModelInterface
     *
     * @throws NotFoundException if no record was found
     *
     */
    public function find(ModelInterface $record)
    {
        $search = new Search();
        $search->setLimit(1);
        $iterator = $this->findMany($record, $search);
        if (!$iterator->valid()) {
            throw new NotFoundException("No record found");        
        }

        return $iterator->current();
    }


    /**
     *
     * Find record based on primary key
     *
     * @param scalar $key
     *
     * @return ModelInterface
     *
     */
    public function findByPk($key)
    {
        $record = clone $this->_prototype;
        $data = array($this->_table->getPrimaryKey() => $key);
        $record->load($data);

        return $this->find($record);
    }


    /**
     *
     * Delete record from persistent storage
     *
     * @param ModelInterface $record
     *
     * @return int Number of affected rows
     *
     */
    public function delete(ModelInterface $record)
    {
        $columns = array($this->_table->getPrimaryKey());
        $where = $this->recordToSearch($record, $columns);
        $stmt = $this->_table->delete($where);

        return $stmt->rowCount();
    }


    /**
     *
     * Persistently store record
     *
     * If record contains a primary key and that key exists in the database
     * record is updated. Else record is inserted.
     *
     * @param ModelInterface $record
     *
     * @return int Number of affected rows
     *
     */
    public function save(ModelInterface $record)
    {
        $pk = $this->_table->getPrimaryKey();
        $params = $this->readRecordParams($record, array($pk));
        
        try {
            if (isset($params[$pk]) && $this->findByPk($params[$pk])) {
                // Record has a primary key and that key exists in db

                return $this->update($record);
            }
        } catch (NotFoundException $e) {
            // Do nothing, exception triggers insert, as do records with no PK
        }

        return $this->insert($record);
    }


    /**
     *
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
     *
     * Insert record into table
     *
     * @param ModelInterface $record
     *
     * @return int Number of affected rows
     *
     */
    protected function insert(ModelInterface $record)
    {
        $data = $this->recordToInsert($record);
        $stmt = $this->_table->insert($data);

        return $stmt->rowCount();
    }


    /**
     *
     * Update db using primary key as where clause.
     *
     * @param ModelInterface $record
     *
     * @return int Number of affected rows
     *
     */
    protected function update(ModelInterface $record)
    {
        $columns = array($this->_table->getPrimaryKey());
        $where = $this->recordToSearch($record, $columns);
        $data = $this->recordToInsert($record);
        $stmt = $this->_table->update($data, $where);

        return $stmt->rowCount();
    }


    /**
     *
     * Get record data as an array with key value pairs ready
     * to be written to database.
     *
     * @param ModelInterface $record
     *
     * @return array
     *
     */
    private function recordToInsert(ModelInterface $record)
    {
        $data = array();

        $params = $this->readRecordParams(
            $record,
            $this->_table->getNativeColumns()
        );

        foreach ($params as $col => $val) {
            $use = TRUE;
            if ( is_a($val, 'itbz\AstirMapper\Attribute\AttributeInterface') ) {
                $val = $val->toInsertSql($use);
            }
            if ( $use ) {
                $data[$col] = $val;
            }
        }
        
        return $data;
    }


    /**
     *
     * Get record data as an array with sql context as keys
     *
     * @param ModelInterface $record
     *
     * @param array $cols List of columns to include
     *
     * @return array
     *
     */
    private function recordToSearch(ModelInterface $record, array $cols = NULL)
    {
        $data = array();

        if (!$cols) {
            $cols = $this->_table->getNativeColumns();
        }

        foreach ($this->readRecordParams($record, $cols) as $col => $val) {
            // :name: will be replaced by attribute name below
            $context = ":name:=?";
            if ( is_a($val, 'itbz\AstirMapper\Attribute\AttributeInterface') ) {
                $val = $val->toSearchSql($context);
            }
            if ($context !== FALSE) {
                $context = str_replace(':name:', "`$col`", $context);
                $data[$context] = (string)$val;
            }
        
        }

        return $data;
    }



    /**
     *
     * Read params from record
     *
     * All param names are converted to camel-case. If the requested param is
     * 'fooBar' this method will first look for a method 'getFooBar()'. If
     * method does not exist object var 'fooBar' is searched. If var does
     * not exist param is skipped.
     *
     * @param ModelInterface $record
     *
     * @param array $params List of params to read
     *
     * @return array
     *
     */
    private function readRecordParams(ModelInterface $record, array $params)
    {
        $return = array();
        foreach ($params as $param) {
            $method = $this->getCamelCase($param, 'get');
            $camelParam = $this->getCamelCase($param);

            if (method_exists($record, $method)) {
                $return[$param] = $record->$method();
            } elseif (isset($record->$camelParam)) {
                $return[$param] = $record->$camelParam;
            }
        }
        
        return $return;
    }



    /**
     *
     * Convert string to camel case
     *
     * Only the first letter if each word is converted. Else original casing
     * is preserved. Underscore is treated as a word delimiter.
     *
     * @param string $str
     *
     * @param string $prefix
     *
     * @return string
     *
     */
    private function getCamelCase($str, $prefix = '')
    {
        $words = explode('_', $str);
        if ($prefix) {
            array_unshift($words, $prefix);
        }
        $camel = lcfirst(array_shift($words));
        while ($word = array_shift($words)) {
            $camel .= ucfirst($word);
        }
        
        return $camel;
    }

}
