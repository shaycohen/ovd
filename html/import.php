<?php 
# array(6) {
#  [0]=>
#  string(2) "OC"
#  [1]=>
#  string(1) "1"
#  [2]=>
#  string(7) "I170532"
#  [3]=>
#  string(11) "DFSU1445930"
#  [4]=>
#  string(19) "I170532179487A06001"
#  [5]=>
#  string(130) "   1 CTN                                                               ARIK & ZIAD AGENCIES LTD
#"
#}


if ((require_once 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

$tally = array();
$active_containers = array();
foreach (glob("import/*/TALLY_LIST.TXT") as $file) {
  $fileh = fopen($file, "r") or die("Unable to open file!");
  while(!feof($fileh)) {
    $line = explode(',', fgets($fileh));
    foreach ($line as &$line_ptr) { 
      $line_ptr = trim($line_ptr);
    }
    if ($line[0] == '') continue;
    echoDebug('import::read_list_file', $line, 3);
    if (! is_array($tally[$line[0] . $line[1]][$line[2] . $line[3]])) { 
      $tally[$line[0] . $line[1]][$line[2] . $line[3]] = array();
    }
    array_push($tally[$line[0] . $line[1]][$line[2] . $line[3]], array('number' => $line[4], 'description' => $line[5]));
    if (! in_array($line[2].$line[3], $active_containers)) { 
      array_push($active_containers, $line[2].$line[3]);
    }
  }
  fclose($fileh);
}
echoDebug('import::all_list_records', $tally, 4);

foreach ($tally as $warehouse => $containers) {
  $warehouse_id = db_query("SELECT * FROM warehouse where description like ?", array($warehouse))['fetch'];
  if ($warehouse_id == false) { 
    echo "creating warehouse";
    $result = db_query ("INSERT INTO warehouse (description) VALUES(?)", array($warehouse));
    $warehouse_id = db_query("SELECT * FROM warehouse where description like ?", array($warehouse))['fetch'];
  }
  $warehouse_id = $warehouse_id['id'];
  foreach ($containers as $container => $serials) {
    $update_container_loc = db_query("UPDATE container SET warehouse_id=:warehouse_id where active=1 and description like :container", array(':container'=>$container, ':warehouse_id'=>$warehouse_id))['fetch'];
    $container_id = db_query("SELECT * FROM container where active=1 and description like :container AND warehouse_id=:warehouse_id", array(':container'=>$container, ':warehouse_id'=>$warehouse_id))['fetch'];
    if ($container_id == false) { 
      echo "creating container";
      $result = db_query ("INSERT INTO container (description, warehouse_id) VALUES(:description, :warehouse_id)", array(':description' => $container, ':warehouse_id' => $warehouse_id));
      $container_id = db_query("SELECT * FROM container where active=1 and description like :container AND warehouse_id=:warehouse_id", array(':container'=>$container, ':warehouse_id'=>$warehouse_id))['fetch'];
    }
    $container_id = $container_id['id'];
    foreach ($serials as $serial) {
      $serial_id = db_query("SELECT * FROM serial where number like :serial and container_id=:container_id", array(':serial' => $serial['number'], ':container_id' => $container_id))['fetch'];
      if ($serial_id == false) { 
        echo "creating serial container_id = $container_id warehouse_id = $warehouse_id\n";
        $result = db_query ("INSERT INTO serial (number, description, container_id) VALUES(:number, :description, :container_id)", array(':number' => $serial['number'], ':description' => $serial['description'], ':container_id' => $container_id));
        $serial_id = db_query("SELECT * FROM serial where number=?", array($serial['number']))['fetch'];
        echo "New serial ID: " . $serial_id['id'] . " Number: " . $serial_id['number'] . "\n";
      } else {
        #echo "Skipping Serial ".$serial['number']."\n";
        $update_res = db_query("UPDATE serial set description=:description WHERE number like :serial and container_id=:container_id", array(':serial' => $serial['number'], ':container_id' => $container_id, ':description' => $serial['description']))['fetch'];
      }
    }
  }
}

$active_containers_count = count($active_containers);
if ($active_containers_count > 0) { 
	$active_containers_qm = sprintf("?%s", str_repeat(",?", ($active_containers_count ? $active_containers_count-1 : 0)));
	$not_active_containers_query = sprintf("UPDATE container SET active=0 WHERE description NOT IN (%s) and active=1", $active_containers_qm);
	$not_active_containers = db_query($not_active_containers_query, $active_containers)['fetch'];
	$not_active_containers_query = sprintf("SELECT description, active FROM container WHERE description NOT IN (%s)", $active_containers_qm);
	$not_active_containers = db_query($not_active_containers_query, $active_containers)['fetch'];
	echoDebug('import::not_active_containers', $not_active_containers, 2);
}


#OC,1,I170532,DFSU1445930,ZZ0006             ,I170532179487A06001

$exch = array();
foreach (glob("import/*/TALLY_EXCH*.TXT") as $file) {
  $fileh = fopen($file, "r") or die("Unable to open file!");
  while(!feof($fileh)) {
    $line = explode(',', fgets($fileh));
    foreach ($line as &$line_ptr) { 
      $line_ptr = trim($line_ptr);
    }
    if ($line[0] == '') continue;
    echoDebug('import::read_exch_file', $line, 2);
    if (! is_array($exch[$line[0] . $line[1]][$line[2] . $line[3]])) { 
      $exch[$line[0] . $line[1]][$line[2] . $line[3]] = array();
    }
    array_push($exch[$line[0] . $line[1]][$line[2] . $line[3]], array('old' => $line[4], 'new' => $line[5]));
    
  }
  fclose($fileh);
}

echoDebug('import::all_exch_records', $exch, 2);

foreach ($exch as $warehouse => $containers) {
  $warehouse_id = db_query("SELECT * FROM warehouse where description like ?", array($warehouse))['fetch'];
  echoDebug('import::exch_containers', $containers, 2);
  if ($warehouse_id == false) {
    echoError('import::exch_no_such_warehouse', $warehouse, 1);
    continue;
  }
  $warehouse_id = $warehouse_id['id'];
  foreach ($containers as $container_desc => $serials) {
    $container = db_query("SELECT * FROM container where active=1 and description like ?", array($container_desc))['fetch'];
    if ($container == false) { 
      echoError('import::exch_no_such_container', $container, 1);
      continue;
    }
    foreach ($serials as $serial) {
      $old_serial_id = db_query("SELECT * FROM serial where number=:old and container_id=:container", array(":old" => $serial['old'], ":container" => $container['id']))['fetch'];
      if ($old_serial_id == false) { 
        echoError('import::exch_no_such_serial_in_container', 'serial_old => ' . $serial['old'] . ' , container_id => ' .$container['id'], 1);
        continue;
      }
      $new_serial_id = db_query("SELECT * FROM serial where number=:new and container_id=:container", array(":new" => $serial['new'], ":container" => $container['id']))['fetch'];
      if ($new_serial_id == false) { 
        echoDebug('import::exch_no_such_serial_in_container', array('serial.new' => $serial['new'], 'container_id' => $container['id']), 1);
        echo "EXCH creating serial container_id = ".$container['id']." warehouse_id = $warehouse_id\n";
        $result = db_query ("INSERT INTO serial (number, description, container_id) VALUES(:number, :description, :container_id)", array(':number' => $serial['new'], ':description' => 'EXCH Auto Created by import.php', ':container_id' => $container['id']));
        $new_serial_id = db_query("SELECT * FROM serial where number=?", array($serial['new']))['fetch'];
        echo "New serial ID: " . $new_serial_id['id'] . " Number: " . $new_serial_id['number'] . "\n";
      }
      echo "EXCH replace ".$serial['old']." with " . $serial['new'] ."\n";
      $result = db_query("UPDATE serial SET serial_id=:new_serial_id WHERE id=:old_serial_id", array(":new_serial_id" => $new_serial_id['id'], "old_serial_id" => $old_serial_id['id']))['fetch'];
      $result = db_query("UPDATE serial SET status=1 WHERE id=:old_serial_id", array("old_serial_id" => $old_serial_id['id']))['fetch'];

      $result = db_query("SELECT * FROM damage WHERE serial_id=:old_serial_id", array("old_serial_id" => $old_serial_id['id']));
      $damages=$result[stmt]->fetchAll();
      array_unshift($damages, $result[fetch]);
      echoDebug('import::exch_damages', $damages, 2);
      if ($damages == false) { 
        continue;
      }
      foreach ($damages as $damage) {
        echoDebug('import::exch_damage_in_damages', $damage, 3);
        echoDebug('import::exch_attribute_in_damages', $damage, 3);
        $new_file_name = $new_serial_id['number'].$container['description'];
	$old_file_name = $damage['file_name'];
        $result = db_query("UPDATE damage SET file_name=:file_name WHERE id=:damage_id", array("damage_id" => $damage['id'], "file_name" => $new_file_name))['fetch'];
        rename('/DMG/'.$old_file_name.'-'.$damage['id'].'.jpg', '/DMG/'.$new_file_name.'-'.$damage['id'].'.jpg');
        rename('/DMG/'.$old_file_name.'-'.$damage['id'].'_thumb.jpg', '/DMG/'.$new_file_name.'-'.$damage['id'].'_thumb.jpg');
      }
    }
  }
}


?>
