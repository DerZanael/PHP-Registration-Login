For this chapter, I'll assume you have some way to manipulate your database, either through the native console mysql access, or with phpmyadmin. If you're not aware of this cool tool, you might have it bundled with your development web server, but you can also install it if you wish. You can visit the project's webpage : [phpMyAdmin](https://www.phpmyadmin.net/)

# Setting up the database
First of all, create a database and call it whatever you fancy. In the tutorial, I've created a ``yourdb`` database. You will also need to check the database character encoding, since we'll be using UTF8 for both the php and html part, I recommend you use ``utf8mb4_general_ci`` which is commonly used. The "mb" part is important for reasons I forgot, but there are probably plenty of explanations available on the web (it has to do with multi-byte characters and how utf8 works).

If you are not aware, database fields are qualified by some properties, most importantly their name, type, default value and nullable state.

For most of your data, you ***need*** a field which will hold an unique value capabale of identifying your database records within a table. The field is commonly called ``id``, and a lot of frameworks will assume you're using that name.

For this tutorial, we'll create a simple ``user`` table, for which we will need a few fields. For not nullable fields, you can set up a default value to "" or 0 depending on the field type : 
* ``id`` (int 11): the user ID, which will serve as the table index. This field will be set to auto-increment, so each time a record is added so you don't really need to bother with setting it up.
* ``firstname`` (varchar 255) : user's first name, not nullable
* ``lastname`` (varchar 255): user's last name, not nullable
* ``email`` (varchar 255): user's email adress, which will be used to authenticate the user, absolutely not nullable
* ``password`` (varchar 255) : user's password, also used to authenticate the user. Hashed, not nullable
* ``verified`` (tinyint 2) : this will be used to check if the user has verified their email address, not nullable, set default to 0 (not verified).
* ``token`` (varchar 80, nullable) : sent to the user in an email and used to verify the user's email address.
* ``registration_date`` (datetime) : self-explainatory I guess ;), not nullable. You can default that to the available mySQL instruction ``CURRENT_TIMESTAMP``. Watch out for timezones, your DB server could be in a different timezone than your user. But I won't talk about that in this tutorial.

I've used arbitrary large lenghts for the INT and VARCHAR fields, keep in mind you should look closely at these values if you're working with databases with a large amount of data and disk space is tight.

## DON'T EVER
**Don't ever store the user's password as plain text.** It's in the interest of your users.

You might also think it's an ok idea if the user needs to get their password back, but it's better to replace those than store them as plain text, or in a decryptable way.

***Also avoid using hashing methods like md5 or sha1.*** They are fast to create but also *fast to bruteforce*, and there's plenty of files on internet with hashes and their corresponding unhashed values available. So if one day your database is hacked, at least the hackers will have a bad time trying to de-hash your users' passwords.

## Connecting to the Database
Now we have a working database, we need to connect to that with php.

For this tutorial, we'll be using ***PDO***, a php abstraction class for databases. PDO (mostly) does not care which type of database you use, and allows you do develop and deploy your website without needing to rewrite all of your source code if you use different DB systems between environment. And if you want to change your DB server, worst-case scenario you'll only need to update your queries, instead of **all your code**.

Also, you'll become familiar with *Object-Orientend Programming (OOP)*, since PDO is a php *Class* and not multiple interacting procedural functions, which is a ++++ if you want to look into frameworks and cool libraries. If you're not familiar with OOP, I suggest you go reading about that since I'm going to use some *jargon* in a few paragraphs. 

### About objects

But basically, a *Class* is an ensemble/template/model of ***properties and methods*** used through ***static calls*** or to create a (or several) manipulable ***object*** through ***instanciating***. When you create an object, you can access their properties and methods through the ``->`` assignation operator. Think of an array with functions built in. You can also do direct calls to ***static*** properties and methods of a *Class* without instanciating it.

For example you have a class ``A`` with a ``foo`` property, a ``setFoo()`` and a ``getFoo()`` functions that set and returns the value of ``foo``. You instanciate the A class into a ``$b`` and ``$c`` objects, for which you set ``foo`` to "foo_b" and "foo_c" respectively through the ``->setFoo()`` method. When you call ``$b->getFoo()`` you'll get "foo_b" and ``$c->getFoo()`` "foo_c", despite both being instance of the same Class A.

Another cool trick of *Classes* is that they can be ***extended***, meaning a *Class* extenting another one will inherit their properties and functions. For example, a ``B`` Class extending the ``A`` Class in the above example will also have access to ``foo``, ``setFoo()`` and ``getFoo()``.

