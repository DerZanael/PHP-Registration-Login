<?php
session_start();
if($_SESSION["logged"] ?? false) {
  header("Location:index.php");
  exit();
}
require_once("config.inc.php");

$error = ""; //Error message
$is_posted = ($_SERVER["REQUEST_METHOD"] == "POST"); //Checks if the form has been posted
$register_complete = false;
$email_sent = false;

//Default values for POST
$firstname = $_POST["firstname"] ?? "";
$lastname = $_POST["lastname"] ?? "";
$email = $_POST["email"] ?? "";
$password1 = $_POST["password1"] ?? "";
$password2 = $_POST["password2"] ?? "";

if($is_posted) {
  //Get the POST data
  $checks = ["email", "password1", "password2", "firstname", "lastname"];
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
          "From" => $_ENV["SMTP_FROM"],
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

$register_complete = $_SESSION["register_complete"] ?? false;
$email_error = $_SESSION["email_error"] ?? false;
unset($_SESSION["register_complete"]);
unset($_SESSION["email_error"]);

print $twig->render("register.html.twig", [
  "firstname"=>$firstname,
  "lastname"=>$lastname,
  "email"=>$email,
  "is_posted"=>$is_posted,
  "error"=>$error,
  "register_complete"=>$register_complete,
  "email_error"=>$email_error,
]);
