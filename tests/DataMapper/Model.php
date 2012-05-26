<?php

class Model implements \itbz\DataMapper\ModelInterface
{
    public function load(array $data)
    {
        foreach ($data as $key => $value)
        {
            $this->$key = $value;
        }
    }
}
