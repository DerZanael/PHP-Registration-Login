# A tutorial for PHP registration/login
This tutorial aims to teach you how to build a complete registration and login system in basic php with build-in sessions.

We well go over the **database creation**, how to interact with it, store and retrieve data to manipulate an user record, how forms work in html and php, and how to store information in the php sessions.

## Preresquites
This tutorial will use php7.x (actually 7.4) syntax, and I've been using mysql for my database (more on that later).

We're not going to use available 3rd-party libraries that you would find with composer and frameworks, but I will give some advices on how to get started on those in a later chapter.

### "But I still use php 5.6"
Well you should upgrade your web server as soon as possible. Php7 is faster and more secure, there's no reason not to upgrade unless you are maintaining an old app on your server. Even then, php5.6 code is 90% compatible with php 7.x so you could upgrade with minimal difficulties.

If you really need to use php5.6, there's a few differences I'll cover later. There's also one difference between php7.4 and older 7.x versions that I will cover as well.

## About me
I'm a web developer and have been using php (and html, css, js...) for something like 17 years now. I've built a lot of websites, blogs and even forums from scratch, and have been creating login forms and user authentication systems for years, in procedural php and with frameworks (Symfony and Laravel).

I've seen a lot of php newcomers struggling with that part, who often use outdated guides with outdated code and bad habits, so this tutorial aims to give you something up-to-date code-wise, well as of 2020 and representing my vision of procedural php. But I'll explain why my source code looks like that ;)

## What's in this tutorial
Two parts in this :
* The tutorial itself, neatly organized in chapters, in /chapters
* The source code I'm basing the tutorial on, in /sources

In the /db folder, you will find a simple SQL script to create your database and an user table to store your users.