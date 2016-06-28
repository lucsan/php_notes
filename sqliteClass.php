<?php

/**
 * Test script for implementing sqlite3.
 * I run this file on win 10 git bash command line using php v7.0.
 */


// I just like to know the script fired.
print "running.\n";

// ----- Main program

// Instansiate the sqlite3 class.
$db = new myDb();
// Create a new database or open an existing one.
$db->openDb('test');
// Call exec method passing it create table sql.
$db->execDb($db->createTableSql());

// Call either exec or query methods on an insert sql.
// Note: either comment this out or change the name in getInsertSql() method
// otherwise you will get Unique constraint (set on name in users) error.
//$db->execDb($db->getInsertSql());
$db->queryDb($db->getInsertSql());

// Call query to retrieve all the data in the table.
$result = $db->queryDb($db->getSelectSql()) or die('select failed.');

// pump out the results of the select query.
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
  print_r($row);
}

// ----- End main

/**
 * Running php on git bash cmmand using the class extends is the only way I have
 * found to get it to work.
 * The folowing class demonstraites the basic implementation of this technique.
 */
class myDb extends sqlite3 {
  // Not used in this example, but you might need it for something.
  public $dbHandle;

  function __construct () {

  }

  /**
   * Creates or opens the db file.
   * @param  string $dbName This is the path and file name.
   */
  function openDb ($dbName) {
    $this->dbHandle = $this->open($dbName);
  }

  /**
   * Calls the sqlite exec method on any sql sent to it.
   * @param  string $sql
   * @return boolean      true = success
   */
  function execDb ($sql) {
    // exec does not return result sets.
    return $this->exec($sql);
  }

  /**
   * Calls the query method on any sql sent. Returns a result set if there is one.
   * @param  string $sql
   * @return resultSet
   */
  function queryDb ($sql) {
    // query returns result sets.
    return $this->query($sql);
  }

  // ----- The sql statements used in this example.

  function createTableSql () {
    return "CREATE TABLE IF NOT EXISTS users (
        username STRING PRIMARY KEY,
        password STRING);
    ";
  }

  function getInsertSql () {
    return "INSERT INTO users VALUES ( 'brian', 'secret' );";
  }

  function getSelectSql () {
    return "SELECT * FROM users;";
  }

}
