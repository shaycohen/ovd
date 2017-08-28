<?php

session_start();
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
		echo var_dump($text) . $br.$br;
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
      if($stmt->execute($params)) { 
        $return = array('fetch'=>$stmt->fetch(), 'stmt'=>$stmt, 'lastInsertId'=>$dbh->lastInsertId());
        echoDebug('common::db_query::result', $return, 3);
        return $return;
      } else {
        echoError('common::db_query::result', 'Statemenet execution failed');
        return array('err'=>2);
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
      $return = array('fetch'=>$stmt->fetch(), 'stmt'=>$stmt, 'lastInsertId'=>$dbh->lastInsertId());
      echoDebug('common::db_query::result', $return, 2);
      return $return;
    } else {
      echoError('common::db_query::result', 'Statemenet execution failed');
      return array('err'=>2);
    }
  }
}

function auth() {
  $realm = 'OVD';

  if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
      header('HTTP/1.1 401 Unauthorized');
      header('WWW-Authenticate: Digest realm="'.$realm.
             '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
      die('Not Authorized');
  }


  if (!($data = http_digest_parse($_SERVER['PHP_AUTH_DIGEST']))) { 
    echoError("common::auth", "AUTH_DIGEST not set");
    die("AUTH_DIGEST not set");
  }

  $user = db_query("SELECT * FROM user WHERE username=?", array($data['username']))[fetch];
  if (!(isset($user['username']))) { 
    unset($_SERVER["PHP_AUTH_DIGEST"]);
    die('Missing Credentials');
  }
  echoDebug('common:auth::user', $user, 9);

  $A1 = md5($data['username'] . ':' . $realm . ':' . $user['pw']);
  $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
  $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

  if ($data['response'] != $valid_response)
      die('Wrong Credentials');
  echoDebug("common::auth", $data, 3);
  return $user;
}


function http_digest_parse($txt) {
  // protect against missing data
  $needed_parts = array('nonce'=>1, 'nc'=>1, 'cnonce'=>1, 'qop'=>1, 'username'=>1, 'uri'=>1, 'response'=>1);
  $data = array();
  $keys = implode('|', array_keys($needed_parts));

  preg_match_all('@(' . $keys . ')=(?:([\'"])([^\2]+?)\2|([^\s,]+))@', $txt, $matches, PREG_SET_ORDER);

  foreach ($matches as $m) {
      $data[$m[1]] = $m[3] ? $m[3] : $m[4];
      unset($needed_parts[$m[1]]);
  }

  return $needed_parts ? false : $data;
}

function get_containers() { 
  #warehouse<-> user ACL ?# $result = db_query("SELECT * FROM container WHERE warehouse_id IN (SELECT id FROM warehouse WHERE id=?)", array($_SESSION[id]));
  $result = db_query("SELECT * FROM container WHERE active=1", array());
  echoDebug("common::get_containers", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}
function get_warehouse($id = '') { 
  #warehouse<-> user ACL ?# if ($id == '') { 
  #warehouse<-> user ACL ?# $id = $_SESSION[warehouse_id];
  #warehouse<-> user ACL ?# }
  #warehouse<-> user ACL ?# $result = db_query("SELECT * FROM warehouse where id=?", array($id));
  $result = db_query("SELECT * FROM warehouse", array());
  echoDebug("common::get_warehouse", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}
function get_serials() { 
  $result = db_query("SELECT * FROM serial;", array());
  echoDebug("common::get_serials", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}

function set_damage() { 
  if ($_POST['serial_id'] > 0) { 
    $result = db_query("INSERT INTO damage (serial_id, type, description) values(:serial_id, :type, :description);", array('serial_id' => $_POST['serial_id'], 'type' => $_POST['type'], 'description' => $_POST['description']));
  }
  if ($_POST['container_id'] > 0) { 
    $result = db_query("INSERT INTO damage (container_id, type, description) values(:container_id, :type, :description);", array('container_id' => $_POST['container_id'], 'type' => $_POST['type'], 'description' => $_POST['description']));
  }
  echoDebug("common::set_damage", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $result['lastInsertId'];
}

function set_damage_enabled($damage_id) { 
  $result = db_query("UPDATE damage set enabled=1 WHERE id=?", array($damage_id));
  echoDebug("common::set_damage", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $result;
}

function set_serial_status($id, $stat) { 
  $result = db_query("UPDATE serial set status=:stat WHERE id=:id", array('id'=>$id, 'stat'=>$stat));
  echoDebug("common::set_serial_status", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $result;
}

function get_damages() { 
  global $user;
  $result = db_query("SELECT * FROM damage WHERE enabled=1;", array());
  echoDebug("common::get_damages", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}

function redirect($url, $permanent = false)
{
  header('Location: ' . $url, true, $permanent ? 301 : 302);
  exit();
}

$user = $_SESSION;
global $user;

#redirect("/loginfo.php");

?>
