<?php
session_start();

require_once("config.inc.php");
require_once("header.inc.php");
?>
<?php if($_SESSION["logged"] ?? false) {
  ?>
<p>
  Hello you are logged ;)
  <br><a href="profile.php" title="Your profile">Go to your profile page</a>
</p>
  <?php
}
else {
  ?>
<p>
  Hello, you are not logged
  <br><a href="login.php" title="Login page">Login</a> or <a href="register.php" title="Registration page">Register</a>
</p>
  <?php
}
?>
<?php
require_once("footer.inc.php");
