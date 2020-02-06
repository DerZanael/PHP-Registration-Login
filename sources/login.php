<?php
session_start();
if($_SESSION["logged"] ?? false) {
  //User is already logged -> go to the index page;
  header("Location:index.php");
  exit();
}
require_once("config.inc.php");
$error_class = "danger"; //CSS class for the error message
$error = ""; //Actual message
$is_posted = $_POST["is_posted"] ?? false; //Check for the form being posted
//Default values for POST variables
$email = $_POST["email"] ?? null; //Get the email input
$password = $_POST["password"] ?? null; //Get the password input

if($is_posted !== false) { //Yay, it's been posted
  if(empty($email)  || empty($password)) { //Both fields are required but we're still going to check if they are filled
    $error = "Please fill all required fields.";
    $error_class = "danger";
  }
  else {
    //Both information are filled, we're gonna retrieve the requested user and compare the password
    $stmt = $pdo->prepare("SELECT * from `user` WHERE (`email` = ?)");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_OBJ);
    if(empty($user)) {
      $error = "No record found for this email. <a href='register.php' title='Register to YourSuperWebsite'>Please register</a>";
      $error_class = "info";
    }
    else {
      //Now we can test the inputted password against the stored encrypted password for the user
      if(!password_verify($password, $user->password)) { //Oh, bad password.
        $error = "The email and the password do not match";
        $error_class = "warning";
        //You could store that in the session and redirect to the login page to avoid people spamming your form with F5
      }
      else { //Looks good, we'll now check if the user has verified their email
        if(!(bool)$user->verified || !empty($user->token)) { //Nope they didn't.
          $error = "You haven't verified your email yet.
          Please click the verify account button in the email you received when you registered and try again";
          $error_class = "info";
        }
        else {
          $_SESSION["logged"] = true; //set the "logged" entry so we can check it to see if the user is logged or not
          /*
          For this part we're going to log some info about the user, you can alternativerly create a toker you will use to identify your user every time a page is loaded. Something like this :
          $_SESSION["token"] = "{$user->id}-{$user->email}";
          and when you need to get the user info you can do a request like this :
          "SELECT * from user WHERE MD5(CONCAT(id, '-', email)) = ?" with $_SESSION["token"] as a parameter
          */
          $_SESSION["lastname"] = $user->lastname;
          $_SESSION["firstname"] = $user->firstname;
          $_SESSION["userid"] = $user->id;
          $_SESSION["email"] = $user->email;

          header("Location:profile.php");
          exit();
        }
      }
    }
  }
}

require_once("header.inc.php");
?>
<?php
if($is_posted && $error !== "") { //There's been an error during the login process
  ?>
<p class="alert alert-<?php echo $error_class; ?>"><?php echo nl2br($error); ?></p>
  <?php
}
?>
<div class="row">
  <div class="col-12 col-md-6 col-lg-4">
    <form class="form" method="POST" action="login.php">
      <input type="hidden" name="is_posted" value="true">
      <div class="form-group">
        <label for="email" class="col-form-label required">Email :</label>
        <input type="email" name="email" id="email" class="form-control" value="<?php echo $email; ?>" required>
      </div>
      <div class="form-group">
        <label for="password" class="col-form-label required">Password :</label>
        <input type="password" name="password" email="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary">Login</button>
      <p class="small text-muted mt-3">
        New user ? <a href="register.php" title="Register">Register</a>
      </p>
    </form>
  </div>
</div>
<?php
require_once("footer.inc.php");
