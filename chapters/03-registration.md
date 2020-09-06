# The registration page
Now let's get rocking. First of all, create a ``registration.php`` page, start the session, include the ``connec.inc.php``, ``header.inc.php`` and ``footer.inc.php`` (basically use what we have in ``index.php``), and for the content part we'll create a simple form later.

Then, we'll check if the user is already logged, and redirect them in that case. No point in being able to view the page when already logged right ? So, right after the ``session_start()``, start your script with :
```php
if($_SESSION["logged"] ?? false) {
    header("Location:index.php");
    exit();
}
```
As said in the introduction, the ``"logged"`` attribute of ``$_SESSION`` will be used to check if our user is logged. We'll set that up to ``true`` in the login page, so for now bear with me on that.

The *null coalescent operator* ``??`` in ``$_SESSION["logged"] ?? false`` is a shorthand to check if that property is set up in ``$_SESSION`` (which would be empty of any association by default). If not, the variable is set up to a specified value. Basically that would a shortcut for something like :
```php
if(isset($_SESSION["logged"])) {
    $islogged = $_SESSION["logged"];
}
else {
    $islogged = false;
}
//or with a ternary operator:
$islogged = (isset($_SESSION["logged"])) ? $_SESSION["logged"] : false;
```
***Attention 5.6 users*** (shame on you, boo) : the ``??`` operator is not available for you, use the above test with the ternary operator instead.

The ``header("Location:index.php");`` sends an HTTP header to the browser telling it that the princess is in another file. Basically it's a redirection.

The ``exit();`` part stops the script execution so nothing will be sent after the ``header()`` and you won't have a dirty php error, since the redirection does not like to have rendered content after. 

## How do forms and php interact
HTML forms are declared with the ``<form>`` tag. Forms have a method (usually ``POST``), and an action property. The action can be any script your fancy, and if not specifed, the default value is the current script url.

In php, when a script is called through the ``POST`` method, you can find the sent data within the ``$_POST`` superglobal, very much like ``$_SESSION``. You will get an associative array with all the inputs' *name property* associated with their values in the form data. Keep in mind that inputs declared *outside* of the ``<form>...</form>`` won't be part of the POST data.

## The registration form
So what do we need for our form ? In our database table ``user``, we have a few fields that need to be filled, since they are not nullable : ``id``, ``firstname``, ``lastname``, ``email``, ``password``, ``verified`` and ``registration_date``. But actually, ``id`` will be set automatically, ``verified`` and ``registration_date`` will be set with php (but the date could be set automatically too).

In the content part of the ``registration.php`` we'll put the html form, in which each input will have a type (text or email in our case), a name, an id (to inspect the field with javascript) and the ``required`` attribute for client-side validation. For good practice, each input will be associated with a ``<label>`` for accessibility. In the sources you'll notice I used bootstrap, you don't need to do that, it's eye candy, so for the tutorial code I'll stick with the bare html instructions.

For ``firstname`` and ``lastname``, we'll be using ``text`` for the input type, ``email`` for the email, and ``password`` for the password (duh). The ``email`` type comes with client-side verification that the user inputs an actual email instead of "gjklvhfjkh", and the ``password`` type will hide the characters typen ;) We won't set an ``action`` for the form since we want the same page to handle the POST data.

Also we're going to duplicate the password field into two, in order to force the user to confirm their password.

```html
<h1>Registration</h1>
<form name="registration_form" id="registration_form" method="post">
    <label for="firstname">First name</label>
    <br><input type="text" name="firstname" id="firstname" placeholder="Your first name" value="" required>
    <br><label for="lastname">Last name</label>
    <br><input type="text" name="lastname" id="lastname" placeholder="Your last name" value="" required>
    <br><label for="email">Email</label>
    <br><input type="email" name="email" id="email" placeholder="Your email address" value="" required>
    <br><label for="password1">Password</label>
    <br><input type="password" name="password1" id="password1" placeholder="Your password" value="" required>
    <br><input type="password" name="password2" id="password2" placeholder="Confirm your password" value="" required>
    <p id="password_message" style="display:none;">The password do not match</p>
    <p><button type="submit">Create account</button></p>
</form>
```
Cool form. The ``required`` attributes will ensure the user can't submit the form without filling the fields, and the ``email`` type will also help for validation. What about the password you ask ? Well, we don't have a client side validation to make sure the passwords match, but fortunately we have our friend Javascript for that.

