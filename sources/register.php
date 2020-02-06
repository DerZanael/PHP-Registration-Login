<?php
session_start();
if($_SESSION["logged"] ?? false) {
  header("Location:index.php");
  exit();
}
require_once("config.inc.php");

$error = ""; //Error message
$is_posted = $_POST["is_posted"] ?? false; //Checks if the form has been posted
//Default values for POST
$email = $_POST["email"] ?? null;
$password1 = $_POST["password1"] ?? null;
$password2 = $_POST["password2"] ?? null;
$firstname = $_POST["firstname"] ?? null;
$lastname = $_POST["lastname"] ?? null;

if($is_posted) {
  //Get the POST data
  $checks = ["email", "password1", "password2", "firstname", "lastname"];
  $fill_fields = false;
  foreach($checks as $variable) {
    if(empty($$variable)) {
      $error = "Please fill all required fields";
    }
  }
  if(!empty($password1) && $password1 !== $password2) {
    $error = "The passwords do not match";
  }
  if($error === "") {
    $stmt = $pdo->prepare("SELECT COUNT(`id`) FROM `user` WHERE `email` = ?");
    $stmt->execute([$email]);
    $test = $stmt->fetchColumn();
    if($test > 0) {
      $error = "This email already exists in the database";
    }
    else {
      //Hash the password
      $password = password_hash($password1, PASSWORD_DEFAULT); //You can use PASSWORD_ARGON2I if available (as of 2020) or the current algo of the month
      //Create a token to validate the user
      $token = str_replace(["+", "/", "="], "", base64_encode(random_bytes(20)));
      $time = new \Datetime("now"); //Time object for registration date
      //DB insert
      try {
        $stmt = $pdo->prepare("INSERT INTO user (`email`, `password`, `firstname`, `lastname`, `verified`, `token`, `registration_date`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
          $email,
          $password,
          $firstname,
          $lastname,
          false,
          $token,
          $time->format("Y-m-d H:i:s"),
        ]);
      }
      catch(PDOException $e) {
        print_r("<pre>Failed to execute user insert :
          ".$e->getMessage()."</pre>");
        die();
      }
      //Validation email
      $validate_url = "https://your-super-website.com/complete_registration.php?email={$email}&token={$token}"; //Validation url that users have to click
      $email_content =
"<p>Hello {$firstname} {$lastname}</p>,
<p>You registered on YourSuperWebsite on {$time->format("d/m/Y H:i")}.</p>
<p>In order to complete your registration, please click on the link below, or copy and paste it on your browser navigation bar :
  <br><a href='{$validate_url}' title='Validate your registration on YourSuperWebsite'>{$validate_url}</a>
</p>
<p>Best regards,
  <br>YourSuperWebsite Team
</p>";
      $email_sent = mail(
        $email,
        "YourSuperWebsite registration : please check your email",
        $email_content,
        [ //Email headers
          "From" => "no-reply@your-super-website.com",
          "X-Mailer" => "PHP/".phpversion(),
          "Content-type" => "text/html; charset=UTF-8",
        ]
      );
      if($email_sent) {
        $_SESSION["email_error"] = true;
      }
      $_SESSION["register_complete"] = true;
      header("Location:register.php");
      exit();
    }
  }
}

require_once ("header.inc.php");
?>
<?php if($_SESSION["register_complete"] ?? false) {
  ?>
  <p class="alert alert-info">Your registration was successful !
    <br>
    <?php
    if($_SESSION["email_error"] ?? false) {
      ?>
       Unfortunately, the confirmation email could not be sent, please contact an administrator, or try sending <a href='resend_confirmation.php' title='Send a new confirmation email'>a new confirmation email</a>.
      <?php
    }
    else {
      ?>
      A confirmation message has been sent to your email address, please follow the link enclosed to complete your registration and be able to log in YourSuperWebsite.
      <?php
    }
    ?>
  <?php
  unset($_SESSION["register_complete"]);
  unset($_SESSION["email_error"]);
}
?>
<?php if($is_posted && $error !== "") {
  ?>
  <p class="alert alert-warning"><?php echo $error; ?></p>
  <?php
}
?>
<div class="row">
  <div class="col-12 col-md-6 col-lg-4">
    <form class="form" method="POST" id="registration_form">
      <input type="hidden" name="is_posted" value="true">
      <div class="form-group">
        <label for="firstname" class="col-form-label required">First name:</label>
        <input type="text" name="firstname" id="firstname" value="<?php echo $firstname; ?>" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="firstname" class="col-form-label required">Last name:</label>
        <input type="text" name="lastname" id="lastname" value="<?php echo $lastname; ?>" class="form-control" required>
      </div>
      <div class="form-group">
        <label for="email" class="col-form-label required">Email:</label>
        <input type="email" name="email" id="email" value="<?php echo $email; ?>" class="form-control" required>
        <p class="form-text small text-muted">Your email will be used to log in</p>
      </div>
      <div class="form-group">
        <label for="password1" class="col-form-label required">Password:</label>
        <input type="password" name="password1" id="password1" class="form-control" required>
        <input type="password" name="password2" id="password2" class="form-control mt-2" required>
        <p id="password_message" class="form-text small text-danger" style="display:none;">The passwords do not match</p>
      </div>
      <button type="submit" class="btn btn-primary">Register</button>
      <p class="mt-3 small text-muted">Already a member ? <a href="login.php" title="Login page">Go to the login page</a></p>
    </form>
  </div>
</div>
<script language="javascript">
//Client-side password match
const pass1 = document.getElementById("password1");
const pass2 = document.getElementById("password2");
const pass_msg = document.getElementById("password_message");
//On page load
document.addEventListener("DOMContentLoaded", function(){
  document.getElementById("registration_form").addEventListener("submit", function(evt){ //On form submit
    pass_msg.style.display = "none";
    if(!checkPwd()) { //Passwords do not match, we stop the process
      evt.preventDefault();
    }
  });
  pass1.addEventListener("keyup", function() { checkPwd(); }); //check passwords on type
  pass2.addEventListener("keyup", function() { checkPwd(); }); //check passwords on type
});
/**
 * Check if password1 and password2 match
 * and display the error message
 * @return bool true = ok ;)
 */
function checkPwd() {
  let pwd_match = (pass1.value != "" && pass1.value === pass2.value);
  pass_msg.style.display = (pwd_match) ? "none" : "block";

  return pwd_match;
}
</script>
<?php
require_once ("footer.inc.php");
