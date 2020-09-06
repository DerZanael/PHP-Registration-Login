# Introduction
Alright, let's dig in.

## What do we need
So, you'll need a **text editor**, but you should have one already, right ? Otherwise, I'd recommend VSCode which is free and highly customizable. But really, any free editor with php language support like Atom, Notepad++, or IDEs like phpStorm or NetBeans.

You of course need a **webserver**, and you also probably have one running. For local dev I usually use EasyPHP for Windows which comes with bundled-in Apache and MySQL servers, but xampp on Windows, mamp on MacOS are also available, with plenty of others (actually I just switched to xampp). If you're a lucky guy working on linux, you favourite distrib should have Apache 2.4 or Nginx available, as well as php and mysql. Or even better, find yourself a docker script with all your needs!

Keep in mind that I will be using **php7.x** compatible syntax, so you should run **php7.1** at the very least, or you might get a couple of errors, but I will specify those. If you're still using php **7.1** you should upgrade, by the way.

I've been using **MariaDB 10.4** to run the database, so it will also work with MySQL 5 or 8. For Postgres, Oracle or other DB users, the SQL requests *should* work, but I'm not really familiar with those. No idea about noSQL though.

A note on MySQL 8 : the default identification method has problems working with the available drivers for php, because the 8+ versions changed their default password encryption for the connection, but there are quick fix for that, google ``mysql native password`` for more on that matter.

You will also see in the source files I've been using bootstrap for the HTML part of the pages, this is by no mean required, it's just for visual candy.

Last bit of information, we'll be using UTF8 as our character encoding for files, html and database charset. Character encoding can be a pain in the ass to work with, and you really want to be coherent between all your systems.

## How the auth system and basic website will work
The demo website will have a simple flow :
* We will create a basic landing page on which the user will have the option to log in or register an account.
* On the registration page, users will have to fill their name, email and password
* We will check if the email address is already in use when the registration occurs, because we don't want duplicate accounts
* The account will be stored in the database with a verification token
* The user will have to verify their email address with the generated token to complete their account
* Once their account is verified, users will be able to log in
* The authentication will grant access to the user profile page
* If the user tries to access a page where they should be logged despite not being, we'll redirect them to the landing page.
* In the same way, if they try to access a page while being logged when they're not supposed to be, they'll also be redirected.

## What are php sessions
I've mentionned sessions earlier, so let's talk about them for a moment.

Sessions are a way for php to transmit information between php pages without the need of passing data through the url or form data. We'll be using them to check if the user is logged, store and display some info on the user (like the name and email address).

Sessions are available with the ``$_SESSION`` superglobal variable, which can be used in any php file, like the other superglobals you should be aware of : ``$_GET`` (used to get url info), ``$_POST`` (form data), ``$_COOKIES`` (read cookie data) or ``$_SERVER`` (everything about the web server). 

***The variable acts as an array*** : you can store values in indexes, so for example you can set something on one page with 
```php
$_SESSION["foo"] = "bar";
``` 
and access the data on another page with 
```php
echo $_SESSION["foo"];
```
In order to do that, however, you must declare on each php file that should receive session data to actually use them, with calling ``session_start()`` ***usually at the top of the file***.

``session_start`` is very sensitive and dislike having other instructions above it, especially rendered HTML, including white spaces (like ``header()`` for example). 

So don't forget to add ``session_start()`` in your files, right at the top. If you're going to develop or run multiple websites on the same server or URL you might want to name your session so the data won't mix between your websites.

You can name a session by using the ``session_name()`` instruction, and specifying a session name, before ``session_start()`` and ***on all your php files*** like this :
```php
session_name("mycoolsession");
session_start();
```

Through the tutorial, we will often test if the session is alive by testing ``$_SESSION["logged"]``, you'll see later when we initialize that (spoiler : it's in ``login.php``), but keep in mind this is a way to tell our php scripts if an user is logged and do execute code depending on the outcome.

## The landing page

Let's get started, create an empty landing page, **index.php** at the root of your website's location, so for example in ``your/web/server/folder/your-website.com/index.php``

You should know by now, but php instructions are executed when between the php tags 
```php
<?php 
//Your code goes here
?>
```
In the **index.php** file, we'll start the session handling, but since we'll assume the user is not connected (and because we haven't created the auth system yet :p), we will also create some links to the login and registration pages. 

Add the following :
```html
<?php
session_start();
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Your Super Website</title>
    </head>
    <body>
        <p>Welcome to Your Super Website!
            <br><a href="login.php" title="Login">Login</a> or <a href="register.php" title="Register">Register</a>
        </p>
    </body>
</html>
```
This is a basic html page structure, with some php at the top. We'll be doing that in several pages. But you know what's tedious? ***Duplicating static code***. Fortunately, php covers that and allows to include php files in other php files, so you can write some part of code once and use that in all your pages.

We'll be isolating the static HTML parts in a couple of files and including those in our basic pages. Create a **header.inc.php** and a **footer.inc.php** page in the same place of **index.php** (or in a subfolder if you want) with the following code, respectively :

```html
<!doctype html>
<html>
    <head>
        <title>Your Super Website</title>
    </head>
    <body>
```
```html
    </body>
</html>
```
You can include a page in another with any of the following instructions :
```php
include("path/to/your/script.php");
include_once("path/to/your/script.php");
require("path/to/your/script.php");
require_once("page/to/your/script.php");
```
So now, the ``index.php`` page becomes :
```html
<?php
session_start();
require_once("header.inc.php");
?>
<p>Welcome to Your Super Website!
    <br><a href="login.php" title="Login">Login</a> or <a href="register.php" title="Register">Register</a>
</p>
<?php
require_once("footer.inc.php");
?>
```
See, no more static HTML code cluttering our php files.

While we're at it, since we're developing and probably do plenty of errors, we kind of need to know what is happening when something goes wrong. For this we want to tell php to show us errors. ***In a production environment*** you really don't want to do that, because you don't want to expose to the users how your website works (or worst-case scenario sensitive data like database username or pasword), but this is a tutorial so we don't really care. You can set that up through php's configuration files, but you can also force and override the error display locally in a php script.

Create a ``config.inc.php`` file wherever you want, and put the following in it :
```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```
### Hey you didn't close your ``<?php`` tag
You don't actually need to use it at the end of a file, especially when the file is pure php, and included in another one. It avoids problems with sessions and headers by the way.

Now we're going to use that in every php script we'll use, so call it in ``index.php``. I would call that right after ``session_start()`` so ``index.php`` becomes like this :
```html
<?php
session_start();
require_once("config.inc.php");
require_once("header.inc.php");
?>
<p>Welcome to Your Super Website!
    <br><a href="login.php" title="Login">Login</a> or <a href="register.php" title="Register">Register</a>
</p>
<?php
require_once("footer.inc.php");
```
From now on, I'll call the mixed php/html part between the header and footer inclusion "content".

Ok now we're going to make the registration page, but before that we need to set up our database.

[Chapter 2 : Database setting](02-database-setting.md)