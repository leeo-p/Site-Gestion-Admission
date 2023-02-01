<?php
if (!isset($_SESSION["id"])) {
    header("Location: connexion.php?redirect=/");
    exit;
}

require_once "../lib.php";

$user = new DB\User();

$title = "Accueil";
include "../templates/top.php";
?>
<div class="index-contenu">
  <p class="bienvenue">Bonjour <?= $user->prenom . " " . $user->nom ?> !</p>
  <div class="infosProfil">
    <?php if (isset($user->profile_picture)) :
      $img = $user->profile_picture;
      $size = getimagesizefromstring($img);
      $b64 = base64_encode($img); ?>
      <img class="imageProfil" src="<?= "data:$size[mime];base64,$b64" ?>">
      <h6>Photo de Profil</h6>
    <?php else: ?>
      <img class="imageProfil"src="ressources/default.jpg" alt="photodeprofil">
      <h6>Photo de Profil <i>(par défaut)</i></h6>
    <?php endif; ?>

    <p> Prénom : <?= $user->prenom ?></p>
    <p>Nom : <?= $user->nom ?></p>
    <p>Date de Naissance : <?php if (isset($user->birthdate)) { ?>
      <?= $user->birthdate->format("d-m-Y") ?> <?php } ?> </p>
    <br>
  </div>
  <div class="presentation">
    Bienvenue sur la plateforme de choix des options.<br>
    Il vous est possible de communiquer entre vous mais aussi avec le
    responsable admission, grâce à la Messagerie.<br>
    Vous pouvez également, dans Messagerie, vérifer votre admission.<br>
    N'oubliez pas de vérifier vos informations et les modifier dans l'espace
    Profil si besoin.<br>
    Enfin vous pouvez accéder aux réseaux sociaux de l'école pour suivre
    l'actualité.<br>
  </div>
</div>

<footer class="mainfooter" role="contentinfo">
  <div class="footer-middle">
    <div class="colonne">
      <h4>Adresse</h4>
      <address>
        <ul>
          <li>
            2 Boulevard Lucien Favre<br> 64075 Pau Cedex<br>
          </li>
          <li>
            Phone: +33 5 590 590 90
          </li>
          <li>
            <a id="lien" href="mailto:administration-pau@cy-tech.fr"> Email: administration-pau@cy-tech.fr </a>
          </li>
        </ul>
      </address>
    </div>
    <img class="banniere" src="ressources/cy-tech1.png">
    <div class="colonne">
      <span class="reseaux_sociaux_libelle">L'ACTU C'EST PAR ICI</span>
      <a target="_blanck" href="https://www.facebook.com/CYCergyParisUniversite/" title="Accéder à facebook" ><img class="logo" src="ressources/facebook.png"></a></li>
          <a target="_blanck" href="https://www.twitter.com/UniversiteCergy" title="Accéder à Twitter"><img class="logo" src="ressources/twitter.png"</a></li>
          <a target="_blanck" href="https://www.linkedin.com/school/cycergyparisuniversit%C3%A9" title="Accéder à Linkedin"><img class="logo" src="ressources/linkedin.png"</a></li>
          <a target="_blanck" href="https://www.instagram.com/cy_univ/" title="Accéder à Instagram"><img class="logo" src="ressources/instagram.png"></a></li>
    </div>
    <div class="footer-bottom">
      <p class="text">&copy; Copyright 2022 - City of PAU. Jérémi Lioger-Bun - Léo Portet - Lucas Ransan - Laura Sabadie.</p>
    </div>
  </div>
</div>
</footer>

<?php include "../templates/bottom.php"; ?>
