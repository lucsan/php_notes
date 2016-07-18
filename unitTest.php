<?php

namespace sqit\ut;

include_once('sqliteClass.php');


ini_set('zend.assertion', '0'); //in development mode
ini_set('assert.exception', '1'); //in development mode


//assert('false /* not implmented */', 'Error no implementation.');

//echo "heeeee";
//
assert_options(ASSERT_CALLBACK, 'Analysys');


// ----- Main
$allFuncs = get_defined_functions();
$theseFuncs = $allFuncs['user'];
//echo "Testing :- " . PHP_EOL;
//print_r($theseFuncs);

foreach ($theseFuncs as $func) {
  if (stripos($func, 'test_') === false) continue;
  echo "Testing: ", $func, PHP_EOL;
  $func();
}

 function test_ClassObject ()
{
  $sqli = new \sqit\sqlite(null, null, 'Class Object');
  assert('is_object($sqli) /* sqli is not an object */');
  $sqli = null;
}


function test_privateFunctions ()
{
  $sqli = new \sqit\sqlite(null, null, 'private functions');
  // Test report

  // Test checkPath.
  $path = $sqli->testFunctions('checkPath', '');
  assert('$path == \'\' /* path is not empty string */');
  $path = $sqli->testFunctions('checkPath', 'databases');
  assert('$path == \'databases/\' /* path is not databases/ */');
  $path = $sqli->testFunctions('checkPath', 'databases/');
  assert('$path == \'databases/\' /* path is not databases/ */');

  // Test checkName.
  $database = $sqli->testFunctions('checkName', '');
  assert('$database === false /* database is not null */');
  $database = $sqli->testFunctions('checkName', 'test.sqlite');
  assert('$database == \'test.sqlite\' /* database is not null */');
  $sqli = null;
}



function test_createDb ()
{
  $path = 'databases';
  $database = 'test1.sqlite';
  $sqli = new \sqit\sqlite($path, $database, 'create DB');
  assert('file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');
  $sqli = null;
}

function test_destroyDB ()
{
  $path = 'databases';
  $database = 'test1.sqlite';
  $sqli = new \sqit\sqlite($path, $database, 'destroy DB');
  assert('file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');
  $sqli->destroyDb();
  assert('!file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');
}

function test_createTb ()
{
  $path = 'databases';
  $database = 'test1.sqlite';
  $table = 'test';
  $values = ['name STRING PRIMARY KEY','stuff STRING'];
  $sqli = new \sqit\sqlite($path, $database, 'create DB');
  assert('file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');
  // Test empty function call.
  $create = $sqli->createTb();
  assert('$create === false /* sqli path is not string */');
  $create = $sqli->createTb($table, $values);
  $result = $sqli->query('SELECT * FROM test');
  assert('is_object($result) /* Table result is not an array */');

  $sqli->destroyDb();
  assert('!file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');

  //assert();


}

function test_destroyTb ()
{

}


function test_insertData ()
{
  $path = 'databases';
  $database = 'test1.sqlite';
  $table = 'test';
  $values = ['name STRING PRIMARY KEY','stuff STRING'];
  $sqli = new \sqit\sqlite($path, $database, 'destroy DB');
  assert('file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');

  $create = $sqli->createTb($table, $values);
  $result = $sqli->query('SELECT * FROM test');
  assert('is_object($result) /* Table result is not an array */');

  $sqli->insert('', [], [[]]);

  $sqli->insert($table, ['name'], [['barry']]);
  $sqli->insert($table, ['name','stuff'], [['larry', 'larrys stuff']]);
  $sqli->insert($table, ['name','stuff'], [['harry', 'Harrys stuff.'],['garry','Things Garry likes.']]);

  $rows = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = $row;
  }

  assert('$rows[1][\'stuff\'] == \'larrys stuff\' /* $rows[1][2] is not larrys stuff */');
  $sqli->destroyDb();
  assert('!file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');
}

function test_updateData ()
{
  $path = 'databases';
  $database = 'test1.sqlite';
  $table = 'test';
  $values = ['name STRING PRIMARY KEY','id INTEGER', 'stuff STRING'];
  $sqli = new \sqit\sqlite($path, $database, 'create DB');
  assert('file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');

  $create = $sqli->createTb($table, $values);
  $result = $sqli->query('SELECT * FROM test');
  assert('is_object($result) /* Table result is not an array */');

  $sqli->insert($table, ['name', 'id'], [['barry', 1]]);
  $sqli->insert($table, ['name', 'id', 'stuff'], [['larry', 2, 'larrys stuff']]);
  $sqli->insert($table, ['name', 'id', 'stuff'], [['harry', 3, 'Harrys stuff.'],['garry', 4, 'Things Garry likes.']]);

  $rows = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = $row;
  }

  //print_r($rows);
  assert('$rows[1][\'stuff\'] == \'larrys stuff\' /* $rows[1][2] is not larrys stuff */');

  $set = ['stuff=\'Things larry likes.\''];
  $where = 'id=2';

  $sqli->update($table, $set, $where);


  //$result = $sqli->query('SELECT * FROM test');
  $rows = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = $row;
  }
  //print_r($rows);

  $sqli->destroyDb();
  assert('!file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');
}

function test_deleteData ()
{
  $path = 'databases';
  $database = 'test1.sqlite';
  $table = 'test';
  $values = ['name STRING PRIMARY KEY','id INTEGER', 'stuff STRING'];
  $sqli = new \sqit\sqlite($path, $database, 'create DB');
  $create = $sqli->createTb($table, $values);
  $result = $sqli->query('SELECT * FROM test');

  $sqli->insert($table, ['name', 'id'], [['barry', 1]]);
  $sqli->insert($table, ['name', 'id', 'stuff'], [['larry', 2, 'larrys stuff']]);
  $sqli->insert($table, ['name', 'id', 'stuff'], [['harry', 3, 'Harrys stuff.'],['garry', 4, 'Things Garry likes.']]);

  $sqli->delete($table, ['id = 2']);

  //assert();

  $rows = [];
  while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $rows[] = $row;
  }
  //print_r($rows);
  assert('count($rows) == 3 /* Table should contain exactly 3 row. */');
  assert('$rows[1][\'id\'] == 3 /* $rows[1][id] should equal 3. */');

  $sqli->destroyDb();
  assert('!file_exists(\'databases/test1.sqlite\') /* sqli path is not string */');
}

function Analysis ($file, $line, $code, $desc = null) {
  echo "Analysis", PHP_EOL;
}


class segments {
  private $path = 'databases';
  private $database = 'test1.sqlite';
  private $table = 'test';
  private $sqli = null;
  private $columnNames = [];
  private $values = [];
  private $createTableSql = '';

  public function __construct ()
  {
    $this->sqli = new \sqit\sqlite($path, $database);


    $values = ['name STRING PRIMARY KEY','stuff STRING'];
    $this->createTableSql = '';

  }



  public function create ($data = null)
  {
      $this->sqli = new \sqit\sqlite($this->path, $this->database, 'create DB');
  }

  public function populate ($data = null)
  {

  }

  public function destroy ($data = null)
  {

  }




}




class Analysis {

function test () {

}

}


?>
