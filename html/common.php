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
      if($stmt->execute($params)) { ;
        $return = array('fetch'=>$stmt->fetch(), 'stmt'=>$stmt, 'lastInsertId'=>$dbh->lastInsertId());
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
      $return = array('fetch'=>$stmt->fetch(), 'stmt'=>$stmt, 'lastInsertId'=>$dbh->lastInsertId());
      echoDebug('common::db_query::result', $return, 4);
      return $return;
    } else {
      return array('err'=>2);;
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
  return $user;

  $A1 = md5($data['username'] . ':' . $realm . ':' . $user['pw']);
  $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
  $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

  if ($data['response'] != $valid_response)
      die('Wrong Credentials');
  echoDebug("common::auth", $data, 3);
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
  global $user;
  $result = db_query("SELECT * FROM container WHERE warehouse_id IN (SELECT id FROM warehouse WHERE id=?)", array($user[id]));
  echoDebug("common::get_containers", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}
function get_warehouse() { 
  global $user;
  $result = db_query("SELECT * FROM warehouse WHERE id=?", array($user[id]));
  echoDebug("common::get_warehouse", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}
function get_manifests() { 
  global $user;
  $result = db_query("SELECT * FROM manifest;", array());
  echoDebug("common::get_manifests", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}

function set_damage() { 
  $result = db_query("INSERT INTO damage (manifest_id, type, description) values(:manifest_id, :type, :description);", array('manifest_id' => $_POST['manifest_id'], 'type' => $_POST['type'], 'description' => $_POST['description']));
  echoDebug("common::set_damage", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $result['lastInsertId'];
}

function get_damages() { 
  global $user;
  $result = db_query("SELECT * FROM damage;", array());
  echoDebug("common::get_damages", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}

function get_user() { 
  return auth();
}

function redirect($url, $permanent = false)
{
  header('Location: ' . $url, true, $permanent ? 301 : 302);
  exit();
}

$user = auth();
global $user;