If you're not familiar with javascript, it's a language used to perform a lot of tasks on the client side of things, or interact with the user through their browser. We will be using that to check when the form is submitted that password1 = password2. 

I won't bother going into details, but the gist of it is : when the form is submitted, we'll look into the value of ``password1`` and ``password2``. If they don't match, we'll prevent the form to be submitted and show the ``password_message`` block, otherwise the POST request will start.

Add this after your form, inside a ``<script>...</script>`` tag :
```javascript
const pass1 = document.getElementById("password1"); //the password1 input
const pass2 = document.getElementById("password2"); //the password2 input
const pass_msg = document.getElementById("password_message"); //the password error message
/**
 * Check if password1 and password2 match
 * and display the error message
 * @return bool true = ok ;)
 */
function checkPwd() {
  let pwd_match = (pass1.value != "" && pass1.value === pass2.value); //will evaluate to true if everything is ok
  pass_msg.style.display = (pwd_match) ? "none" : "block"; //display the error message if the passwords don't match

  return pwd_match;
}
//On page load
document.addEventListener("DOMContentLoaded", function(){
  document.getElementById("registration_form").addEventListener("submit", function(evt){ //On form submit, evt: the actual submit event
    pass_msg.style.display = "none";
    if(!checkPwd()) { //Passwords do not match, we stop the process
      evt.preventDefault();
    }
  });
  pass1.addEventListener("keyup", function() { checkPwd(); }); //check passwords on type
  pass2.addEventListener("keyup", function() { checkPwd(); }); //check passwords on type
});
```

Now we've done everything we can on the client-side and we're going to process the post data. Since I don't trust users (protip : never trust the user), I'll also perform some server-side verification for the data sent. 

## The php side
First of all, we want to check if the form has been posted. There's a few tricks for that, like setting an hidden input in your form and check if the ``$_POST`` superglobal has an input for that. It can be useful if you have multiple forms pointing to the same php script. In our case, we'll use the ``$_SERVER`` superglobal and its property ``REQUEST_METHOD``, and test if it's equal to **"POST"**. I'll put the result in a variable because I'll use it later.
```php
$is_posted = ($_SERVER["REQUEST_METHOD"] === "POST");
```
As you can see, I didn't go through a ``if...else`` condition, but instead tested the result between  ``()`` brackets. It's another handy shorthand you should know when you want to store the *boolean result* (so *true* or *false*) of a conditionnal test into a variable. In the same way you have the ternary operator which allows you to set up a string, or call a function into your variable, like this :
```php
$is_posted = ($_SERVER["REQUEST_METHOD"] === "POST") ? strtoupper("form has been posted") : "form hasn't been posted";
//Or alternatively if you have only ONE instruction to do in your if...else
($_SERVER["REQUEST_METHOD"] === "POST") ? echo "it's posted" : var_dump($_SERVER);
```
The ```===``` triple equivalence allows you to check if something is really really really equal to something else, down to the type of both parts of the equivalence. In php for example ``false``, ``0``, ``""``, ``[]`` and ``null`` could be "equal" if checked with a ``==`` double equivalence. There could be some cases when you could set a variable to ``null`` or ``0``, so it's important to know which of those it is. There's also a few functions in php that can return multiple types of data, which is one of the reason why php has a bad reputation among some developers.

### Getting the POST data
This is not mandatory, but I like to fetch my data beforehand into variables and set them up before checking if a form has been posted. "Is this guy drunk? *Before* checking the post??" Well yeah, because I'm going to use those in the HTML form to fill the ``value`` attribute of each input, so the user doesn't have to fill them again if there's been an error. If the variables don't exist, I can't use them, or have to declare them twice. It will make sense.

Basically for each data I'm getting from the POST data, I'll set up a variable, and set it to ``""`` by default with the ``??`` we've seen earlier for the session. That way the variables are set up and can be used or manipulated/cleaned afterwards. I'm going to name the variables with the same name as their corresponding input, but you should know you can name them whatever you want.

