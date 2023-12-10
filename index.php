<?php

require "includes/helpers.php";

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Manager</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <h1>Password Manager</h1>
    </header>
    <form id="clear-results" method="post"
        action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <input id="clear-results_submit-button" type="submit" value="Clear Results">
    </form>

<?php
require_once "includes/config.php";
require_once "includes/helpers.php";

define("NOTHING_FOUND",  0);
define("SEARCH",         1);
define("UPDATE",         2);
define("INSERT",         3);
define("DELETE",         4);

$option = (isset($_POST['submitted']) ? $_POST['submitted'] : null);

#Switch case to call helper functions and check to ensure required elements aren't empty;
if ($option != null) {
    switch ($option) {
        case SEARCH:
            if ("" == $_POST['search']) {
                echo '<div id="error">Search query empty. Please try again.</div>' .
                    "\n";
            } else {
                if (NOTHING_FOUND === (search($_POST['search'], $_POST['search_param']))) {
                    echo '<div id="error">Nothing found.</div>' . "\n";
                }
            }

            break;

        case UPDATE:
            if ((0 == $_POST['new-attribute']) && ("" == $_POST['pattern'])) {
                echo '<div id="error">One or both fields were empty, ' .
                    'but both must be filled out. Please try again.</div>' . "\n";
            } else {
                update($_POST['current-attribute'], $_POST['new-attribute'],
                    $_POST['query-attribute'], $_POST['pattern']);
            }

            break;

        case INSERT:
            if (("" == $_POST['Website_Name']) || ("" == $_POST['Website_URL']) || ("" == $_POST['Comment'])
            || ("" == $_POST['User_Name']) || ("" == $_POST['Email_Address']) || ("" == $_POST['Password_'])) {
                echo '<div id="error">At least one field in your insert request ' .
                     'is empty. Please try again.</div>' . "\n";
            } else {
                insert($_POST['Website_Name'],$_POST['Website_URL'],$_POST['Comment'],
                $_POST['User_Name'],$_POST['Email_Address'],$_POST['Password_']);
            }

            break;

        case DELETE:
            if (("" == $_POST['current-attribute']) || ("" == $_POST['pattern'])) {
            echo '<div id="error">At least one field in your delete procedure ' .
                 'is empty. Please try again.</div>' . "\n";
        } else {
            delete($_POST['current-attribute'], $_POST['pattern']);
        }

        break;

    }
}

#HTML forms for different functions
?>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
        <legend>Delete</legend>
        DELETE FROM student_passwords WHERE
        <select name="current-attribute" id="current-attribute">
            <option>Website_Name</option>
            <option>Website_URL</option>
            <option>User_Name</option>
            <option>Email_Address</option>
            <option>Password</option>
        </select>
        = <input type="text" name="pattern" required>
        <input type="hidden" name="submitted" value="4">
        <p><input type="submit" value="delete"></p>
    </fieldset>
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
      <legend>Insert</legend>
      INSERT INTO student_passwords VALUES <br>( <input type="text" name="Website_Name" placeholder="Website_Name" required>, <input type="text" name="Website_URL" placeholder="Website_URL" required>,
      <input type="text" name="User_Name" placeholder="User_Name" required>,
      <input type="text" name="Email_Address" placeholder="Email_Address" required>, <input type="text" name="Password_" placeholder="Password" required>);
      <br><br> <textarea id="Comment" name="Comment" rows="4" cols="50">Enter comment here... </textarea>
      <input type="hidden" name="submitted" value="3">
      <p><input type="submit" value="insert"></p>
    </fieldset>
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
        <legend>Search</legend>
        <select name="search_param" id="search_param">
            <option>Website_Name</option>
            <option>Website_URL</option>
            <option>User_Name</option>
            <option>Email_Address</option>
            <option>Password</option>
        </select>
        <input type="text" name="search" autofocus required>
        <input type="hidden" name="submitted" value="1">
        <p><input type="submit" value="search"></p>
    </fieldset>
    </form>

    <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <fieldset>
        <legend>Update</legend>
        UPDATE student_passwords SET
        <select name="current-attribute" id="current-attribute">
            <option>Website_Name</option>
            <option>Website_URL</option>
            <option>User_Name</option>
            <option>Email_Address</option>
            <option>Password</option>
        </select>
        = <input type="text" name="new-attribute" required> WHERE
        <select name="query-attribute" id="query-attribute">
            <option>Website_Name</option>
            <option>Website_URL</option>
            <option>User_Name</option>
            <option>Email_Address</option>
            <option>Password</option>
        </select>
        = <input type="text" name="pattern" required>
        <input type="hidden" name="submitted" value="2">
        <p><input type="submit" value="update"></p>
    </fieldset>
    </form>
</html>
