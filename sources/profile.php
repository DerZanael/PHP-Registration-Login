<?php
session_start();
if(!($_SESSION["logged"] ?? false)) {
  header("Location:login.php");
}

require_once ("config.inc.php");
require_once ("header.inc.php");
?>
<p class="alert alert-info">Hey <?php echo $_SESSION["firstname"]; ?> <?php echo $_SESSION["lastname"]; ?> ! Your email is <?php echo $_SESSION["email"]; ?> !
  <br><a href="logout.php" title="Log out">Log out</a>
</p>
<?php
require_once ("footer.inc.php");
