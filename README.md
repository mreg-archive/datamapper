AstirMapper
===========

PHP data mapper


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
