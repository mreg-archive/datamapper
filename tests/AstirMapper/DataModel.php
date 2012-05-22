<?php
namespace itbz\AstirMapper\tests;
use itbz\AstirMapper\ModelInterface;


class DataModel implements ModelInterface
{
    public function load(array $data)
    {
        $this->id = $data['id'];
        if (array_key_exists('name', $data)) {
            $this->name = $data['name'];
        }
    }
}
