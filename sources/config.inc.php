<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__."/vendor/autoload.php";
//Loading environment parameters
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, ".env");
$dotenv->load();

//Loading twig
$loader = new \Twig\Loader\FilesystemLoader("views"); //loading twig environment
//create variable to use to render views
$twig = new \Twig\Environment($loader, [
    "cache"=>"cache", //the cache folder
    "debug"=>($_ENV["APP_DEBUG"] ?? false), 
]);
$twig->addGlobal("_SESSION", $_SESSION); //Adding the session globally
$twig->addGlobal("_ENV", $_ENV["APP_ENV"] ?? "local"); //ENV status
$fnc = new \Twig\TwigFunction("getenv", function($key){
  return $_ENV[$key] ?? null;
});
$twig->addFunction($fnc);
if($_ENV["APP_DEBUG"]) {
  $twig->addExtension(new \Twig\Extension\DebugExtension());
}

$pdo = pdo();
function pdo() {
  //Edit your DB info :p
  $driver = $_ENV["DB_DRIVER"] ?? "mysql";
  $host = $_ENV["DB_HOST"] ?? "localhost";
  $port = $_ENV["DB_PORT"] ?? 3306;
  $db = $_ENV["DB_NAME"] ?? "yourdb";
  $user = $_ENV["DB_LOGIN"] ?? "root";
  $password = $_ENV["DB_PASSWORD"] ?? "";

  try{ //We'll test the connection
    $pdo = new \PDO("{$driver}:host={$host};port={$port};dbname={$db}", $user, $password);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }
  catch(\PDOException $e) { //Or die with an error message
    "Could not connect to the database : ".$e->getMessage();
    die();
  }
}
