# Going further : Community Packages
The community for php is extremely active, and there are been a lot of reusable scripts and tutorials published on the web, for whatever need you would ever have.

But one problem was *"how do you adapt that script to my particular case"*.

## Using the community php packages

Introducing Composer : https://getcomposer.org/ and https://packagist.org/

For a few years now, php has been introducted to a very cool tool called composer, which is a dependency manager for public packages, much like other tools from other languages, like npm for Node.js. A lot of work has been done by the community to create reusable, ready-to-use and compatible packages (think of mods for Skyrim :p) for a buttload of features you can integrate with minimum effort into your own projects. Each package might require other packages, or certain php versions to run, and composer is a tool allowing to fetch packages along with their dependencies.

For example, let's say you want to generate native excel files (not .csv) from your database in php, but you probably have no idea where to begin. Fortunately for you, there's a package called PHPSpredsheet that does exactly that. You could download it as a standalone library and try to include it in your project. But that package requires several others, and the day you want to update PHPSpreadsheet, you will need to track all other packages and get the appropriate version.

And maybe you have an older php version that the requirements for the current version of the package and can't upgrade your php so, again, you have to track the last compatible version.

Or, you can use composer that would do the work for you.

## How it works
I won't go into much details here, because the official website has a lot of information on that, and there are plenty of other tutorials that will help you a lot more than me, but we will see the global theory behind composer.

First, you will need to be able to run php as a command line program from your favourite OS console. If you use windows, I really recommand using the console provided by Git-scm, called Git Bash. Then you can install composer on your system, or just download the composer.phar file (more on that on the official website) and run it with the ``php composer.phar`` command. If you have *globally* installed composer on your system, you can just use the ``composer`` command.

Then, you need to initialize your project with the ``composer init`` command, which will create a basic ``composer.json`` containing all the information about your project. You can tinker this file to specify your php version for example, and basic project info.

To tell composer you want to use a specific package, you use the command ``composer require author/package-name``, so in our example that would be ``composer require phpoffice/phpspreadsheet``. This will update your ``composer.json`` file with all the package you marked as required (or add a new entry), then composer will download the package and its requirements into a ``/vendor`` folder, create a file called ``/vendor/autoload.php`` that we will use later, and finally create a ``composer.lock`` file that will have an exhaustive and precise list of all the packages used, their version, and some other information. This file is useful when deploying your project.

Now that you have downloaded your package, you need to update your project so it can be used. This is simply done by doing an include/require of ``vendor/autoload.php`` in your php scripts (so I would recommend adding it into **config.inc.php** in our example tutorial). Now that all your required packages are loaded, you can call them as a *Class* like ``\PDO`` or ``\Datetime`` we saw earlier.

## Introducting ``namespaces`` and the ``use`` directive
Since some classes or even packages could be named the same way, php solved that problem by using something called ``namespaces``, that define the basic/root name used by a package, and how you will be able to call a class included in the package. It roughly translates by the path of a class in the vendor folder, but not exactly. Each class included in the package will belong to a certain namespace, so you won't get the wrong class if multiple classes have the same name across all your included packages.

For example, if you wanted to create a new spreadsheet instance, you would have to do this :
```php
require("vendor/autoload.php"); //include your composer packages

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet(); //create a new instance of a class from the phpoffice/phpspreadsheet package
```
Since we don't like to type long ass paths to a class, we have the handy ``use`` directive, that basically tells php "Hey, keep that path to a class at hand, I'll need it later". Then, simply create a new instance of that class when needed :
```php
require("vendor/autoload.php");
use \PhpOffice\PhpSpreadsheet\Spreadsheet;

//do a lot of things, and then 
$spreadsheet = new Spreadsheet();
```
You can also add the root namespace and call subsequent classes from it, or even give an alias to any of them :
```php
require("vendor/autoload.php");
use \PhpOffice\PhpSpreadsheet;
use \PhpOffice\PhpSpreadsheet\Writer\Xlsx as Excel;

$spreadsheet = new PhpSpreadsheet\Spreadsheet();
$excel = new Excel();
```
***Something to know*** : You can require packages on certain versions if needed, and even restrict automatically the versions by specifying a required php version for your project. This is useful when you develop on a php7.4 local server, but will deploy your website on a php7.1 server. Since packages evolve along with the php versions, this will ensure will be usable on the final server, granted that you don't deploy your composer.lock file that is dependant on the environment.

### So what could we have used for our tutorial ?
Well the main things we could have used are :
* an environment package, like dotenv, which will facilitate deployement of your application by setting the environment-specific parameters in a separate file
* a templating engine : Templating engines allow you dissociate the logic from the display, which is one of the principles of the MVC (Model-View-Controller) design pattern that is widely used by modern php frameworks (and basically the entire industry).
* a router engine, which will allow you to use prettier and SEO-friendly urls
* an ORM, which is an abstraction layer allowing you to manipulate your database records as objects, like Doctrine or Eloquent, also one of the pillars of MVC

I won't go into explaining routing and ORM at the moment, but let's take a look at templating and dotenv, which are easier to set up and give immediate improvements.

