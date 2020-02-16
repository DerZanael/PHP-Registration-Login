<?php
session_start();
if(!($_SESSION["logged"] ?? false)) { //No need to access this page if you're not logged :)
    header("index.php");
    exit();
}
$_SESSION = [];
session_destroy();

header("Location:login.php?logout=true");
