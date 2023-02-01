<?php
namespace DB\Storers;
use DB\Storer;

class Blob extends Storer {
    public static function toStored($spec, $value) {
        return $value;
    }

    public static function fromStored($spec, $value) {
        return $value;
    }
}
?>