Like this :
```php
<?php
Class A
{
    public const HELLO = "hello"; //a constant property
    public $bar;
    private $foo; //a private property not available outside the class
    
    public function setFoo(?string $value) :void
    {
        $this->foo = $value; //you can manipulate private properties from a class method
    }

    public function getFoo() :string
    {
        return $this->foo; //and read them
    }
}

//Class Z will have all the properties and methods from Class A
Class Z extends A
{
    public const GOODBYE = "goodbye";
}

//Instanciating
$b = new A();
$b->bar = "bar_b";
$b->setFoo("foo_b");
$c = new A();
$c->bar = $b->bar;
$c->setFoo("foo_c");
$d = new Z();
$d->setFoo("foo_d");
echo $b->foo; //throws an error because "foo" is private and can't be accessed from outside the class
echo $b->getFoo(); //outputs "foo_b"
echo $c->getFoo(); //outputs "foo_c"
echo $c->bar; //outputs bar_b since we were able to read $b->bar
echo $d->getFoo(); //outputs "foo_d"

//Static classes and extending
echo A::HELLO; //ouputs "hello"
echo Z::HELLO; //ouputs "hello"
echo Z::GOODBYE; //ouputs "goodbye"
echo A::GOODBYE; //throws an error because the constant GOODBYE does not exists for A
echo $b::HELLO; //Outputs "hello"
```

It's handy to know and there's a whole lot more to learn about classes, like *implementing Interfaces* or *using Traits*, but that's not the topic at hand. You'll need to know about ***instanciating*** and the ``->`` in a few moments, though.

### Why not ``mysqli_*`` functions or class ?
As I said, PDO is more flexible. If you use a MySQL server and wish to move to PostgreSQL or Oracle or whatever, you would have to update ***EVERY*** ``mysqli_*`` function to their equivalent. You can also develop locally on MySQL and deploy your app on a server using another database system. Ultimately, you could use them, but why bother ? PDO is just better and (mostly) database-agnostic.

### "I read other tutorials that use ``mysql_*`` functions and..."
Well, they were written 10 years ago and they are outdated. Don't.

***``mysql_*`` functions have been DEPRECATED since php5.5 (2013 !!) and removed with php7.*** Don't use them.

### How do PDO work
PDO needs a few information to connect to a database :
* a driver, indicating what kind database server you're using. In this example, we'll be specifying ``mysql`` (as a side note, if you're working with oracle, don't use ``pdo_oci`` but ``oci8``, trust me)
* a host : basically the alias/IP/domain name of your database, like ``localhost`` if the DB is on the same server as your web server (which is our case)
* a port : default mysql port is ``3306``
* a db user that can access your database server's target database
* the db user's password
* a db name

As said before, PDO is a php *Class* so we will have to create a new PDO object, to which we will feed the above information, and that we will manipulate and use to do things in our database. We'll put that in ``config.inc.php``.

In the file, we will declare a ``$pdo`` variable, calling a ``pdo()`` function, in which the ``PDO object`` will be created. Why in a function you ask ? Because I'll declare a few variables there that I don't want to be overwritten by other variables with the same name or whatever in other scripts.

The ``PDO object`` is created like this in our case :
```php
$pdo = new \PDO("<database driver>:host=<database host>;port=<database port>;dbname=<database name>", "<database user>", "<database password>");
);
```
Why the ``\`` in ``\PDO`` ? It's an habit I got from working with frameworks and namespaces, it tells php to look into the globally available classes and functions for php, instead of the ones locally loaded with the current script. You sometimes have errors coming from not using the ``\``.

The constructor of the PDO class requires 3 parameters : 
* A dsn, which is basically a string containing the adress of the database serveur along the driver used (mysql, postgres, oci etc), to which to can pass along various parameters like the port and the database name
* the user name
* the password

So add the following in ``config.inc.php`` after the error handling (replace with your server information, of course) :
```php
$pdo = new pdo();
function pdo() {
    $driver = "mysql";
    $host = "localhost";
    $port = 3306;
    $user = "your db user"; //root in my case - but don't do that
    $password = "your db password"; //empty for me, don't do that either
    $dbname = "your db name";
    return new \PDO("{$driver}:host={$host};port={$port};dbname={$dbname}", $user, $password);
}
```
### What's with the ``"{$variable}"`` syntax ?
There's a few ways to ***concatenate*** strings and variables in php, for example :
* Using ``'this is a string'.$variable``, or ``"this is a string".$variable;``
* Using ``"this is a string with $variable in it"`` (note the double quotation mark here)
* Using ``"this is a string with {$variable} in it"`` (again, the double quotes)

There's a difference between using single quotes ``'`` and double quotes ``"`` in php. Single quotes don't allow for interpretation of php variables, meaning that if you declare a ``$variable = "yo";``, doing ``echo 'my variable is : $variable';`` will output exactly that, while doing ``echo "my variable is : $variable";`` will output ``my variable is yo``. If you want to include a variable into a string using single quotes, you *have* to use the ``.`` concatenation operator.

The ``{}`` brackets around the variable allows you to use special characters in the variable name, like underscores ``_``, and most importantly you can use array indexes or object properties/functions, like ``"hey {$array["property"]}"`` or ``"hey {$object->property}"``, otherwise the special characters won't be intepretated by php.

