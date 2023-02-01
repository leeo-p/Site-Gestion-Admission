<?php
namespace DB;

abstract class Storer {
    final protected static function validate($spec, $value) {
        unset($spec[0]);
        foreach ($spec as $k => $v)
            static::$k($v, $value);
    }

    public static function toStored($spec, $value) {
        self::validate($spec, $value);
        if (!settype($value, static::TYPE))
            throw new \RuntimeException("failed to convert $value to " . static::TYPE);
        return $value;
    }

    public static function fromStored($spec, $value) {
        if (!settype($value, static::TYPE))
            throw new \RuntimeException("failed to convert $value to " . static::TYPE);
        self::validate($spec, $value);
        return $value;
    }
}
?>
