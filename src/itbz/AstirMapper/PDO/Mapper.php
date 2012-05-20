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
namespace itbz\AstirMapper;
use itbz\AstirMapper\MapperInterface;


/*

    ** intressant med hur grumpy gör sin grej med mappers
        med protected för alla värden
        och sedan magic methods för get och set på alla dessa värden...
        
        jag skulle kunna skriva en mapper som tar ett table object
            hittar vilka fält som är med (om det inte hårdkodas i mapper)
            kan spara ett object med hjälp av att läsa namn på fälten direkt från object
                eller leta efter funktioner av typ getParam()

            $mapper = new MemberMapper($authUser);

            $mapper->save($member);

            $mapper->find($member)
                kan läsa den info som finns i member, och sedan fylla member med det som saknas

            $mapper->findById(1);


            $iterator = $mappar->findAllMembers($member);
                kan läsa info från member men hämta mycket olika värden...
            
            $membersIterator = $mapper->findFromFaction($faction);

            osv. jag kan nog fundera ut en massa mer hur detta ska fungera
                vad jag annars vill göra med en member kan jaq se i Member.php
                    samt i views.php, member-funktionerna
            
            det här tror jag kan bli jävligt cool...
            
            det blir antagligen ganska enkelt att implementera min rättighetskod
                i en sån här mapper...


        för adresser kan det se ut såhär
            $mapper = new AddressMapper();
            $address = $mapper->findByMember($member);        
            $address = $mapper->findByFaction($faction);
            osv...
            Att spara adresser blir sedan ett arbete en adress i taget...
            snyggt!!

        skapa ett Mapper gränssnitt
            - skapa seda PDOmapper som en implementation
            - och PDOAUthMapper som en subklass
            - sedan kan jag om jag vill skapa MangoMappers osv...
        
        
        
         fundera om jag kanske kan använda detta gränssnitt istället för Wrapper som jag har skrivit nu...
            jag tror att detta helt enkelt är en riktigt bra idé
            men utvärdera innan jag gör klar...


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
    protected $_table;


    /**
     *
     * Construct and inject table instance
     *
     * @param Table $table
     *
     */
    public function __construct(Table $table)
    {
        $this->_table = $table;
    }


    /**
     *
     * Read a param from record
     *
     * @param string $param
     *
     * @return mixed
     *
     */
    private function readRecordParam(ModelInterface $record, $param)
    {
        // hämta value från record
        // antingen från getParam() metod
        // eller direkt via $record->param
        // TODO vad ska den returnera om param inte kan läsas???
        return '';
    }


    /**
     *
     * Get record data as an array with key value pairs ready
     * to be written to database.
     *
     * @return array
     *
     */
    private function parseRecordForInsert(ModelInterface $record)
    {
        $data = array();
        foreach ($this->_table->getNativeColumns() as $colname) {
            $value = $this->readRecordParam($record, $colname);
            if ( is_a($value, "itbz\\AstirMapper\\Attribute") ) {
                $use = TRUE;
                $value = $value->toInsertSql($use);
                if ( !$use ) {
                    continue;
                }
                $data[$colname] = $value;
            }
        }
        
        return $data;
    }


    /*
        här håller jag på
            dessa metoder ska användas för att kunna skriva models till databasen....
            hämta från databasen och ladda i modell
            hämta många och returnera en iterator ifrån vilken olika rows kan hämtas en i taget...

            kolla på save/delete/update/inset/search metoderna i Astir\Record så går det snabbare att implementera...

            
        TODO jag ska också skriva MysqlTable som kan reverseEngineer mysql...
        TODO jag ska också skriva SqliteTable som kan reverseEngineer sqlite...
    */


    /**
     *
     * Get record data as an array with sql context as keys
     *
     * @param array $columns List of columns to include
     *
     * @return array
     *
     */
    private function parseRecordForSearch(array $columns = NULL)
    {

        // TODO den här har jag inte arbetat om
        // den är fortfarande samma som från Astir/Record...

        // The data to process
        $data = $this->toArray();

        // Remove properties not listed in columns
        if ( $columns ) {
            $data = array_intersect_key($data, array_flip($columns));
        }

        $data = $this->tearDown($data);
        
        // Create the return array
        $toSql = array();

        foreach ( $data as $attr => &$val ) {
            // Default attribute SQL context
            $context = ":name: = ?";
            // Converte Attributes to SQL values
            if ( is_a($val, "itbz\\Astir\\Attribute") ) {
                $val = $val->toSearchSql($context);
            }
            if ( $context !== FALSE ) {
                // Replace :name: with attribute name
                $context = str_replace(':name:', "`$attr`", $context);
                $toSql[$context] = (string)$val;
            }
        }
        
        return $toSql;
    }

}
