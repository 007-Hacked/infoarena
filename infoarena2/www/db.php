<?php
/**
 * This module contains various database-related functions and routines.
 *
 * Note: We keep database-persisted "models" very simple. Most of them are
 * simple dictionaries. 
 */

// first, we need a database connection
assert(!isset($dbLink));    // repetitive-include guard
$dbLink = mysql_connect(DB_HOST, DB_USER, DB_PASS)
          or die('Cannot connect to database.');
mysql_select_db(DB_NAME, $dbLink) or die ('Cannot select database.');

// Escapes a string to be safely included in a query.
function db_escape($str) {
    return mysql_escape_string($str);
}

// Executes query. Outputs error messages
// Returns native PHP mysql resource handle
function db_query($query) {
    global $dbLink;
    $result = mysql_query($query, $dbLink);
    if (!$result) {
        // An error has occured. Print helpful debug messages and die
        echo '<br/><br/><hr/><h1>SQL ERROR!</h1>';

        if (IA_SQL_TRACE) {
            echo '<p>' . mysql_error($dbLink) . '</p>';
            echo '<p>This has occured upon trying to execute this:</p>';
            echo '<pre>' . $query . '</pre>';
        }
        die();
    }
    return $result;
}

// Executes query, fetches only FIRST result
function db_fetch($query) {
    global $dbLink;
    $result = db_query($query);
    if ($result) {
        $row = mysql_fetch_assoc($result);
        if ($row === false) {
            return null;
        }
        return $row;
    }
    else {
        return null;
    }
}

// Executes query, fetches the whole result
function db_fetch_all($query) {
    global $dbLink;
    $result = db_query($query);
    if ($result) {
        $buffer = array();
        while ($row = mysql_fetch_assoc($result)) {
            $buffer[] = $row;
        }
        return $buffer;
    }
    else {
        return null;
    }
}

/**
 * Task
 */
function task_get($id) {
    $query = sprintf("SELECT * FROM ia_task WHERE id = '%s'", db_escape($id));
    return db_fetch($query);
}

/**
 * Wiki
 */

// Gets the latest version of a page, or null if the page is missing.
function wikipage_get($name) {
    $query = sprintf("SELECT * FROM ia_page ".
                     "WHERE LCASE(`name`) = LCASE('%s') ".
                     "ORDER BY `timestamp` DESC LIMIT 1",
                      db_escape($name));
    return db_fetch($query);
}

// Do use later.
function wikipage_add_revision($name, $content, $user) {
    global $dbLink;
    $query = sprintf("INSERT INTO ia_page (name, `text`, timestamp) ".
                     "VALUES ('%s', '%s', NOW())",
                     db_escape($name), db_escape($content));
    return db_query($query);
}

/**
 * User
 */
function user_get_by_username($username) {
    $query = sprintf("SELECT * FROM ia_user
                      WHERE username = '%s'", db_escape($username));
    return db_fetch($query);
}

function user_get_by_email($email) {
    $query = sprintf("SELECT * FROM ia_user WHERE email = '%s'",
                     db_escape($email));
    return db_fetch($query);
}

function user_get_by_id($id) {
    $query = sprintf("SELECT * FROM ia_user WHERE id = '%s'", db_escape($id));
    return db_fetch($query);
}

function user_create($data) {
    global $dbLink;
    $query = "INSERT INTO ia_user (";
    foreach ($data as $key => $val) {
        $query .= '`' . $key . '`,';
    }
    $query = substr($query, 0, strlen($query)-1);
    $query .= ') VALUES (';
    foreach ($data as $jey => $val) {
        $query .= "'" . db_escape($val) . "',";
    }
    $query = substr($query, 0, strlen($query)-1);
    $query .= ')';

    $ret = db_query($query);

    if ($ret)
    {
        $last = mysql_insert_id($dbLink);
        $q2 = sprintf("UPDATE ia_user set `password` = sha1('%s')
                       WHERE `id` = '%s'",
                      $data['password'], $last);
        db_query($q2);
    }
    return $ret;
}

function user_update($data)
{
}

/**
 * Attachment
 */
function attachment_get($name, $page) {
    $query = sprintf("SELECT * FROM ia_file
                      WHERE LCASE(`name`) = LCASE('%s') AND LCASE(`page`) =
                      LCASE('%s')", db_escape($name), db_escape($page));
    return db_fetch($query);
}

function attachment_update($name, $size, $page, $user) {
    global $dbLink;

    $query = sprintf("UPDATE ia_file SET size = '%s', user ='%s',
                      `timestamp` = NOW() WHERE LCASE(`name`) = LCASE('%s') AND
                      LCASE(`page`) = LCASE('%s')", db_escape($size),
                     db_escape($user), db_escape($name), db_escape($page));
    return db_query($query);
}

function attachment_create($name, $size, $page, $user) {
    global $dbLink;
    if (!attachment_get($name, $page)) {
        $query = sprintf("INSERT INTO ia_file (name, page, size, user, `timestamp`)
                          VALUES ('%s', '%s', '%s', '%s', NOW())", 
                          db_escape($name), db_escape($page), db_escape($size),
                          db_escape($user));
    }
    else {
        return attachment_update($name, $size, $page, $user);
    }

    db_query($query);
    return mysql_insert_id($dbLink);
}

function attachment_delete($name, $page) {
    global $dbLink;
    $query = sprintf("DELETE FROM ia_file WHERE
                      LCASE(`name`) = LCASE('%s') AND LCASE(`page`) =
                      LCASE('%s') LIMIT 1", db_escape($name), db_escape($page));
    return db_query($query);
}

function attachment_get_all($page) {
    $query = sprintf("SELECT * FROM ia_file LEFT JOIN ia_user ON
                      ia_file.user = ia_user.id WHERE
                      LCASE(ia_file.page) = LCASE('%s')
                      ORDER BY ia_file.`timestamp` DESC", db_escape($page));
    return db_fetch_all($query);
}

?>