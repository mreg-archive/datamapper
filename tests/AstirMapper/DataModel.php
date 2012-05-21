<?php
namespace itbz\AstirMapper\tests;
use itbz\AstirMapper\ModelInterface;


class DataModel implements ModelInterface
{
    public function load(array $data)
    {
        $this->id = $data['id'];
        if (isset($data['name'])) {
            $this->name = $data['name'];
        }
    }
}
