# Email validation process
In the previous chapter, we sent an email to the user with a link to validate their email address. The link looks something like this : 

``https://your-super-website.com/complete_registration.php?email=<the user's email>&token=<the generated token>``

## The validation script
Let's create the **complete_registration.php** page. Add the ``session_start()`` directive, add your included files, and add a check for ``$_SESSION["logged"]`` to redirect your user to **index.php** if they are already logged, pretty much like the start of the registration page. Don't forget the footer ;)

Now let's get our url parameters. Like ``$_POST``, url parameters can be fetched from a php *superglobal* ``$_GET``, which also acts like an associative array. What you get (lol.) in this are the additional parameters added to a php script url. Additional parameters have to be declared by separating the script name (in our case **complete_registration.php**) from them with an ``?``, and separated from each other with ``&``. Each parameter has a *key* and a *value* defined by ``key=value``.

For example, the url ``musician.php?firstname=dave&name=grohl`` would have two entries in ``$_GET`` : 
```php
$_GET[
    "firstname"=>"dave",
    "name"=>"grohl"
]
```
So first we will retrieve our email and token from the url, and set them by default to ``""``. While you're at it, declare a ``$error`` :
```php
$error = "";
$email = $_GET["email"] ?? "";
$token = $_GET["token"] ?? "";
```
Do a check on both parameters to see if they are empty, and redirect to **index.php** if one of them is missing. Since we only have two parameters to check and that most likely won't change anytime soon, I won't bother with pointers.
```php
if(empty($email) || empty($token)) {
    header("Location:index.php");
    exit();
}
```
If you look at the provided source code, you will see that I do that check before including my **config.inc.php**, that's because I don't want to interact with the database if not needed. Do as you wish ;) You will also see that I declared a ``$error_class``, that's purely eye candy for the color of the error message displayed.

If we're good, we will retrieve the user corresponding to the email address from the database, and we will check a few things and display an error message if one fails :
* The user actually exists
* If they don't have verified their address
* If the token provided in the url correspond with the one stored in the database

First, retrieve the user :
```php
$stmt = $pdo->prepare("SELECT * FROM `user` WHERE (`email` = ?) LIMIT 0, 1");
$stmt->execute([
  $email,
]);
$user = $stmt->fetch(PDO::FETCH_OBJ);
```
As a sidenote : you really should clean everything you fetch from ``$_GET`` and ``$_POST``, especially when working with databases, because ***we don't trust the user***. But we're using *prepared statements* so we are kind of ok on that issue. Still, it's good practice, and at least use ``trim()`` to remove whitespaces at the start and the end of strings.

``PDO::fetch()`` will return ``false`` if no result is returned by ``execute()``, and you might also get a null or empty object, we will test for that :
```php
if($user === null || $user === false || empty($user)) {
  $error = "No record found in the database for this email"; //again, you might not want to expose that fact, so you can just display a generic message
}
```
If you get no error, and therefore the user exists, we will check if they already verified their address, so we will look at the ``verified`` property in the ``else {...}`` clause :
```php
if((bool) $user->verified) {
    $error = "Your email has already been verified";
}
```
The ``(bool)`` will force the type of the ``$user->verified`` value into its *boolean value*, so ``0`` becomes ``false`` and ``1`` becomes ``true``. We could have done the check on the numeric value, but I'd rather test an actual boolean.

If the user hasn't verified their email, we can check the parameter token against the database token, again in the ``else {...}`` clause of the previous test :
```php
if($user->token !== $token) {
    $error = "The token does not match, please verify the confirmation email you received or try sending a new one";
}
```
If the token match, we will simply update the user by setting ``verified`` to true/1  and deleting the token in the database.
```php
$pdo->exec("UPDATE `user` SET `verified` = 1, `token` = NULL WHERE `id` = {$user->id}");
```
I didn't use a *prepared statement* here (***gasp!***), but we didn't really need to, since we're not passing data that comes from an external source but the id of the record we got from the database. I used the ``exec()`` function that allows you to simply execute a plain query when you don't need to pass parameters. You can totally use a *prepared statement* on this, though. Maybe you should, actually.

That's it for all the logic part, now we have to display the result to the user. In the content part of your file, do a test on ``$error``, and display the error message if something failed, or a success message if everything is ok :

```html
<?php 
if($error !== "") {
  ?>
<p style="color:red;"><?php echo $error; ?></p>
<p style="color:grey;"><small>Troubles with you account verification ? <a href="resend_confirmation.php" title="Send a new confirmation email page">Try sending a new email verification code</a>.</small></p>
  <?php
}
else {
  ?>
<p style="color:green;">Congratulations!
  <br>Your account is now active, you can log in YourSuperWebsite
  <br><a href="login.php" title="YourSuperWebsite login page" class="btn btn-info">Login page</a>
</p>
  <?php
}
?>
```

## Sending a new verification email
As you can see, in the error part displayed on to the user, I've added a link to a **resend_confirmation.php** page. This page would allow the user (that have not validated their email adress) to request a new validation email, because the first email got lost.

This page will be a mix of the **register.php** and **complete_registration.php** page, it will have a short form with only an email field to be filled, and in the server-side of thing we will do some checks :
* does the email address exists in the database and retrieve the associated user
* has the used already validated their email

If everything is ok, the script will generate a new token for the user, update the database record accordingly, and send the user a new validation email.

I'm not going into details for this one, since there's nothing really new and by now you should be competent with forms, how to process their data, do database requests and send an email. You can look at the provided source code, or better yet, try to create it yourself. Think of it as a logic puzzle ;) 

So what do you need to do in this page ?
* First of course, start the session and include your required scripts.
* Check if the user is logged
  * if they are, redirect them to **index.php**
* Create a form in the content part with just an email input
  * and declare a ``$email`` variable where you get the ``$_POST["email"]`` value and set it do ``""`` by default, as well as an empty ``$error`` variable
* Check if the form is posted
  * Check if email is empty, or set up an error message
    * retrieve the user from the database corresponding to the email
    * If the user doesn't exists, set up the error
      * Or check if they have validated their email already
      * If they have, set up the error
        * Or :
          * Generate a new token
          * Update the user in the database with the new token
          * Send an email
          * Set up that the email has been sent in the session
* Display the error message with the form
* Or the confirmation message that the email has been sent again