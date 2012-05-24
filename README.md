AstirMapper
===========

PHP data mapper

Implementation of the data mapper design pattern.

Supports multiple relational datbases through PDO.

Currently there is no support for NoSQL databases. Support for MongoDB is
planed.


## Limitations

* The PDO subpackage can currently only handle db tables with single column
  primary keys. Eg. composite primary keys is not supported.

* The PDO subpackage currently only support naturally joined tables. Support for
  inner joins is planed.

* The PDO subpackage can only read data from joined tables, not write.


## Using the PDO subpackage

    // create PDO instance
    // $pdo = ...
    
    // create table object
    $table = new \itbz\AstirMapper\PDO\Table\Table('mytable', $pdo);

    // add a naturally joined table
    $join = new \itbz\AstirMapper\PDO\Table\Table('joinedtable', $pdo);
    $table->addNaturalJoin($join);

    // create an instance of your model
    // this instance will be cloned when reading from db
    $prototype = new MyModel();
    
    // create mapper
    $mapper = new \itbz\AstirMapper\PDO\Mapper($table, $prototype);

    // defined search values in a model object
    $model = new MyModel();
    $model->id = 1;
    
    // serch db for all rows matching model
    $iterator = $mapper->findMany($model);

    // iterate over results
    foreach ($iterator as $obj) {
        // $obj is an instance of MyModel with data from database set
    }

    // alter some values
    $model->name = 'foobar';
    
    // write to database
    $mapper->save($model);


## Testing

The testsuite uses the composer autoloader. To run tests from project root dir:

    curl -s http://getcomposer.org/installer | php
    php composer.phar install
    cd tests
    phpunit

If you are using Phing this can ba automated using

    phing test

Or to do a complete build (also triggers CodeSniffer and other checks)

    phing

To test against MySQL add your server credentials to `phpunit.xml`. To only test
the parts that don't require MySQL specify testpath.

    phpunit AstirMapper/PDO
