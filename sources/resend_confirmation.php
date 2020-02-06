<?php
session_start();
if($_SESSION["logged"] ?? false) {
  header("Location:index.php");
  exit();
}
require_once ("config.inc.php");

$error = "";
$error_class = "warning";
$regenerated = false;
$is_posted = $_POST["is_posted"] ?? false;
$email = $_POST["email"] ?? null;
if($is_posted !== false) {
  $stmt = $pdo->prepare("SELECT * from `user` WHERE `email` = ?");
  $stmt->execute([
    $email,
  ]);
  $user = $stmt->fetch(PDO::FETCH_OBJ);
  if(empty($user)) {
    $error = "No email recorded in the database";
  }
  else {
    if((bool) $user->verified) {
      $error = "This email has already been verified";
      $error_class = "info";
    }
    else {
      $regenerated = true;
      //New token
      $token = str_replace(["+", "/", "="], "", base64_encode(random_bytes(20)));
      $stmt = $pdo->prepare("UPDATE `user` SET `token` = ? WHERE `id` = ?");
      $stmt->execute([
        $token,
        $user->id,
      ]);
      //Validation email
      $validate_url = "https://your-super-website.com/complete_registration.php?email={$email}&token={$token}"; //Validation url that users have to click
      $email_content =
"<p>Hello {$user->firstname} {$user->lastname}</p>,
<p>You requested a new email verification link, please click on the link below, or copy and paste it on your browser navigation bar :
  <br><a href='{$validate_url}' title='Validate your registration on YourSuperWebsite'>{$validate_url}</a>
</p>
<p>Best regards,
  <br>YourSuperWebsite Team
</p>";
      $email_sent = mail(
        $email,
        "YourSuperWebsite registration : your new email verification link",
        $email_content,
        [ //Email headers
          "From" => "no-reply@your-super-website.com",
          "X-Mailer" => "PHP/".phpversion(),
          "Content-type" => "text/html; charset=UTF-8",
        ]
      );
    }
  }
}

require_once ("header.inc.php");
?>
<?php
if(!$regenerated) {
  if($error !== "") {
    ?>
<p class="alert alert-<?php echo $error_class; ?>"><?php echo $error; ?></p>
    <?php
  }
  ?>
<form class="form form-inline" method="POST">
  <input type="hidden" name="is_posted" value="true">
  <div class="input-group mr-2">
    <label for="email" class="mr-2">Email:</label>
    <input type="email" name="email" id="email" placeholder="Email" value="<?php echo $email; ?>" class="form-control" required>
  </div>
  <button type="submit" class="btn btn-primary">Send new code</button>
  <?php
}
else {
  ?>
<p class="alert alert-info">
  <?php if($email_sent) {
    ?>
    A new link has been sent to your email adress.
    <?php
  }
  else {
    ?>
    Unfortunately, the email could not be sent, please contact the administration team.
    <?php
  }
  ?>
</p>
  <?php
}
 ?>
<?php
require_once ("footer.inc.php");