First thing first, initialize your composer environment with ``composer init`` (or ``php composer.phar init`` if you haven't installed composer globally) in your CLI, answer the few questions from the installer, which will create your ``composer.json`` file. Just to make sure (especially if you use Composer 2.0 which is currently in alpha), do a quick ``composer update`` which will create your ``composer.lock`` file.

You will notice a new folder ``/vendor`` has been created in your ``composer.json`` folder. This will contain all the dependencies needed for your project, as well as the ``autoload.php`` file to bind them, and in the darkness rule them.

Then, edit the ``config.inc.php`` file to add a requirement to the ``autoload.php`` file (edit the path as needed if your config file is in another folder) :
```php
require_once __DIR__."/vendor/autoload.php";
```
While you're at it, if you're using git for version control, add the path to the vendor folder in your ``.gitignore`` file. I usually also add ``composer.lock`` to the ignore list when my local machine and final servers' php version don't match.

Now we're all set to add some cool features to our project. You will find the updated sources in the ``composer`` branch of the repository.

#### dotenv
Dotenv packages allow you to declare parameters at the environment level. For example, you're developping on your local machine with some SQL host/login/password, while the production server has its own parameters. And you have coworker with their own parameters on top of that. And maybe you also have a development server that has some debug functionnalities available, but not in production. 

You want to be able to provide a generic configuration file that each environment can use to define their own parameters. Dotenv packages answer that problem. They basically allow you to define a parameter template, that will be duplicated or compiled on each environment, with their own values, into a ``.env`` file. In your php scripts, you will then read the data in the ``.env`` file, and use the values in the rest of the application.

For this example, we'll use the [vlucas/phpdotenv](https://packagist.org/packages/vlucas/phpdotenv) package, which is fairly simple to install and use. 

First, add the package to your composer with ``composer require vlucas/phpdotenv``, the update and download will execute automatically, the ``composer.json`` and ``composer.lock`` files will be also updated.

Now, we'll create our ``.env`` file. What I usually do is first create a ``.env.example`` or whatever filename you want which will act as the model you'll commit and publish to your project, while the actual ``.env`` will be ignored and ***never to be commited*** since it will contain **critical** information about your machine/server. 

The file structure is basically just a list of keys and values, in our case we will store the database information and some global parameters, so it would look like this : 
```bash
APP_NAME="Your project"
APP_DEBUG=true

DB_DRIVER=mysql
DB_HOST=localhost
DB_NAME=yourb
DB_PORT=3306
DB_LOGIN=root
DB_PASSWORD=
```
You can leave the values empty or fill them with placeholders in the ``.env.example`` file. Any unfilled value will be evaluated as ``null``, and don't forget to add double quotes around any string values that might hold spaces, or you will be in big trouble.

When you have your template, copy it to the final ``.env`` file and fill the values with you actual parameters.

Since we've already required the ``autoload.php`` to our config file we're good to go to follow the package's documentation :p As per documentation we have to create the environment and load the parameters file, first parameter is the path to your parameters file, second is the file name :

```php
$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__, ".env");
$dotenv->load();
```
Now all our parameters will be available in a ``$_ENV`` superglobal available for all your project, as well as in the ``$_SERVER`` superglobal. So how about we update the ``pdo()`` function to fetch the values we used earlier ?
```php
$pdo = pdo();
function pdo() {
  $driver = $_ENV["DB_DRIVER"];
  $host = $_ENV["DB_HOST"];
  $port = $_ENV["DB_PORT"];
  $db = $_ENV["DB_NAME"];
  $user = $_ENV["DB_LOGIN"];
  $password = $_ENV["DB_PASSWORD"];

  try{
    $pdo = new \PDO("{$driver}:host={$host};port={$port};dbname={$db}", $user, $password);
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    return $pdo;
  }
  catch(\PDOException $e) {
    "Could not connect to the database : ".$e->getMessage();
    die();
  }
}
```
No more dirty critical information in plain sight in our source files! You can safely publish your project to git or other public repositories you want without exposing your machines and servers' config. You can also now re-use your sources for various projects without worrying having to find every instances of your parameters, since everything is in a neatly non-commited file.

***Remember:***

Never commit your ``.env`` file to your project repository, and you should also use ``.htaccess`` directive to deny direct access to the file.

#### Templating
What it means to use a templating engine, is that you can split your logic and your design, by getting the whole html part from another script that you will then serve to the user, after passing to it certain information (like a list of users to display etc). 

The advantage of that is that modern template engines come with their own syntax and tools to handle the data you will pass to the template from the logic script. In the end, the template scripts are actually translated into php, but they are way easier to handle, and you can use the caching functions to speed up the process.

There's a lot of templating engines available, two of the most well-known being Twig (used in Symfony) and Blade (used in Laravel). I like Twig better for various reasons, but it's really apples and oranges. Still, I'll use Twig for this example, but the logic works for whatever engin you choose.

More about Twig : [Twig documentation](https://twig.symfony.com/doc/3.x/)

First of all, require twig through composer : ``composer require twig/twig``, and we will create an object that we will use afterwards to create our vues. 

Also create a **views** folder at the project root, which will be the folder where we will store all our twig scripts, and a **cache** folder that will contain cached views (don't forget to add it to your ``.gitignore``). While we're at it, we will also add a global variable to our twig instance to store the user's session, so we will be able to access it from the views, the reason being that you *have to pass dynamic content* to the vues, we will see that later.

If we follow the official documentation, it will look like this :
```php
require("vendor/autoload.php");
$loader = new \Twig\Loader\FilesystemLoader("views"); //the path to our twig scripts
//We'll crate a $twig variable we'll be able to use in our other files, much like $pdo
$twig = new \Twig\Environment($loader, [
    "cache"=>"cache", //the cache folder
    //"debug"=>true, //uncomment this if you want to be able to use the dump() functions of twig
]);
$twig->addGlobal("_SESSION", $_SESSION); //Adding the session globally
```
In the sources, you will see that I've also added a few things, like declaring a ``getenv()`` twig function to fetch data from the ``$_ENV`` superglobal from the views. I've also added the debug functionnality to twig to dump things.

In the ``/views`` folder, create a ``layout.html.twig`` file we will use to define the basic layout used in all the templates. Basic layouts allows you to create named ``blocks`` which you can, in each template, replace with the content you want. So we will simply copy the ``header.inc.php`` and ``footer.inc.php`` contents in, define a "content" ``block`` between them, and a "title" ``block`` in the header, so we can change the title of the page if we want to. 

In twig, instructions and functions are declared between ``{% ... %}`` and echoes between ``{{ ... }}``. You will see we will also use filters applied with ``|filtername`` on any variable or functions, for example ``{{ "hello"|upper }}`` will output ``HELLO``

Blocks can be empty or have content that can be replaced in each view, and are defined like this : ``{% block something %}{% endblock (something) %}``, the block name is optional in the ``{% endblock %}`` instruction.

```html
<!doctype html>
<html>
  <head>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{% block title%}My default title{% endblock %}</title>
    <meta charset="utf-8">
  </head>
  <body>
    <div class="content">
    {% block content %}{% endblock %}
    </div>
  </body>
</html>
```
Now let's create a view ``index.html.twig`` for the index page : each view can extend a base layout (in our case ``layout.html.twig``), in which case you simply define blocks you'll fill with contents that will replace the blocks of the basic layout. So the view for the index page would look like this :

```twig
{# comments look like this #}
{% extends "layout.html.twig" %}{# the base layout #}
{% block title %}The index page{% endblock %}{# replacing the title block #}
{% block content %}{# replacing the content block #}
    {% if _SESSION["logged"]|default(false) %}{# the |default(value) filter works like the ?? operator #}
    <p>Welcome, {{ _SESSION["firstname"] }} {{ _SESSION["lastname"] }} !
        <br>Go to <a href="profile.php" title="Your profile">your profile</a>
    </p>
    {% else %}
    <p>Welcome to Your Super Website!
        <br><a href="login.php" title="Login">Login</a> or <a href="register.php" title="Register">Register</a>
    </p>
    {% endif %}
{% endblock %}
```
As you can see, we haven't used any ``<?php ?>`` tags around variables or instructions, which should clear possible confusions, and we replaced some default content of the template directly from the view.

Now let's update our greatly simplified ``index.php`` to handle the view call :
```php
<?php
session_start();

require_once("config.inc.php");

print $twig->render("index.html.twig", [
    //You can pass additional variables to the view in this array
]);
```
The ``$twig->render("template name", [])`` method will call the template engine to compile and render the given template file, and will return the result. Since it's a string, you can just print the result. Any entry you pass in the parameter array will be available to the view as a variable, for example : 
```php
//file.php
print $twig->render("some_template.html.twig", [
    "foo"=>"bar"
]);
```
```twig
{# some_template.html.twig #}
{{ foo }}{# will output "bar" #}
```
Following this idea, let's say you want to display a list of users, I'll use a script from chapters/02-Databases :

```php
$verified = 1;
$domain = "%@test.com";
$statement = $pdo->prepare("SELECT * FROM `user` WHERE (`verified` = :verified AND `email` LIKE :email) ORDER BY `email` ASC");
$statement->bindParam("verified", $verified, \PDO::PARAM_INT);
$statement->bindParam("email", $domain, \PDO::PARAM_STR);
$statement->execute();
$users = $statement->fetchAll();
print $twig->render("users.html.twig", [
    "users"=>$users,
])
```
```twig
{% for user in users %}{# equivalent to a foreach #}
    {% if loop.first %}<ul>{% endif %}{# loop is a special variable available in "for" loops which will give you info on the current loop status, in this case, we check if it's the first item of the loop #}
        <li>{{ user.email }}</li>
    {% if loop.last %}</ul>{% endif %}{# same as above for the last item #}
{% else %}{# you can have a "else" case if the provided array doesn't have items #}
<p style="color:red;">No user found</p>
{% enfor %}
```

By now, you probably have realized that using a templating engine allows you to simplify both the logic of your php script and the design. It requires a little setup, though, but the benefits easily outweight the initial investment. And you're one step closer to the MVC model ;)