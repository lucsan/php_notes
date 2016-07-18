<?php

namespace sqit;

class sqlite extends \sqlite3 {
  private $testing = null;
  private $report = '';
  private $path = null;
  private $dbName = null;
  private $tableName = '';

  public function __construct (String $path = null, String $dbName = null, String $testing = null)
  {
    if ($testing) {
      echo "Creating sqlite [to test {$testing}]", PHP_EOL;
      $this->testing = $testing;
    }
    $this->checkPath($path);
    $this->checkName($dbName);

    if ($this->dbName != null) $this->open($this->path . $this->dbName);

  }

  public function __destruct ()
  {
    if ($this->testing) echo "Destrying sqlite [done testing {$this->testing}]" , PHP_EOL;
  }

  /**
   * Inserts a new row into a table. Format $values thus [['',''],[]].
   * @param  String $tableName   [description]
   * @param  Array  $columnNames [description]
   * @param  Array  $values      [description]
   * @return [type]              [description]
   */
  public function insert (String $tableName, Array $columnNames, Array $values)
  {
     if ($tableName == null && $this->tableName == null) return $this->report('Needs a table name.');
     if ($tableName == null && $this->tableName != null) $tableName = $this->tableName;
     if ($columnNames == null) return $this->report('Needs at least one column.');
     if ($values == null || $values[0] == null) {
       return $this->report('Needs values, must be 2d array. use [[\'\',], []].');
     }
     $this->tableName = $tableName;
     // build insert string.
     foreach ($values as $items) {
       $sql = "INSERT INTO {$tableName} ";
       $sql .= "(";
       foreach ($columnNames as $column) {
         $sql .= "'$column', ";
      }
       $sql = substr($sql, 0, strlen($sql) -2);
       $sql .= ") VALUES (";
       foreach ($items as $item) {
         $sql .= "'$item', ";
       }
       $sql = substr($sql, 0, strlen($sql) -2);
       $sql .= ");";
       //echo $sql, PHP_EOL;
       // Call insert sql on db.
       $this->exec($sql);
    }
  }

  public function update (String $tableName = null, Array $set = null, String $where = null)
  {
    $sql = "UPDATE {$tableName} SET ";
    foreach ($set as $pair) {
      $sql .= $pair . ',';
    }
    $sql = substr($sql, 0, strlen($sql) -1);
    $sql .= " WHERE {$where} ;";

    // update x set (c=v, c=v) where a=b
    $this->exec($sql);

    //echo $sql, PHP_EOL;
  }

  public function createTb (String $tableName = null, Array $values = null)
  {
    if ($tableName == null) return $this->report('Needs table name', false);
    if ($values == null) return $this->report('Needs column values', false);

      $sql = "CREATE TABLE IF NOT EXISTS {$tableName} (";
      foreach ($values as $value) {
        $sql .= "{$value},";
      }
      $sql = substr($sql, 0, strlen($sql) - 1);
      $sql .= ");";
      $this->exec($sql);

// return "CREATE TABLE IF NOT EXISTS users (
//     username STRING PRIMARY KEY,
//     password STRING);
// ";


  }

  // Delete data.
  public function delete (String $tableName = null, Array $values = null)
  {
    if ($tableName == null) return $this->report('Needs table name', false);
    if ($values == null) return $this->report('Needs column values', false);
      // Delete from T where c = i

    foreach ($values as $value) {
      $sql = "DELETE FROM {$tableName} WHERE {$value};";
      //echo $sql, PHP_EOL;
      $this->exec($sql);
    }
  }

  public function destroyTb (String $tableName = null)
  {

  }

  public function destroyDb ()
  {
    $this->close();
    unlink($this->path . $this->dbName);
  }

  private function checkName (String $dbName = null)
  {
    if ($dbName == null) return $this->report('Needs DB name', false);
    $this->dbName = $dbName;
    return $this->dbName;
  }

  private function checkPath (String $path = null)
  {
    if ($path == null) {
      $this->path = ''; // Assumed db at same path level;
      return $this->path;
    };
    // Ensure there is a trailing slash on the path.
    if (substr($path, strlen($path) -1, strlen($path)) != '/') { $path .= '/'; }
    $this->path = $path;
    return $path;
  }



  /**
   * Report an error or warning.
   * @param  String $msg        An error message.
   * @param  Any $testReturn    Anything you want to return instead of the error msg.
   * @param  Boolean $fullTrace  Print a full trace of calling functions.
   * @return Any             Either the report message, or the $testReturn value (if set).
   */
  private function report (String $msg, $testReturn = null, $fullTrace = false)
  {
    $this->report = '';
    $e = new \Exception();
    $trace = $e->getTrace();
    $last_call = $trace[1];
    $report = "Issue: $msg [function {$last_call['function']} line {$last_call['line']}]" . PHP_EOL;
    if ($fullTrace === true) print_r($trace);
    if ($this->testing) echo $report;
    //die($report);
    $this->report = $report;
    if ($testReturn !== null) {
      return $testReturn;
    }
    return $this->report;
  }

  public function printReport ()
  {
    echo $this->report;
  }


  public function testFunctions ($func, $values)
  {
    $result = $this->$func($values);
    //echo $result . PHP_EOL;
    return $result;
  }


}

?>
