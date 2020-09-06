# Going further : User roles and permissions
In this chapter, we'll talk a little about how to differenciate your users and allow different roles, like administrators, moderators, simple users and such.


## User roles and permissions
In this tutorial, the only tricky part we added was forcing the users to verify their email, and as you can see, we didn't let the users to log in if they didn't. In the end, every user has the same rank. I don't know how you intend to use your shiny new user management, but my guess is that you want to be able to access an administration dashboard as an admin, but not the other regular users.

To this end, you need to be able to tell if an user is an administrator or a regular user, or even have multiple ranks (like moderators that have access to certain functions on top of the regular users, but not administrator functions).

You could set up multiple tables for each type of user, but what happens when you want to promote/demote users ? This isn't viable. There's a few solutions achieving what you want : you can either add several fields in the ``users`` table like the ``verified`` field and set each to 0 or 1 (like one for is_administrator, or is_moderator), or you can go the hard way by setting a ``roles`` table, and junction table between ``users`` and ``roles`` where each record will store an user id and a role id.

For example, let's imagine a ``band`` database, where we have a ``musicians`` table, and a ``roles`` table. In our ``musicians`` we have 1-Josh, 2-John, 3-Dave and 4-Alan. In our ``roles``, we have 1-"Singer", 2-"Backup vocals", 3-"Guitar player", 4-"Bass player", 5-"Drummer", 6-"Writer". 

To link each one with their corresponding roles, we crate a ``musician_role`` table, which only has two fields : ``musician_id`` and ``role_id``. Josh sings, plays guitar and writes songs, so he would have three records in that table, Alan is a guitar and bass player, two roles for him, John is a bass player and provides backup vocals, and Dave beats the shit out of his drums while screaming in a microphone. So our table would look like this :
```
| musician_id | role_id |
-------------------------
|      1      |    1    | (Josh/singer)
|      1      |    3    | (Josh/guitar)
|      1      |    6    | (Josh/writing)
|      2      |    2    | (John/vocals)
|      2      |    4    | (John/bass)
|      3      |    2    | (Dave/vocals)
|      3      |    5    | (Dave/drums)
|      4      |    2    | (Alan/vocals)
|      4      |    3    | (Alan/guitar)
```
Now, you can fetch for each musician their corresponding roles list, and vice-versa. For example you could be on the "Guitar player" page, and see that both Josh and Alan fit that role.

## How does that translate to our website
Now that you have the theory, you can create a ``roles`` table to fill with whatever you fancy, and a juction table ``user_role``, and allocate each user to their role.

When a user logs in, your perform another request to the database where you will fetch all the roles corresponding to the user by using the SQL ``JOIN``. This allows you to join two tables on a pivot field (or multiple pivots) common to each table, and get the results from one or both table. It's handy in that case to give your tables an alias so the query will be less a chore to write, and especially give aliases to fields that have the same name in both table.

In our example above, let's say you want all the roles that Josh has, ordered by the role name, the SQL query would look something like this :
```sql
SELECT r.* FROM `roles` AS r INNER JOIN `musician_role` AS mr ON (mr.`role_id` = r.`id`) WHERE mr.`musician_id` = 1 ORDER BY r.`name`;
```
Once you've retrieved all the roles of your user, you can store them in the session, and when an user visit a retricted page, on top of simply checking if the user is logged, you can add further restrictions to see if the user has the correct roles to access a page. You can check for page access, but also display certain actions or information according to their roles.

For example, let's say you have setup a ``$_SESSION["role"]`` for an user, where the value can either be "ADMIN" or "USER", and you want to display a delete button on a list, but only for the administrators. The code would look like this :
```html
<?php
if(($_SESSION["role"] ?? "USER") === "ADMIN) {
    ?>
<button type="delete">Delete</button>
    <?php
}
?>
```
If you want to push that to even another level, you can also manage permissions and allocate them to roles, again with a juction table. That would mean that an user has one or several roles, each of them having one or several permissions, and you can check on those when the user tries to do something, still in the session for example.