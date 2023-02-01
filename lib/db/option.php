<?php
namespace DB;

class Option extends Entity {
    const DIRNAME = "options";
    const TABLE = [
        "etudiantsGSI" => ["Vector", "of" => ["DB\\User"]],
        "placesGSI" => ["Integer", "ge" => 0],
        "etudiantsMI" => ["Vector", "of" => ["DB\\User"]],
        "placesMI" => ["Integer", "ge" => 0],
        "etudiantsMF" => ["Vector", "of" => ["DB\\User"]],
        "placesMF" => ["Integer", "ge" => 0],
    ];

    public static function create($id) {
        $option = parent::create($id);
        $option->etudiantsGSI = [];
        $option->etudiantsMI = [];
        $option->etudiantsMF = [];
        return $option;
    }
}
?>
