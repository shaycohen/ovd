<?php 

if ((require_once 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

if (isset($_GET['action'])) { 
  if ($_GET['action'] == 'get_containers') {
    echo json_encode(get_containers());
  }

  if ($_GET['action'] == 'get_warehouse') {
    echo json_encode(get_warehouse());
  }

  if ($_GET['action'] == 'get_user') {
    echo json_encode($_SESSION);
  }

  if ($_GET['action'] == 'get_manifests') {
    echo json_encode(get_manifests());
  }

  if ($_GET['action'] == 'get_damages') {
    echo json_encode(get_damages());
  }
}

?>
