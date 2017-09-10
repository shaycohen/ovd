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

  if ($_GET['action'] == 'set_selected') {
    $postdata = file_get_contents("php://input");
    $data = json_decode($postdata);
    $_SESSION['selectedWarehouse'] = $data->selectedWarehouse;
    $_SESSION['selectedContainer'] = $data->selectedContainer;
    $_SESSION['selectedSerial'] = $data->selectedSerial;
    echo json_encode($_SESSION);
 }

  if ($_GET['action'] == 'get_user') {
    echo json_encode($_SESSION);
  }

  if ($_GET['action'] == 'get_serials') {
    echo json_encode(get_serials());
  }

  if ($_GET['action'] == 'get_damages') {
    echo json_encode(get_damages());
  }

  if ($_GET['action'] == 'set_serial_status') {
    echo json_encode(set_serial_status($_GET['id'], $_GET['stat']));
  }
}

?>
