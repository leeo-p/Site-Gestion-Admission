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

try {
    $path = $_FILES["file"]["tmp_name"];
    if (!is_uploaded_file($path))
        throw new RuntimeException("Upload invalide");

    $fp = fopen($path, "r");
    if (fgetcsv($fp, 0, ";") !== ["option", "GSI", "MI", "MF"])
        throw new RuntimeException("Mauvais nom de colonne");

    try {
        while (($e = fgetcsv($fp, 0, ";")) !== false) {
            $places = filter_var_array(array_slice($e, 1), FILTER_VALIDATE_INT);
            if ($places === false)
                throw new RuntimeException("Nombre de places invalide");

            $option = DB\Option::create(strtoupper(trim($e[0])));
            $option->placesGSI = $places[0];
            $option->placesMI = $places[1];
            $option->placesMF = $places[2];
        }
    } catch (Exception $e) {
        if (isset($option))
            $option->remove();
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

<?php include "../templates/bottom.php"; ?>