You could also do an ``extract($_POST);`` which would create a variable for each index in ``$_POST``, but I want to set up default values for the variables.

Add this in your file :
```php
//POST status
$is_posted = ($_SERVER["REQUEST_METHOD"] === "POST");
//Set up each variable
$firstname = $_POST["firstname"] ?? "";
$lastname = $_POST["lastname"] ?? "";
$email = $_POST["email"] ?? "";
$password1 = $_POST["password"] ?? "";
$password2 = $_POST["password2"] ?? "";
//or do an extract($_POST);

if($is_posted) {
    //Form is posted, do things
}
```

### Server side verification
Users lie. They cheat. They deceive. They exploit vulnerabilities. Or they somehow fuck everything up unwillingly and manage to send incomplete data. *It's your job* to make sure you get everything you need. We put client-side verification in our form, but some clever/stupid ass could be able to post it anyway even with nothing but empty fields.

So, before inserting our user in the database, we will perform multiple verifications :
* has the user filled everything
* do the password match
* is the email adress already in the database

Every test will go in the *is the post submitted* test on ``$is_posted`` and if one of the test fail, we will display the form again with an error message.

Introducing ``$error``, which we will set to empty ``""`` by default before checking the request method. This little variable will help us see if the user has done something wrong.

We will first check each POST variable to see if it's empty (``""``) or not. You could do a basic ``if($var1 === "" || $var2 === "" || ...)``, but where would be the fun in that ? We're going to use *pointers* because we can (that might not be the best solution in terms of performance though). 

Pointers are available by using a ``$$`` double dollar instead of a single one in a variable ``$a``, that will point to a ``$b`` variable which name is the same as ``$a``'s value, in order to return ``$b``'s value. At this point you're rolling your eyes and wonder why we don't look at ``$b``'s value directly or wondering what that nonsense sentence was, so let's see an example, and why we're going to do that :
```php
$a = "b";
$b = "yo";
echo $a; //prints "b"
echo $b; //prints "yo"
echo $$a; //prints "yo" because you're requesting "the value of the variable whose name is the same as this"
echo $$b; //probably throws an error because there's no variable $yo declared
```
Now that it should be clearer, we'll create an array with all the *required* inputs' names (or their respective php variable name), loop on it, and see if the associated variable is empty or not. If one of them is indeed empty, we'll set a message in ``$error``. For clarification : I'm creating an array so I can loop on it and add new inputs later, in the case the form has to evolve and add new required fields, like birth date or whatever.

The first test will look like this (remember, in the ``if($is_posted)){...}`` part) :
```php
$checks = ["firstname", "lastname", "email", "password1", "password2"]; //all the variable names that should be tested
foreach($checks as $check) { //we loop on each of them
    if(empty($$check)) { //woops, the variable is empty
        $error = "Fill all fields";
    }
}
```
The ``empty()`` function checks the value of the passed variable and will return true when the variable is either ``""``, ``null``, an empty array or for ints ``0`` (watch out for that). 

