# A tutorial for PHP registration/login
This tutorial aims to teach you how to build a complete registration and login system in basic php with built-in sessions.

We well go over the **database creation**, how to interact with it, store and retrieve data to manipulate an user record, how forms work in html and php, and how to store information in the php sessions.

## Preresquites
This tutorial will use php7.x (actually 7.4) syntax, and I've been using mariadb for my database (more on that later).

We're not going to use available 3rd-party libraries that you would find with composer and frameworks, but I will give some advices on how to get started on those in a later chapter.

### "But I still use php 5.6"
Well you should upgrade your web server as soon as possible. Php7 is faster and more secure, there's no reason not to upgrade unless you are maintaining an old app on your server. Even then, php5.6 code is 90% compatible with php 7.x so you could upgrade with minimal difficulties.

If you really need to use php5.6, there's a few differences I'll cover later. There's also one difference between php7.4 and older 7.x versions that I will cover as well.

## About me
I'm a web developer and have been using php (and html, css, js...) for something like 17 years now. I've built a lot of websites, blogs and even forums from scratch, and have been creating login forms and user authentication systems for years, in procedural php and with frameworks (Symfony and Laravel).

I've seen a lot of php newcomers struggling with that part, who often use outdated guides with outdated code and bad habits, so this tutorial aims to give you something up-to-date code-wise, well as of 2020 and representing my vision of procedural php. But I'll explain why my source code looks like that ;)

## What's in this tutorial
Three parts in this :
* The tutorial itself, neatly organized in chapters, in /chapters
* The source code I'm basing the tutorial on, in /sources
* A *Going Further* section in /going-further where we'll cover more functionnalities not included in this tutorial.

In the /db folder, you will find a simple SQL script to create your database and an user table to store your users.

A ``composer`` branch is also available to reflect the [Going further 3 : Community packages](going-further/03-community-packages.md) section.

## The sources
* ``index.php`` : the landing page, sending your users to the login, registration or profile page
* Config and included pages :
  * ``config.inc.php`` : sets up the database connection and some environment rules
  * ``header.inc.php`` : first part of the html pages
  * ``footer.inc.php`` : closes the html
* Registration related :
  * ``register.php`` : allows users to register to the website by setting their email and password
  * ``complete_registration`` : users will confirm their email adress
  * ``resend_confirmation`` : sends a new token to the users
* Login related :
  * ``login.php`` : login to the website
  * ``logout.php`` : destroy the session and logs out
* ``profile.php`` : user's profile, available when logged in

All the sources are organised on the same level, but you should *really* organise your website better.

## Navigation
Tutorial chapters :
* [Chapter 1 : Introduction](chapters/01-introduction.md) : What we're doing, some php basics, and the first scripts
* [Chapter 2 : Database setting](chapters/02-database-setting.md) : Setting up the tutorial's database and the connexion
* [Chapter 3 : Registration](chapters/03-registration.md) : The registration form and inserting records to the database
* [Chapter 4 : Email validation](chapters/04-email-validation.md) : Finalizing the user registration
* [Chapter 5 : Login](chapters/05-login.md) : Log the user
* [Chapter 6 : Going further](chapters/06-going-further.md) : A quick introduction to new features

Going further :
* [Chapter 1 : Cookies](going-further/01-cookies.md) : using cookies and regenerating the session
* [Chapter 2 : User roles](going-further/02-user-roles.md) : A word about user roles to differenciate your users
* [Chapter 3 : Community packages](going-further/03-community-packages.md) : Using composer, environment parameters and templating

Let's start : [Chapter 1 : Introduction](chapters/01-introduction.md)