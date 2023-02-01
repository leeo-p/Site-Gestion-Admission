<?php
if (!isset($_SESSION["id"])) {
    header("Location: connexion.php?redirect=messagerie.php");
    exit;
}

require_once "../lib.php";

$title = "Messagerie";
include "../templates/top.php";
?>


<?php if (isset($_GET["action"]) and $_GET["action"] === "lire") { ?>
  <?php if (!isset($_GET["id"])): ?>
    <p>
      Id de message manquant
    </p>
  <?php else: ?>
    <?php
    try {
        $user = new DB\User();
        $message = new DB\Message(base64url_decode($_GET["id"]));
        $sender = $message->sender;
        $reciever = $message->reciever;
        if ($reciever->id !== $user->id and $sender->id !== $user->id and $user->role !== "admin")
            throw new RuntimeException("Access non authorisé");

        $date_fmt = new IntlDateFormatter("fr", IntlDateFormatter::FULL, IntlDateFormatter::SHORT);
        if ($reciever->id === $user->id)
            $message->read = true;
    ?>
    <div id="message">
      <a id="date">
        <?= $date_fmt->format($message->timestamp) ?>
      </a>
      <?php if ($reciever->id === $user->id): ?>
        <a id="signaler" href="messagerie.php?action=signaler&id=<?= $_GET["id"] ?>">Signaler le message</a>
        <a id="bloquer" href="messagerie.php?action=bloquer&id=<?= base64url_encode($sender->id) ?>">Bloquer <?php $sender->id ?></a>
      <?php endif; ?>
      <a id="exit" href="messagerie.php" id="exit">&#10006;</a>
      <p id="sender">
        De <?= htmlspecialchars("$sender->prenom $sender->nom <$sender->id>") ?>
      </p>
      <p id="destinataire">
      Pour <?= htmlspecialchars("$reciever->prenom $reciever->nom <$reciever->id>") ?>
      </p>
      <p id="object">
        <?= htmlspecialchars($message->subject) ?>
      </p>

      <div id="cekiladit">
        <?= $message->body ?>
      </div>
    <?php } catch (RuntimeException $e) { ?>
      <p id="">
        <?= $e->getMessage() ?>
      </p>
    </div>
    <?php } ?>
  <?php endif; ?>

<?php } else if (isset($_GET["action"]) and $_GET["action"] === "ecrire") { ?>
  <datalist id="user-logins">
    <?php
    $users = DB\User::getAll();
    foreach ($users as $user):
    ?>
      <option value="<?= htmlspecialchars($user->id) ?>">
    <?php endforeach; ?>
  </datalist>

  <?php if (isset($_GET["erreur"]) and $_GET["erreur"] === "destinataire"): ?>
    <div class="erreur">
      Ce destinataire n'existe pas.
    </div>
  <?php endif; ?>

  <form action="messagerie.php" method="POST" id="msg">
    <a id="tete" href="messagerie.php" id="exit">&#10006;</a>
    <input id="reciever" name="reciever" list="user-logins" placeholder="À" required>
    <input id="subject" name="subject" placeholder="Sujet" required>
    <textarea id="body" name="body" required></textarea>
    <button id="submit" type="submit" name="action" value="envoyer">Envoyer</button>
  </form>
  <!-- UwU -->
<?php } else if (isset($_GET["action"]) and $_GET["action"] === "signaler" and isset($_GET["id"])) {
    $msg = new DB\Message(base64url_decode($_GET["id"]));
?>
  <h2>Signalement du message envoyé par <?= $msg->sender->id ?></h2>
  <form action="messagerie.php" method="POST" id="msg">
    <a id="tete" href="messagerie.php" id="exit">&#10006;</a>
    <input type="hidden" name="msg" value="<?= $_GET["id"] ?>">
    <textarea id="body" name="body" required></textarea>
    <button id="submit" type="submit" name="action" value="signaler">Signaler</button>
  </form>
<?php } else if (isset($_POST["action"]) and $_POST["action"] === "envoyer") {
    try {
        $msg = DB\Message::create();
        $msg->sender = $_SESSION["id"];
        $msg->reciever = $_POST["reciever"];
        $msg->subject = $_POST["subject"];
        $msg->body = $_POST["body"];
        header("Location: messagerie.php?info=envoye");
    } catch (DB\ExistenceException $e) {
        $msg->remove();
        header("Location: messagerie.php?action=ecrire&erreur=destinataire");
    }
    exit;
} else if (isset($_POST["action"]) and $_POST["action"] === "signaler") {
    $msg = DB\Message::create();
    $msg->sender = $_SESSION["id"];
    $msg->reciever = "admin";
    $msg->subject = "Signalement";
    $msg->body = "
    <a href=\"messagerie.php?action=lire&id=$_POST[msg]\">Lien vers le message</a>
    <p>Détail du signalement :</p>
    <p>$_POST[body]</p>
    ";
    header("Location: messagerie.php?info=signale");
} else if (isset($_GET["action"]) and $_GET["action"] === "bloquer") {
    $user = new DB\User();
    $blocked = $user->blocked;
    $blocked[] = base64url_decode($_GET["id"]);
    $user->blocked = $blocked;
    header("Location: messagerie.php?info=bloque");
} else if (isset($_POST["action"])) {
    $uid = $_SESSION["id"];
    foreach ($_POST["messages"] as $msg) {
        $message = new DB\Message(base64url_decode($msg));
        if ($uid !== $message->reciever->id)
            continue;
        switch ($_POST["action"]) {
            case "supprimer":
                $message->deleted = true;
                break;
            case "lu":
                $message->read = true;
                break;
            case "nonlu":
                $message->read = false;
                break;
        }
    }

    header("Location: messagerie.php");
    exit;
} else { ?>
  <p>
    <a id="newMsg" href="messagerie.php?action=ecrire">&#x1F58B;&#xFE0F; Écrire un message</a>
  </p>

  <?php if (isset($_GET["info"])): ?>
    <div class="info">
      <a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">
        <?= [
            "envoye" => "> Message envoyé !",
            "signale" => "> Message signalé !",
            "bloque" => "> Utilisateur bloqué !",
        ][$_GET["info"]] ?>
      </a>
    </div>
  <?php endif; ?>

  <form action="messagerie.php" method="POST">
    <div id="editbuttons">
      <button class="mailBoxButtons" type="submit" name="action" value="supprimer" alt="Supprimer">&#128465;&#65039;</button>
      <button class="mailBoxButtons" type="submit" name="action" value="lu">Marquer comme lu</button>
      <button class="mailBoxButtons" type="submit" name="action" value="nonlu">Marquer comme non lu</button>
    </div>
    <div class="mailBox">
      <table>
        <tr id="headMsg">
          <th></th>
          <th>Expéditeur</th>
          <th>Destinataire</th>
          <th>Sujet</th>
          <th>Date</th>
        </tr>
        <?php
        $user = new DB\User();
        $date_fmt = new IntlDateFormatter("fr", IntlDateFormatter::MEDIUM, IntlDateFormatter::SHORT);
        $messages = array_filter(DB\Message::getAll(), function($message) use ($user) {
            return $user->id === $message->reciever->id
                || $user->id === $message->sender->id
                and !$message->deleted
                and !in_array($message->sender->id, $user->blocked ?? []);
        });
        usort($messages, function($a, $b) {
            return $b->timestamp <=> $a->timestamp;
        });

        foreach ($messages as $message) {
            $mid = base64url_encode($message->id);
        ?>

          <tr class="<?= $message->read || $user->id === $message->sender->id ? "lu" : "nonlu" ?>" class="msg">
            <td><input type="checkbox" name="messages[]=" value="<?= $mid ?>"></td>
            <td><?= $message->sender->id ?></td>
            <td><?= $message->reciever->id ?></td>
            <td>
              <a href="messagerie.php?action=lire&id=<?= $mid ?>">
                <?= htmlspecialchars($message->subject) ?>
              </a>
            </td>
            <td><?= $date_fmt->format($message->timestamp) ?></td>
          </tr>
        <?php } ?>
      </table>
    </div>
  </form>
<?php } ?>

<?php include "../templates/bottom.php"; ?>
