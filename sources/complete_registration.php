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

print $twig->render("complete_registration.html.twig", [
  "error"=>$error,
  "error_class"=>$error_class,
]);