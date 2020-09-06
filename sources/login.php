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
$is_posted = ($_SERVER["REQUEST_METHOD"] == "POST"); //Check for the form being posted
//Default values for POST variables
$email = $_POST["email"] ?? ""; //Get the email input
$password = $_POST["password"] ?? ""; //Get the password input

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
    if($user === false || empty($user)) {
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
          Please click the verify account button in the email you received when you registered and try again
          <a href='resend_confirmation.php' class='btn btn-primary'>Send email again</a>";
          $error_class = "info";
        }
        else {
          $_SESSION["logged"] = true; //set the "logged" entry so we can check it to see if the user is logged or not
          //For this part we're going to log some info about the user
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

print $twig->render("login.html.twig", [
  "logout"=> $_GET["logout"] ?? null,
  "is_posted"=>$is_posted,
  "error"=>$error,
  "error_class"=>$error_class,
  "email"=>$email,
]);