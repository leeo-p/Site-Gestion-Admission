<?php
namespace DB\Storers;
use DB\Storer;

class Vector extends Storer {
    public static function toStored($spec, $value) {
        if (!isset($spec["of"]))
            throw new \LogicException("spec is missing key `of`");

        $of = $spec["of"];
        return implode("\n", array_map(function($v) use ($of) {
            return \DB\Entity::toStored($of, $v);
        }, $value));
    }

    public static function fromStored($spec, $value) {
        if (!isset($spec["of"]))
            throw new \LogicException("spec is missing key `of`");

        $of = $spec["of"];
        return array_map(function($v) use ($of) {
            return \DB\Entity::fromStored($of, $v);
        }, array_filter(explode("\n", $value), function($v) {
            return $v !== "";
        }));
    }
}
?>
