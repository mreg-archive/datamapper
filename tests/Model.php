<?php
namespace iio\datamapper\tests;

class Model implements \iio\datamapper\ModelInterface
{

    public function load(array $data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    public function extract($context, array $using)
    {
        $data = array();
        foreach ($this as $key => $value) {
            $data[$key] = $value;
        }

        return $data;
    }
}
