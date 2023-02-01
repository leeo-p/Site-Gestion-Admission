<?php
namespace DB\Storers;
use DB\Storer;

class Integer extends Storer {
    const TYPE = "int";

    protected static function ge($x, $value) {
        if ($value < $x)
            throw new \RuntimeException("$value is not greater or equal than $x");
    }

    protected static function le($x, $value) {
        if ($value > $x)
            throw new \RuntimeException("$value is not less or equal than $x");
    }
}
?>
