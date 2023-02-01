<?php
if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

if ($_SESSION["role"] !== "responsable") {
    http_response_code(401);
    exit;
}

require_once "../lib.php";

$parcours_nb_wishes = [
    "GSI" => 8,
    "MI" => 6,
    "MF" => 2,
];

foreach ($parcours_nb_wishes as $parcours => $nb_wishes) {
    $options = DB\Option::getAll();
    $options_places = [];
    foreach ($options as $o)
        $options_places[$o->id] = $o->{"places$parcours"};

    if ($options_places === [])
        throw new RuntimeException("Nombre de places dans les options inconnu");

    $etudiants_tmp = array_filter(DB\User::getAll(), function($u) use ($parcours) {
        return $u->role === "etudiant" and $u->parcours === $parcours;
    });
    usort($etudiants_tmp, function($a, $b) {
        return $b->moyenne <=> $a->moyenne ?: $b->ects <=> $a->ects;
    });

    $etudiants = [];
    foreach ($etudiants_tmp as $e)
        $etudiants[$e->id] = $e->wishes;

    $etudiants_final_option = [];
    while (count($etudiants) > 0) {
        foreach ($etudiants as $etudiant => &$wishes) {
            $wish = array_shift($wishes);
            if ($wish === null)
                throw new RuntimeException("L'étudiant $etudiant ne peut pas avoir d'option attribuée");

            if ($options_places[$wish] > 0) {
                $options_places[$wish]--;
                $etudiants_final_option[$etudiant] = $wish;
                unset($etudiants[$etudiant]);
            }
        }
        unset($wishes);
    }

    foreach ($options as $o)
        $o->{"etudiants$parcours"} = [];

    foreach ($etudiants_final_option as $user_id => $option) {
        $u = new DB\User($user_id);
        $u->option = $option;

        $o = new DB\Option($option);
        $ets = $o->{"etudiants$parcours"};
        $ets[] = $u;
        $o->{"etudiants$parcours"} = $ets;
    }
}

header("Location: etudiants.php");
?>
