<?php
if (!isset($_SESSION["id"])) {
    header("Location: connexion.php?redirect=etudiants.php");
    exit;
}

if (!in_array($_SESSION["role"], ["responsable", "admin"])) {
    http_response_code(401);
    exit;
}

require_once "../lib.php";

$title = "Étudiants";
include "../templates/top.php";
?>


<?php if ($_SESSION["role"] === "admin"): ?>
  <h2>Inscrire des étudiants</h2>

  <form class="insc" enctype="multipart/form-data" action="inscrireEtudiants.php" method="POST">
    <input type="file" name="file">
    <input type="submit" value="Envoyer">
  </form>

  <h2>Étudiants inscrits</h2>
  <div class="tableau">
    <table>
      <tr>
        <?php foreach ([
            "Prénom", "Nom", "Login", "Mot de passe par défaut",
            "Parcours", "ECTS acquis", "Moyenne", "Vœux", "Option",
            "Date de naissance", "Adresse postale", "Numéro de téléphone",
        ] as $h): ?>
          <th>
            <?= $h ?>
          </th>
        <?php endforeach; ?>
      </tr>
      <?php
      $etudiants = DB\User::getAll();
      foreach ($etudiants as $e):
          if ($e->role !== "etudiant")
              continue;
      ?>
        <tr class="etudiant-ligne">
          <?php foreach ([
              $e->prenom, $e->nom, $e->id, $e->default_password,
              $e->parcours, $e->ects, $e->moyenne,
              $e->wishes ? implode(", ", $e->wishes) : null, $e->option,
              $e->birthdate ? $e->birthdate->format("Y-m-d") : null, $e->address, $e->tel
          ] as $v): ?>
            <td><?= $v ?></td>
          <?php endforeach; ?>
        </tr>
      <?php endforeach; ?>
    </table>
  </div>

<?php elseif ($_SESSION["role"] === "responsable"): ?>
  <?php if (isset($_POST["action"])) {
      switch ($_POST["action"]) {
          case "modifier":
              foreach (DB\Option::getAll() as $o) {
                  $o->etudiantsGSI = [];
                  $o->etudiantsMI = [];
                  $o->etudiantsMF = [];
              }

              foreach ($_POST["option"] as $uid => $oid) {
                  $u = new DB\User(base64url_decode($uid));
                  $u->option = $oid;
                  $o = new DB\Option($oid);
                  $ets = $o->{"etudiants$u->parcours"};
                  $ets[] = $u;
                  $o->{"etudiants$u->parcours"} = $ets;
              }
              # breakthrough
          case "valider":
              foreach (DB\User::getAll() as $u) {
                  if ($u->role !== "etudiant")
                      continue;

                  $msg = DB\Message::create();
                  $msg->sender = $_SESSION["id"];
                  $msg->reciever = $u;
                  $msg->subject = "Accepté dans une option";
                  $msg->body = "Vous êtes accepté·e dans l'option $u->option.";
              }
      }

      header("Location: etudiants.php");
      exit;
  } else { ?>
    <h2>Renseigner le nombre de places pour les options</h2>

    <form class="insc" enctype="multipart/form-data" action="renseignerPlaces.php" method="POST">
      <input type="file" name="file">
      <input type="submit" value="Envoyer">
    </form>

    <?php
    $options = DB\Option::getAll();
    if ($options !== []):
    ?>
      <h2>Statistiques des options</h2>
        <?php foreach (["GSI", "MI", "MF"] as $parcours): ?>
      <div class="tab-options">
        <h3><?= $parcours ?></h3>
          <table class="options">
            <tr>
              <th>Option</th>
              <th>Places</th>
              <th>Étudiants</th>
              <th>Moyenne des moyennes</th>
              <th>Moyenne du dernier admis</th>
            </tr>
            <?php
            foreach ($options as $option) {
                $etudiants = $option->{"etudiants$parcours"};
                $nb_etudiants = count($etudiants);
            ?>
            <tr>
              <td><?= $option->id ?></td>
              <td><?= $option->{"places$parcours"} ?></td>
              <td><?= $nb_etudiants ?></td>
              <td>
                <?= $nb_etudiants ? number_format(
                    array_reduce($etudiants, function($c, $e) {
                        return $c + $e->moyenne;
                    }, 0) / $nb_etudiants,
                    4, ",", ""
                ) : "" ?>
              </td>
              <td>
                <?= $nb_etudiants ? number_format(
                    min(array_map(function($e) {
                        return $e->moyenne;
                    }, $etudiants)),
                    3, ",", ""
                ) : "" ?>
              </td>
            </tr>
            <?php } ?>
          </table>
        </div>
        <?php endforeach; ?>

    <h2 id="algoM"><a href="repartirEtudiants.php">Lancer l'algorithme de mariage stable</a></h2>
    <?php endif; ?>
    <form action="etudiants.php" method="POST">
      <div class="tableau2">
        <table>
          <tr>
            <?php foreach ([
                "Prénom", "Nom", "Login",
                "Parcours", "ECTS acquis", "Moyenne", "Vœux", "Option",
            ] as $h): ?>
              <th>
                <?= $h ?>
              </th>
            <?php endforeach; ?>
          </tr>
          <?php
          $etudiants = DB\User::getAll();
          foreach ($etudiants as $e):
              if ($e->role !== "etudiant")
                  continue;
          ?>
            <tr class="etudiant-ligne">
              <?php foreach ([
                  $e->prenom, $e->nom, $e->id,
                  $e->parcours, $e->ects, $e->moyenne,
                  $e->wishes ? implode(", ", $e->wishes) : null,
              ] as $v): ?>
                <td>
                  <?= $v ?>
                </td>
              <?php endforeach; ?>
              <td>
                <select name="option[<?= base64url_encode($e->id) ?>]">
                  <?php foreach ($options as $o): ?>
                    <option value="<?= $o->id ?>" <?= $e->option === $o->id ? "selected" : "" ?>>
                      <?= $o->id ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
      <div class="buttons">
        <button type="submit" name="action" value="modifier">Modifier</button>
        <button type="submit" name="action" value="valider">Valider</button>
      </div>
    </form>
  <?php } ?>
<?php endif; ?>

<?php include "../templates/bottom.php"; ?>
