<?php
namespace DB\Storers;
use DB\Storer;

class Str extends Storer {
    const TYPE = "string";

    protected static function in($choices, $value) {
        if (!in_array($value, $choices))
            throw new \RuntimeException("$value is not in $choices");
    }

    protected static function re($re, $value) {
        $res = preg_match($re, $value);
        if ($res === false)
            throw new \DomainException("regex is not valid");
        if ($res === 0)
            throw new \RuntimeException("$value does not match $re");
    }

    public static function toStored($spec, $value) {
        $value = trim($value);
        self::validate($spec, $value);
        return $value;
    }
}
?>
