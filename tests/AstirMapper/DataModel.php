<?php
namespace itbz\AstirMapper\tests;
use itbz\AstirMapper\ModelInterface;


class DataModel implements ModelInterface
{
    public function load(array $data)
    {
        foreach ($data as $key => $value)
        {
            $this->$key = $value;
        }
    }
}
