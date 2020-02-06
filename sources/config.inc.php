<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$pdo = pdo();
function pdo() {
  //Edit your DB info :p
  $driver="mysql";
  $dsn="localhost";
  $user="root";
  $password="";
  $port=3306;
  $db="yourdb";
  try{ //We'll test the connection
    $pdo = new PDO("{$driver}:host={$dsn};port={$port};dbname={$db}", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }
  catch(PDOException $e) { //Or die with an error message
    "Could not connect to the database : ".$e->getMessage();
    die();
  }
}
