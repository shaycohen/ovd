<?php

$br = "<BR>\n";
global $br;
$db_name='ovd';
global $db_name;

function get_dbh($dbname) { 
  $dbuser=getenv('DBUSER');
  if ($dbuser == '') { 
    $dbuser = 'ovd';
  }
  $dbpass=getenv('DBPASS');
  if ($dbpass == '') { 
    $dbpass = 'should_not_work';
  }
  $dbhost=getenv('DBHOST');
  if ($dbhost == '') { 
    $dbhost = 'db';
  }
  $dsn = "mysql:host=".$dbhost.";dbname=".$dbname.";charset=utf8";
  $options = array(
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
      PDO::ATTR_EMULATE_PREPARES => false
  ); 

  $dbh = new PDO($dsn, $dbuser, $dbpass, $options);
  if ($_GET[debug_level] > 3) { 
     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  }
    
  return $dbh;
}

function echoError($caller, $text) { 
  global $br;
  echo "ERROR [$caller]: $text $br";
}

function echoDebug($caller, $text, $level) { 
  global $br;
	if (isset($_GET['debug_level']) && $_GET['debug_level'] > $level) { 
		echo "DEBUG($level<".$_GET['debug_level'].") [ ". $caller ." ] $br";
		echo var_dump($text);
	}
}

function verify_params($params) { 
  foreach ($params as &$param) { 
    if (empty($_GET[$param])) { 
      echoError('common::verify_params', 'Parameter '.$param.' is empty');
      return false;
    }
    if ($param == "hwaddr") { 
      if ( ! is_valid_mac($_GET[$param]) ) { 
        echoError('common::verify_params', 'Parameter '.$param.' is not valid');
        return false;
      }
    }
  }
  return true;
}

function db_query($query, $params) {
  global $db_name;
	$dbh = get_dbh($db_name);
	echoDebug("db_query::query", $query, 1);
	echoDebug("db_query::params", $params, 1);
	$stmt = $dbh->prepare($query);
  if ($_GET[debug_level] > 3) { 
    try { 
      if($stmt->execute($params)) { ;
        $return = array('fetch'=>$stmt->fetch(), 'rowCount'=>$stmt->rowCount(), 'stmt'=>$stmt);
        echoDebug('common::db_query::result', $return, 4);
        return $return;
      } else {
        return array('err'=>2);;
      }
    }
    catch (PDOException $err) { 
    $trace = '#TRACE ';
    foreach ($err->getTrace() as $a => $b) {
        foreach ($b as $c => $d) {
            if ($c == 'args') {
                foreach ($d as $e => $f) {
                    $trace .= strval($a) . "\n args: [$e] [$f]";
                }
            } else {
                $trace .= strval($a) . "\n $c $d";
            }
        }
    }
    echo "PDO Code [" . strval($err->getCode()) . "]\n
          PDO Message [". $err->getMessage() . "]\n
          PDO File [". $err->getFile() . "]\n
          PDO Line [". strval($err->getLine()) . "]\n
          PDO Trce [". $trace . "]\n";

    }
  } else { 
    if ($stmt->execute($params)) {
      $return = array('fetch'=>$stmt->fetch(), 'rowCount'=>$stmt->rowCount(), 'stmt'=>$stmt);
      echoDebug('common::db_query::result', $return, 4);
      return $return;
    } else {
      return array('err'=>2);;
    }
  }
}

