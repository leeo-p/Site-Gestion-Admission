<?php
if (!isset($_SESSION["id"])) {
    header("Location: connexion.php?redirect=profil.php");
    exit;
}

require_once "../lib.php";

$user = new DB\User();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $fns = [];

        if (isset($_FILES["pp"]) and $_FILES["pp"]["error"] !== UPLOAD_ERR_NO_FILE) {
            $f = $_FILES["pp"];
            if ($f["error"] !== UPLOAD_ERR_OK)
                throw new RuntimeException("Erreur d'upload de la photo de profil : " . [
                    UPLOAD_ERR_INI_SIZE => "fichier trop volumineux",
                    UPLOAD_ERR_FORM_SIZE => "fichier trop volumineux",
                    UPLOAD_ERR_PARTIAL => "fichier incomplet",
                    UPLOAD_ERR_NO_FILE => "fichier inexistant",
                    UPLOAD_ERR_NO_TMP_DIR => "pas de dossier temporaire",
                    UPLOAD_ERR_CANT_WRITE => "impossible d'écrire sur le disque",
                    UPLOAD_ERR_EXTENSION => "erreur d'extension",
                ][$f["error"]]);

            $path = $f["tmp_name"];
            if (!is_uploaded_file($path))
                throw new RuntimeException("Upload invalide");

            $img = file_get_contents($path);
            if ($img === false)
                throw new RuntimeException("Impossible de lire le fichier");

            $size = getimagesizefromstring($img);
            if ($size === false)
                throw new RuntimeException("L'image est invalide");

            if ($size[0] > 512 or $size[1] > 512)
                throw new RuntimeException("L'image est trop grande (512x512 max)");

            $fns[] = function($user) use ($img) {
                $user->profile_picture = $img;
            };
        }

        if (isset($_POST["birthdate"]) and $_POST["birthdate"] !== "") {
            $birthdate = DateTime::createFromFormat("Y-m-d", $_POST["birthdate"]);
            if ($birthdate === false)
                throw new RuntimeException("Date invalide");

            $fns[] = function($user) use ($birthdate) {
                $user->birthdate = $birthdate;
            };
        }

        if (isset($_POST["address"]) and $_POST["birthdate"] !== "") {
            if (strlen($_POST["address"]) > 127)
                throw new RuntimeException("Adresse postale trop longue");

            $fns[] = function($user) {
                $user->address = $_POST["address"];
            };
        }

        if (isset($_POST["tel"]) and $_POST["tel"] !== "") {
            if (strlen($_POST["address"]) > 127)
                throw new RuntimeException("Numéro de téléphone trop long");

            $fns[] = function($user) {
                $user->tel = $_POST["tel"];
            };
        }

        if (isset($_POST["password"]) and $_POST["password"] !== "") {
            if (strlen($_POST["password"]) < 8)
                throw new RuntimeException("Mot de passe trop court");
            if (strlen($_POST["password"]) > 72)
                throw new RuntimeException("Mot de passe trop long");
            if (!isset($_POST["password_confirmation"]))
                throw new RuntimeException("Veuillez confirmer le mot de passe");
            if ($_POST["password"] !== $_POST["password_confirmation"])
                throw new RuntimeException("Les mots de passe ne correspondent pas");

            $hash = password_hash($_POST["password"], PASSWORD_BCRYPT);
            $fns[] = function($user) use ($hash) {
                $user->password = $hash;
            };
        }

        foreach ($fns as $fn) {
            $fn($user);
        }

        $message = "Profil mis à jour";
    } catch (RuntimeException $e) {
        $erreur = $e->getMessage();
    }
}

$title = "Profil";
include "../templates/top.php";
?>
<div class="login-box2">

  <?php if (isset($erreur)): ?>
    <div class="erreur">
      <?= $erreur ?>
    </div>
      <?php elseif (isset($message)): ?>
    <div class="info">
    <div class="maj">
      <?= $message ?>
    </div>
    </div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <h2>PROFIL</h2>
    <div class="photo">
      <?php if (isset($user->profile_picture)) {
          $img = $user->profile_picture;
          $size = getimagesizefromstring($img);
          $b64 = base64_encode($img); ?>
        <img class="imageProfil" src="<?= "data:$size[mime];base64,$b64" ?>">
      <?php } ?>
    </div>
    <div class="user-box">
      <input type="hidden" name="MAX_FILE_SIZE" value="8000000">
      <input type="file" name="pp" id="pp" accept="image/*">
      <label for="pp">Photo de profil : </label>
    </div>
    <div class="user-box">
      <input type="date" id="birthdate" name="birthdate"
             <?php if (isset($user->birthdate)): ?>
             value="<?= htmlspecialchars($user->birthdate->format("Y-m-d")) ?>"
             <?php endif; ?>>
      <label for="birthdate">Date de naissance : </label>
    </div>
    <div class="user-box">
      <input id="address" name="address"
             <?php if (isset($user->address)): ?>
             value="<?= htmlspecialchars($user->address)?>"
             <?php endif; ?>>
      <label for="address">Adresse postale : </label>
    </div>
    <div class="user-box">
      <input type="tel" id="tel" name="tel"
             <?php if (isset($user->tel)): ?>
             value="<?= htmlspecialchars($user->tel)?>"
             <?php endif; ?>>
      <label for="tel">Numéro de téléphone : </label>
    </div>
    <div class="user-box">
      <input type="password" id="password" name="password">
      <label for="password">Mot de passe : </label>
    </div>
    <div class="user-box">
      <input type="password" id="password_confirmation" name="password_confirmation">
      <label for="passworm_confirmation">Confirmer le mot de passe : </label>
    </div>
    <input class="submit" type="submit" value="Sauvegarder les modifications">
  </form>
</div>

<?php include "../templates/bottom.php"; ?>
