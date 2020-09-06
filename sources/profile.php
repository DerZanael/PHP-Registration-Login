<?php
session_start();
if(!($_SESSION["logged"] ?? false)) {
  header("Location:login.php");
}

require_once ("config.inc.php");

print $twig->render("profile.html.twig", [

]);
