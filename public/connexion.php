<?php
if (isset($_SESSION["id"])) {
    header("Location: /");
    exit;
}

require_once "../lib.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $user = new DB\User($_POST["login"]);
        if ($user->password !== null) {
            $match = password_verify($_POST["password"], $user->password);
        } else {
            $match = $_POST["password"] === $user->default_password;
        }
        if ($match) {
            $_SESSION["id"] = $user->id;
            $_SESSION["role"] = $user->role;
            header("Location: " . ($_GET["redirect"] ?? "/"));
            exit;
        } else {
            $erreur = "password";
        }
    } catch (DB\ExistenceException $e) {
        $erreur = "login";
    }
}

$title = "Connexion";
?>
<div class="login-box">
  <!DOCTYPE html>
  <html>
    <header>
      <meta charset="utf-8">
      <link rel="stylesheet" href="style.css">
      <title><?= $title ?? "Titre par défaut" ?></title>
    </header>
    <body>

  <h2>CONNEXION</h2>

  <?php if (isset($erreur)): ?>
    <div class="erreur">
      <?php switch ($erreur): case "login": ?>
      Login inconnu
      <?php break; case "password": ?>
      Mot de passe invalide
      <?php break; default: ?>
      Erreur inconnue
      <?php endswitch; ?>
    </div>
  <?php endif; ?>

  <form method="POST">
    <div class="user-box">
      <input id="login" name="login" required>
      <label for="login">Identifiant : </label>
    </div>
    <div class="user-box">
      <input type="password" id="password" name="password" required>
      <label for="password">Mot de Passe : </label>
    </div>

    <input class="submit" type="submit" value="Valider">

  </form>
</div>

</body>
</html>
