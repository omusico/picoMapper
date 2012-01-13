<?php

require_once 'src/picoMapper.php';


class Foo {

    private $value;


    public function __construct($value = null) {

        $this->value = $value;
    }


    public function getValue() {

        return $this->value;
    }


    public function setValue($v) {

        $this->value = $v;
    }


    public function toArray() {

        return true;
    }
}


class CollectionTest extends PHPUnit_Framework_TestCase {


    public function testCollection() {

        $collection = new \picoMapper\Collection();
        $collection[] = new Foo();

        $this->assertEquals(1, $collection->count());

        unset($collection[0]);

        $this->assertEquals(0, $collection->count());

        for ($i = 0; $i < 10; $i++) {

            $collection[] = new Foo($i);
        }

        $this->assertEquals($i, $collection->count());

        $i = 0;

        foreach ($collection as $c) {

            $this->assertEquals($i, $c->getValue());
            $i++;
        }

        $this->assertEquals(true, isset($collection[1]));

        $collection[2]->setValue(666);

        $this->assertEquals(666, $collection[2]->getValue()); 
    }


    public function testToArray() {

        $collection = new \picoMapper\Collection();
        $collection[] = new Foo();
        $collection[] = new Foo();

        $rs = $collection->toArray();

        $this->assertEquals(array(true, true), $rs);
    }
}