We also need to check that ``$password1`` and ``$password2`` match, which is fairly easy. Add that after the first test :
```php
if(!empty($password1) && $password1 !== $password2) {
    $error = "The passwords do not match";
}
```
Now, what happens if something goes wrong and ``$error`` is not null anymore ? We will add a message before the ``<form>`` :
```html
<?php
if($error !== "") {
    ?>
<p style="color:red;"><?php echo $error; ?></p>
    <?php
}
?>
```
If you're up to it, you can even make ``$error`` an array and store/display the multiple problems that happened in the POST request handling, but we will keep it simple. However, since we're handling server-side errors, when one occurs, it's a nice gesture to fill what the user has submitted in the form so they won't have to fill the fields again (don't you hate it when a form is empty when something went wrong ?). So for each of your input fields in the form, in the value property, do an echo of the corresping variable, like this :

```html
<input type="text" name="firstname" id="firstname" placeholder="Your first name" value="<?php echo $firstname ?>" required>
```
Since we set up a default value for all the input variables outside of the check on ```$is_posted```, we don't have to worry that php will throw a notice like "Undefined variable $variable on line xxx".

***Don't do echoes on the password variables*** though. Force the user to re-type them.

Ok, so the user has filled all the inputs, and the password match, time to check if the user already exists in the database, we will do a simple request to check if the ``$email`` is already stored. And we only be doing that only if ``$error`` is empty. First : make a COUNT request on the ``user`` table where the ``email`` fields is equal to the ``$email`` submitted. If the count is equal to 1 or superior (which shouldn't happen for the latter), the email is already in use and we stop the process. Do that after the previous checks, still in the ``$is_posted`` test :
```php
if($error !== "") {
    $stmt = $pdo->prepare("SELECT COUNT(`id`) FROM `user` WHERE `email` = ?");
    $stmt->execute([$email]);
    $test = $stmt->fetchColumn(); //fetchColumn() is useful when you have one column to fetch from a result
    if($test > 0) { //If we have at least one result, the test fails
        $error = "This email already exists in the database";
    }
    else {
        //Alright, everything is ok for sure now
    }
}
```
Please note that, for security reasons, you might not want to expose *the fact* that the email address exists in your database, since that could lead ill-intended individuals to exploit that fact and try to do some phishing campaign against your users. So you might want to give a generic message like "invalid email address" instead.

If everything goes ok, we're going to do the actual insert in the ``else{}`` clause of our test and create the record.

### Hashing the password

First, we will hash the user's password because, remember, ***never store a password as a plain value***. For this, the native php function ``password_hash()`` works wonders. It needs a value to hash, and an hashing algorithm. You can actually use the default hashing algorithm, which is determined by the php version used (currently ``bcrypt``). If you have access to ``argon2i`` or ``argon2id``, you can use that. You can also pass some options that will add new layers of complexity if you wish. Check the official documentation about the hashing options.

Let's hash the password (remember, in the ``else{}`` clause of *"is the email already in the database"*) :
```php
$password = password_hash($password1, PASSWORD_DEFAULT); //final hash that will be stored in the database
```
Then, we will create a token to be sent to the user so they can verify their email address. We will also create a timestamp with the ``Datetime`` *Class*, and set the ``verified`` field to 0. ``Datetime`` can be used to create an *date* object at the current time, that we can manipulate and format afterwards. The token will be created with random characters and tweaked a little. We just need a complicated string with no other meaning :
```php
$token = str_replace(["+", "/", "="], "", base64_encode(random_bytes(20))); //cool token that will look like mcw11UIj9AEnoacnbTi7kUkX7j0
$time = new \Datetime("now"); //manipulable date object at the current time
```
``random_bytes()`` creates a random cyrptosecure string of the specified length, ``base64_encode()`` will ensure the data is readable and usable in an url, and we replace certain characters that are not url-friendly with ``str_replace()``.

Finally, we create the PDO statement, and execute it. But we're going to do that in a ``try...catch`` test in case something goes wrong. Again, don't expose the errors in a production environment.
```php
//DB insert
try {
    $stmt = $pdo->prepare("INSERT INTO user (`email`, `password`, `firstname`, `lastname`, `verified`, `token`, `registration_date`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $email, //comes from the form
        $password, //the hashed password
        $firstname, //comes from the form
        $lastname, //comes from the form
        false, //this will be stored as 0 in the database since the db field is an integer - if you get errors, change to 0
        $token, //our generated random string
        $time->format("Y-m-d H:i:s"), //This format the current date as the standard SQL datetime format, something like 2020-01-01 13:00:00
    ]);
}
catch(\PDOException $e) { //If something goes wrong
    print_r("<pre>Failed to execute user insert :
        ".$e->getMessage()."</pre>");
    die();
}
```
The user is now created in the database, and they have to validate their email address with their token, so let's send them an email. PHP comes with a built-in function for that : ``mail()``, which will need a few informations : the recipient address, the email subject, the email body, and optionally some headers. In our email body, we will need an url the user can access to validate their token. In this tutorial we will pass the email address and token as ***URL parameters*** that will be read by the endpoint php script.

The ``mail()`` function returns a boolean, so you can test the returned value to see if you email has been sent. We will add a few headers in the email : the sender's address, the mailer identity, and specify we're using an UTF8 charset.

Add this following the ``try...catch`` user insertion in the database (we don't have to worry an email will be sent while the insertion failed since we killed the script with ``die()`` in that case) :
```php
//Validation email
$validate_url = "https://your-super-website.com/complete_registration.php?email={$email}&token={$token}"; //Validation url that users have to click
//We will put simple HTML in the email body with a few information from the form
$email_content =
"<p>Hello {$firstname} {$lastname}</p>,
<p>You registered on YourSuperWebsite on {$time->format("d/m/Y H:i")}.</p>
<p>In order to complete your registration, please click on the link below, or copy and paste it on your browser navigation bar :
  <br><a href='{$validate_url}' title='Validate your registration on YourSuperWebsite'>{$validate_url}</a>
</p>
<p>Best regards,
  <br>YourSuperWebsite Team
</p>";
//We wend the email and store the result in a $email_sent variable we will use later
$email_sent = mail(
    $email,
    "YourSuperWebsite registration : please check your email",
    $email_content,
    [ //Email headers
        "From" => "no-reply@your-super-website.com",
        "X-Mailer" => "PHP/".phpversion(),
        "Content-type" => "text/html; charset=UTF-8",
    ]
);
```
### A quick note about ``mail()``
***If you are using a php version below 7.4*** : this will fail, because passing the headers as an array is only available since php7.4. If you are using an earlier version of php, you have to pass a string where each header is separated by a ``\r\n`` CLRF character for a new line.

***If you are developping on Windows*** : bad luck, windows does not come with a regular smtp server, and sending emails with that OS can be tricky. I don't have a solution at hand, but there are probably plenty of help on stackoverflow or even on the php documentation on that issue. Xampp comes with a mail server, but I've never used it, try it and let me know :D

## Finishing touches
The user is recorded in the database, and we tried to send their email. Now is the time to complete our registration process. What we want to do is show a success message to inform the user they are done, and most importantly, ***we really don't want the user to reload the page and send their form again***. So we will redirect them to the same page, but with the redirection, the *POST request* will be invalidated, and even if they mash F5, nothing will happen.

For this, we will actually use sessions to store the success state of the registration, but first we will check if the email has been sent. So right after the ``$email_sent = mail(...);`` part, check the value of ``$email_sent``, and if it failed, store something in the session, you'll see later why :
```php
if(!$email_sent) {
    $_SESSION["email_sent"] = false;
}
```
Now, store the info that the registration is complete, and redirect your user to the same page :
```php
$_SESSION["registration_complete"] = true;
header("Location:register.php");
exit(); //End of the registration process
```
Finally, in the content part, above the error message display and the form, check the value of ``$_SESSION["registration_complete"]``. In this part we will display a message that the user has been successfully recorded and that they have to validate their email address. We will also show a message if the email couldn't be sent. Since we're testing a value of the session that is ONLY set if the db recording has been done, this message will never show on the form the first time the user visit the page or has an error on the form. And at the end of that test, we will unset the ``$_SESSION`` properties so the message will not show up again.

If the email has failed, we will show a link to try to resend the validation email.
```html
<?php
if($_SESSION["register_complete"] ?? false) { //Registration successfull, yay
  ?>
  <p style="color:green;">Your registration was successful !
    <br>
    <?php
    if($_SESSION["email_error"] ?? false) { //Woops, the email failed
      ?>
       Unfortunately, the confirmation email could not be sent, please contact an administrator, or try sending <a href='resend_confirmation.php' title='Send a new confirmation email'>a new confirmation email</a>.
      <?php
    }
    else { //Evrything is good
      ?>
      A confirmation message has been sent to your email address, please follow the link enclosed to complete your registration and be able to log in YourSuperWebsite.
      <?php
    }
    ?>
  <?php
  //We remove the session properties that are not needed anymore
  unset($_SESSION["register_complete"]);
  unset($_SESSION["email_error"]);
}
?>
```

And that's a wrap for the registration part. Now let's validate that email address.

[Chapter 4 : Email validation](04-email-validation.md)