<?php
ob_start();
?>
<!DOCTYPE html>
<html>
  <header>
    <meta charset="utf-8">
    <link rel="stylesheet" href="style.css">
    <title><?= $title ?? "Titre par défaut" ?></title>
  </header>
  <body>
    <div class="menu">
      <ul class="menu-items">
        <li class="items"><a class="links" href="index.php">Accueil</a></li>
        <?php if ((new DB\User())->role !== "etudiant"): ?>
          <li class="items"><a class="links" href="etudiants.php">Etudiants</a></li>
        <?php endif; ?>
        <li class="items"><a class="links" href="profil.php">Profil</a></li>
        <li class="items"><a class="links" href="messagerie.php">Messagerie</a></li>
        <li class="items"><a class="links" href="deconnexion.php">Deconnexion</a></li>
      </ul>
    </div>
