<?php
namespace DB\Storers;
use DB\Storer;

class DateTime extends Storer {
    public static function toStored($spec, $value) {
        if (!($value instanceof \DateTimeInterface))
            throw new \InvalidArgumentException("expected a DateTimeInterface");

        $format = $spec["format"] ?? \DateTime::ISO8601;
        return $value->format($format);
    }

    public static function fromStored($spec, $value) {
        $format = $spec["format"] ?? \DateTime::ISO8601;
        $date = \DateTime::createFromFormat($format, $value);
        if ($date === false)
            throw new \RuntimeException("$value is not a valid date of format $format");
        return $date;
    }
}
?>