There are some debates in the community regarding the use of single or double quotes, about script performance and such. Mostly the outcome is "It's basically the same". I prefer using double quotes over single quotes, since you'd have to escape every single quotes otherwise, which are commonly found in french or english, or rely on concatenation for every variable in strings. I'm lazy so...

### Error handling
We've created a PDO object, but maybe we set up the wrong information, or the connection does not work for some reason. As of now, we wouldn't know when something goes wrong. So we're going to encapsulate the object creation into a ``try... catch`` test which will display the error and kill the script if something happens.

***In a production environment***, please do not display the error, simply tell the user something went wrong and send a notification to the team, otherwise you might expose very sensitive information, and you don't want that.

Fortunately, this is a tutorial, so go all in. When failing to connect, we'll display the PDO error message from ``PDOException`` class in the ``catch()`` part. Also we'll set our PDO object to display all relevant errors when executing a query. Our object creation in the ``pdo()`` function now becomes :
```php
try{ //We'll test the connection
    $pdo = new \PDO("{$driver}:host={$dsn};port={$port};dbname={$db}", $user, $password); //Creates a connection to the database
    $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION); //Tells the object to display more errors, like a query that goes wrong
    return $pdo;
}
catch(\PDOException $e) { //Or die with an error message
    print("Could not connect to the database : ".$e->getMessage());
    die(); //Kill the php script
}
```
You'll notice we return the ``$pdo`` object in the ``try`` part, that's because we don't need that if something goes wrong, as we just kill the script in that case.

The ``PDOException`` class is generated when an error happens, and ``PDO::ATTR_ERRMODE`` and ``PDO::ERRMODE_EXCEPTION`` are options for PDO allowing more error messages. The ``::`` is a pointer to contant properties and methods of a *static* class call, where you don't instanciate a *Class* but instead refers to it directly.

Try and reload ``index.php`` and check if you have any error. Correct them if needed.

## How are we going to do requests to the database ?
Introducing ***prepared statements*** : https://www.php.net/manual/en/pdo.prepare.php

*Prepared statements* are a way to execute queries on a database while preventing SQL injection (or at least reduce the threat of injection). If you want to know more details, please read https://en.wikipedia.org/wiki/Prepared_statement

With PDO, the common way to do a request is to first create a prepared statement, execute the statement with parameters, fetch the result, and optionnaly loop on the records from the result.

For example, let's say we want to fetch and display all records in the user table who have verified their email address that ends in "@test.com", the script would look like this :
```php
$statement = $pdo->prepare("SELECT * FROM `user` WHERE (`verified` = ? AND `email` LIKE ?) ORDER BY `email` ASC");
$statement->execute([1, "%@test.com"]);
$users = $statement->fetchAll();
foreach($users as $user) {
    //Do something
}
```
The ``?`` in the query string represent placeholders for the parameters we will inject to the statement with ``->execute()``. The parameters are passed as an array, even if you only have one, and will be replaced in the same order as they are in the array, so don't shuffle them ;) 

I'll be using ``?`` placeholders, but if you want a more detailed system, you can also use named placeholders like ``:placeholder`` and the ``->bindParam()`` method of a PDO object. ``bindParam()`` require a placeholder from the statement and a value. You could additionnaly pass the type of the placeholder. The script would look like that :
```php
$verified = 1;
$domain = "%@test.com";
$statement = $pdo->prepare("SELECT * FROM `user` WHERE (`verified` = :verified AND `email` LIKE :email) ORDER BY `email` ASC");
$statement->bindParam("verified", $verified, \PDO::PARAM_INT);
$statement->bindParam("email", $domain, \PDO::PARAM_STR);
$statement->execute(); //Since we've bound placeholders and parameters we don't need to pass them in the execute()
$users = $statement->fetchAll();
foreach($users as $user) {
    //Do something
}
```
Alternatively to the ``->fetchAll()`` method, you can do a ``->fetch()`` for just one result at a time in a ``while()`` loop, like this :
```php
$statement->execute();
while($user = $statement->fetch()) {
    //Do something
}
```
For the ``->fetch()`` and ``->fetchAll()`` methods, you can also pass a *fetch style* that will determine how the returned record will be interacted with, for example as an array will full indexes, an associative array, or an object. Fetch styles are actually constant integers from the PDO *Class*. I'll be using the argument ``PDO::FETCH_OBJ`` to get objects in the tutorial, like this :
```php
$user = $statement->fetch(\PDO::FETCH_OBJ);
```
You can also set the default fetch mode of all your calls with the ``setAttribute()`` method, like we used for the errors. Like this : 
```php
$pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
```
If you want to read more about the *fetch styles*, please refer to the official documentation :
https://www.php.net/manual/fr/pdostatement.fetch.php

Also you will notice the ` around the tables names, it's not mandatory so you could avoid using them.