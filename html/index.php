<HTML>
<?php 

if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}
$user = auth();
global $user;
echoDebug('index::user',$user,9);

### MAIN ###

function get_containers() { 
  global $user;
  $result = db_query("SELECT * FROM container WHERE warehouse_id IN (SELECT id FROM warehouse WHERE id=?)", array($user[id]));
  echoDebug("index::init", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}
function get_warehouse() { 
  global $user;
  $result = db_query("SELECT * FROM warehouse WHERE id=?", array($user[id]));
  echoDebug("index::init", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}
function get_reports() { 
  global $user;
  $result = db_query("SELECT * FROM report WHERE container_id IN (SELECT warehouse_id FROM container WHERE id IN (SELECT id FROM warehouse WHERE id=?))", array($user[id]));
  echoDebug("index::init", $result, 0);
  $fetchAll=$result[stmt]->fetchAll();
  array_unshift($fetchAll, $result[fetch]);
  return $fetchAll;
}

if (isset($_GET['action'])) { 
  if ($_GET['action'] == 'get_containers') {
    echo json_encode(get_containers());
  }

  if ($_GET['action'] == 'get_warehouse') {
    echo json_encode(get_warehouse());
  }

  if ($_GET['action'] == 'get_user') {
    echo json_encode($user);
  }

  if ($_GET['action'] == 'get_reports') {
    echo json_encode(get_reports());
  }
}
  
  


#phpinfo();


?>
<HTML>
