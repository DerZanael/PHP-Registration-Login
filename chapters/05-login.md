# The login page
Finally, we're actually at the login page, which is probably what your wanted from the beggining.

## How it will work
So for the login page, we will need a form with an email and a password inputs, and we will check the user's inputs against what we have in the database. If you read the previous chapters you should have an idea on how this will happen, but there's a couple of new features that I need to explain.

So let's start, create a **login.php** script, set up the session with ``session_start()``, include your database and HTML scripts, check if the user is already logged by testing ``$_SESSION["posted"]`` and redirect them to **index.php** if needed.

We will need a ``$email``, ``$password`` and a ``$error`` variables, the first two checking the ``$_POST`` inputs with ``""`` as default values, and ``$error`` also set to ``""`` :
```php
$email = $_POST["email"] ?? "";
$password = $_POST["email"] ?? "";
$error = "";
```
The form will look like this :
```html
<form name="login" method="post">
    <label for="email">Email</label>
    <br><input type="email" name="email" id="email" value="<?php echo $email; ?>" required>
    <br><label for="password">Password</label>
    <br><input type="password" name="password" id="password" value="" required>
    <br><button type="submit">Login</button>
</form>
```
Notice that both inputs are set up to required, but only the ``email`` input will display the equivalent php variable, because remember that you don't ever send the password value to the HTML, even if the user fumbled.

Now we will check if the form is posted, and if all fields are indeed filled, or set up the error, so add under the variables set up :
```php
if($_SERVER["REQUEST_METHOD"] === "POST") {
    if(empty($email) || empty($password)) {
        $error = "Fill all fields";
    }
}
```
If the required variables are set up, we will retrieve the corresponding user from the database, and we check :
* does the user exists
* has the user verified their email address
* do the password sent match the password in the database

So first, in the ``else {...}`` clause of the previous check, retrieve the user :
```php
else {
    $stmt = $pdo->prepare("SELECT * FROM `user` WHERE (`email` = ?)");
    $stmt->execute([
        $email,
    ]);
    $user = $stmt->fetch(\PDO::FETCH_OBJ);
}
```
Then check if the user exists by checking it against ``false`` (since that's what ``->fetch()`` would return if no record is found), and optionally against ``null`` and ``empty()``
```php
else {
    if($user === false || $user === null || empty($user)) {
        $error = "No user found in the database"; //Or a generic message
    }
}
```
In the else clause, we will check if the user has verified their email address :
```php
else {
    if(!((bool)$user->verified)) {
        $error = "You haven't verified your email address yet";
    }
}
```
And finally we will check the input password against the database password. For this part, we will need the ``password_verify()`` function. In the ancient times, when the norm was hashing password into md5 or sha1, we would have hashed the input password, and check the result string against the string in the database. So in the same way, you're thinking "well we should do a ``password_hash()`` on the input password and compare the string".

But these are the modern times, and it doesn't work that way, because ``password_hash()`` does not return the same hash every time you execute it on a certain string. You can try it yourself by doing ``password_hash("test", PASSWORD_DEFAULT);`` a couple of times, you won't get the same result. That's where ``password_verify()`` come. It needs a string and a hashed string to compare. 

So do the following :
```php
else {
    if(!password_verify($password, $user->password)) {
        $error = "The password does not match";
    }
}
```
We've tested everything, so don't forget to display the error message in the content part, above the form :
```html
<?php
    if(!empty($error)) {
        ?>
<p style="color:red;"><?php echo $error; ?></p>
        <?php
    }
?>
```

## Setting up the session
Our user exists, their email is validated, the password match, let's actually log them.

We've talked a lot about it and tested on it by now, but we will now really set up ``$_SESSION["logged"]``! Took a while, eh ? 

How we do that is simply assigning indexes and values into ``$_SESSION``, since all our pages use sessions through the ``session_start()`` directive and the *superglobal* is available. You can basically store whatever you want in ``$_SESSION`` (***except passwords***, don't do that), and since nothing is formatted or mandatory, you're free to use whatever index names you want. Just be consistent, this will help in the long run.

So, while we're at it, we will also set up some information from the database, and then redirect the user to the **profile.php** page, since they are now able to visit the rest of the website :
```php
else {
    $_SESSION["logged"] = true; //finally
    $_SESSION["firstname"] = $user->firstname;
    $_SESSION["lastname"] = $user->lastname;
    $_SESSION["email"] = $user->email;
    $_SESSION["id"] = $user->id;

    header("Location:profile.php");
    exit();
}
```

## The profile page
This one will be fairly simple since we're almost done. We're just going to test if the user is logged or not, redirect them if they aren't, and otherwise display their information.

Create a **profile.php**, and you know the drill, use the ``session_start()`` directive, and add your includes. Now the one change is that on the *is the user is logged* check, we will redirect to **login.php** if they aren't :
```php
//You can also test with if(!($_SESSION["logged"] ?? false)) {}
if(($_SESSION["logged"] ?? false) === false) {
    header("Location:login.php");
}
```
And for the content page, we will simply display a message with the user's name retrieved from the session :
```html
<p>Hey <?php echo $_SESSION["firstname"]; ?> <?php echo $_SESSION["lastname"]; ?> ! Your email is <?php echo $_SESSION["email"]; ?> !
    <br><a href="logout.php" title="Log out">Logout</a>
</p>
```

## The logout page
Well, yeah, we now know how to set up a session, but how do you destroy it ? You can use and combine a few ways :
* using the native ``session_destroy()`` function, which works exactly as you would expect
* emptying the ``$_SESSION`` array so the ``logged`` index is not set anymore
* resetting the local session cookie with a negative time to force the browser to delete it

Create a **logout.php** page, start the session as usual, and we will use the first two ways. Then we redirect the user to the login page with an *url parameter* so we can add a notice for the user :
```php
<?php
session_start();
if(!($_SESSION["logged"])) {
    header("Location:index.php");
    exit();
}
$_SESSION = []; //empty the array
session_destroy(); 

header("Location:login.php?logout=true");
```
That's it for the logout page, but we need to update the **login.php** page to handle the notice. At the very top of the content part, add a test on ``$_GET["logout"]`` and display a notice if the parameter is set up :
```html
<?php
if(($_GET["logout"] ?? "") !== "") {
    ?>
<p style="color:green;">You have been successfuly disconnected</p>
    <?php
}
?>
```

## Finalizing the **index.php** page
We set up that page a few chapters ago now, so we need to update it to handle the session. We will add in the content a test on ``$_SESSION["logged"]``, display a link to the **profile.php** page if they are logged, or display the already existing links to **login.php** et **register.php** :
```html
<?php
if($_SESSION["logged"] ?? false) {
    ?>
<p>Welcome, <?php echo $_SESSION["firstname"]; ?> <?php echo $_SESSION["lastname"]; ?> !
    <br>Go to <a href="profile.php" title="Your profile">your profile</a>
</p>
    <?php
else {
    ?>
<p>Welcome to Your Super Website!
    <br><a href="login.php" title="Login">Login</a> or <a href="register.php" title="Register">Register</a>
</p>
<?php
}
?>
```

Aaaaaaannnndddd... we're done. But this was a simple (yet lengthy) tutorial for a really simple way to handle and log your users. As with everything in development, there's always room for improvement (you can actually check that in the sources commits), and we will talk about that in the last chapter.

[Chapter 6 : Going further](06-going-further.md)