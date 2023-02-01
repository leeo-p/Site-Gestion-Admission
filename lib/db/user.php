<?php
namespace DB;

class User extends Entity {
    const DIRNAME = "users";
    const TABLE = [
        "prenom" => ["Str"],
        "nom" => ["Str"],
        "password" => ["Str"],
        "default_password" => ["Str"],
        "role" => ["Str", "in" => ["admin", "responsable", "etudiant"]],
        "parcours" => ["Str", "in" => ["GSI", "MI", "MF"]],
        "ects" => ["Integer", "ge" => 0],
        "moyenne" => ["Real", "ge" => 0, "le" => 20],
        "wishes" => ["Vector", "of" => ["Str"]],
        "option" => ["Str"],
        "birthdate" => ["DateTime", "format" => "Y-m-d"],
        "address" => ["Str"],
        "tel" => ["Str"],
        "profile_picture" => ["Blob"],
        "blocked" => ["Vector", "of" => ["Str"]],
    ];

    public function __construct($id = null) {
        if ($id === null)
            $id = $_SESSION["id"];
        parent::__construct($id);
    }
}
?>
