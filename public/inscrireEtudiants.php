<?php
if (!isset($_SESSION["id"])) {
    header("Location: connexion.php");
    exit;
}

if ($_SESSION["role"] !== "admin") {
    http_response_code(401);
    exit;
}

require_once "../lib.php";

function random_password(int $length = 8): string {
    $keyspace = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $keyspace_len = strlen($keyspace) - 1;
    $password = "";
    for ($i = 0; $i < $length; $i++)
        $password .= $keyspace[mt_rand(0, $keyspace_len)];
    return $password;
}

try {
    $path = $_FILES["file"]["tmp_name"];
    if (!is_uploaded_file($path))
        throw new RuntimeException("Upload invalide");

    $fp = fopen($path, "r");
    $header = fgetcsv($fp, 0, ";");
    $nb_wishes = count($header) - 5;
    $parcours = [
        8 => "GSI",
        6 => "MI",
        2 => "MF",
    ][$nb_wishes];
    if (!$parcours)
        throw new RuntimeException("Mauvais nombre de colonnes");

    if (array_slice($header, 0, 5) !== ["prenom", "nom", "login", "ECTS acquis", "Moyenne"])
        throw new RuntimeException("Mauvais nom de colonne");

    for ($i = 0; $i < $nb_wishes; $i++) {
        if ($header[$i + 5] !== "Choix " . ($i + 1))
            throw new RuntimeException("Mauvais nom de colonne");
    }

    try {
        while (($e = fgetcsv($fp, 0, ";")) !== false) {
            $ects = filter_var($e[3], FILTER_VALIDATE_INT, [
                "options" => ["min_range" => 0],
            ]);
            if ($ects === false)
                throw new RuntimeException("Nombre d'ECTS acquis invalide");
            $moyenne = filter_var($e[4], FILTER_VALIDATE_FLOAT, [
                "options" => [
                    "decimal" => ",",
                    "min_range" => 0,
                    "max_range" => 20,
                ],
            ]);
            if ($moyenne === false)
                throw new RuntimeException("Moyenne invalide");

            $user = DB\User::create(trim($e[2]));
            $user->prenom = trim($e[0]);
            $user->nom = trim($e[1]);
            $user->default_password = random_password();
            $user->role = "etudiant";
            $user->parcours = $parcours;
            $user->ects = $ects;
            $user->moyenne = $moyenne;
            $user->wishes = array_map(function($str) {
                return strtoupper(trim($str));
            }, array_slice($e, 5, $nb_wishes));

            $msg = DB\Message::create();
            $msg->sender = $_SESSION["id"];
            $msg->reciever = $user;
            $msg->subject = "Important !";
            $msg->body = "
            <a href=\"https://youtube.com/watch?v=dQw4w9WgXcQ\">Va vite voir cette vidéo !</a>
            ";
        }
    } catch (Exception $e) {
        if (isset($user))
            $user->remove();
        throw $e;
    }

    header("Location: etudiants.php");
} catch (Exception $e) {
    http_response_code(400);
    echo $e->getMessage();
} finally {
    if (isset($fp))
        fclose($fp);
}
?>
