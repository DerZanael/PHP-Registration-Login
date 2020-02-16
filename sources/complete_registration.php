<?php
session_start();
if($_SESSION["logged"] ?? false) {
  header("Location:index.php");
  exit();
}
$token = $_GET["token"] ?? null;
$email = $_GET["email"] ?? null;
if(empty($token) || empty($email)) {
  header("Location:index.php");
  exit();
}

require_once ("config.inc.php");
$error = "";
$error_class= "warning";

$stmt = $pdo->prepare("SELECT * FROM `user` WHERE (`email` = ?) LIMIT 0, 1");
$stmt->execute([
  $email,
]);
$user = $stmt->fetch(PDO::FETCH_OBJ);
if($user === null || $user === false || empty($user)) {
  $error = "No record found in the database for this email";
  $error_class = "danger";
}
else {
  if((bool) $user->verified) {
    $error = "Your email has already been verified";
    $error_class = "info";
  }
  else {
    if($user->token !== $token) {
      $error = "The token does not match, please verify the confirmation email you received or try sending a new one";
    }
    else {
      $pdo->exec("UPDATE `user` SET `verified` = 1, `token` = NULL WHERE `id` = {$user->id}");
    }
  }
}
require_once ("header.inc.php");
?>
<?php if($error !== "") {
  ?>
<p class="alert alert-<?php echo $error_class; ?>"><?php echo $error; ?></p>
<p class="small text-muted">Troubles with you account verification ? <a href="resend_confirmation.php" title="Send a new confirmation email page">Try sending a new email verification code</a>.</p>
  <?php
}
else {
  ?>
<p class="alert alert-info">Congratulations!
  <br>Your account is now active, you can log in YourSuperWebsite
  <br><a href="login.php" title="YourSuperWebsite login page" class="btn btn-info">Login page</a>
</p>
  <?php
}
?>
<?php
require_once ("footer.inc.php");
