
# Going further : cookies
In this chapter we'll see how to handle cookies so the user can stay logged in for a longer time

## Remember me and session lifetime
The "Remember me" option is pretty common in login forms. It allows your users to be logged automatically after their session has expired. Sessions have a lifetime, which is set up in the ``php.ini`` configuration file on the webserver, with the ``session.cookie_lifetime`` config. The time is in seconds, and the default value is ``0``, which means "until the browser is closed". You can actually set up at runtime that value when calling ``session_start()`` by passing an array containing a ``cookie_lifetime`` property, again in seconds.

So when the lifetime condition is met, the session expires and is wiped. In our case that would mean the user would be redirected to **index.php** or **login.php** depending on the page they are visiting.

To bypass that, you can add the "Remember me" option with a checkbox on the login form (it's better to leave it unchecked when the form is first served to the user) :
```html
<input type="checkbox" name="rememberme" id="rememberme" value="rememberme"> <label for="rememberme">Remember me</label>
```
You can check if the user checked the box by testing if ``$_POST["rememberme"]`` is set in the ``$_POST`` *superglobal*. If the user didn't check the box, the property will not be present.

If they have checked the box, we will set up a ***cookie*** containing some information that will help us regenerate the session automatically. Don't ever store passwords, especially in plain text, in your cookies. Actually you shouldn't store information in plain text, because cookies aren't really secure. But what we can do is store a hash of some info that we need and we could use in a SQL request.

Cookies need a name and optionnaly a set of options like a value, a lifetime in seconds, or a path and domain where they are active for. 

For the value, we will store a hash of a string combining the ``id`` and ``email`` of the user. Disregarding everything I have said so far, we will actually use ``sha1`` to hash our string, because we need the same hash method to be available in mySQL, you will see why. Also we're hashing something that would be unique to our system so the probability the unhashed value corresponding to the generated string is available somewhere in the internet is pretty much zero. If you are a little frisky on this, you can use sha256, but I think it requires a plugin installation for mysql.

You can add this in the part where you set up the session in **login.php** :
```php
if(($_POST["rememberme"] ?? "") !== "") {
    $str = sha1("{$user->id}-{$user->email}") //for a little more security you can also add a static private key that you will need to define somewhere in your files
    $duration = 3600 * 24; //One hour in seconds by 24 for a dat, multiply that by the number of days you want your cookie to be alive
    setcookie("yourwebsitecookie", $str, $duration, "/"); //the / makes it active for your website
}
```
Keep in mind that when a cookie is set up in php, it will come alive at the next page load.

Now that our cookie is set up, we have to use it, and it needs to happen on all of our pages. Since we don't like code duplication, we will store the routine to check the cookie and regenerate the session in the **config.inc.php** script. There's a few pages in the tutorial sources where we do the session check before including that script, so put the inclusion before the session check in those. Cookies are handled through the ``$_COOKIE`` *superglobal*, you know how it works now.

In **config.inc.php**, after the DB setup, first check if the session is alive. If not, check if the cookie exists, and try to fetch the user from the database. If you find an user, regenerate the session :
```php
if(!($_SESSION["logged"] ?? false)) { //The user is not logged, check the cookie
    $cookie = $_COOKIE["yourwebsitecookie"] ?? null; //get the correct cookie
    if($cookie !== null) { //The cookie exists
        $stmt = $pdo->prepare("SELECT * FROM `user` WHERE SHA1(CONCAT(`id`, '-',`email`))) = ?"; //CONCAT allows you to glue db fields, strings, numbers... into one string, and SHA1 hashes the result - Please note that I used regular single quotes around the dash
        $stmt->execute([$cookie]);
        $testuser = $stmt->fetch(\PDO::FETCH_OBJ);
        if($testuser !== false) { //the user is found
            //Regenerate the session
            $_SESSION["logged"] = true;
            $_SESSION["firstname"] = $testuser->firstname;
            $_SESSION["lastname"] = $testuser->lastname;
            $_SESSION["email"] = $testuser->email;
            $_SESSION["id"] = $testuser->id;
        }
    }
}
```