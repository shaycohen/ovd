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

#OC,1,I170532,DFSU1445930,ZZ0006             ,I170532179487A06001

$exch = array();
foreach (glob("zz_exch_test.TXT") as $file) {
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
        $serial_id = db_query("SELECT * FROM serial where number=?", array($serial['new']))['fetch'];
        echo "New serial ID: " . $serial_id['id'] . " Number: " . $serial_id['number'] . "\n";
        continue;
      }
      echo "EXCH replace ".$serial['old']." with " . $serial['new'] ."\n";
      $result = db_query("UPDATE serial SET serial_id=:new_serial_id WHERE id=:old_serial_id", array(":new_serial_id" => $new_serial_id['id'], "old_serial_id" => $old_serial_id['id']))['fetch'];
      $result = db_query("UPDATE serial SET status=0 WHERE id=:old_serial_id", array("old_serial_id" => $old_serial_id['id']))['fetch'];

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
        rename('damages/'.$old_file_name.'-'.$damage['id'].'.jpg', 'damages/'.$new_file_name.'-'.$damage['id'].'.jpg');
        rename('damages/'.$old_file_name.'-'.$damage['id'].'_thumb.jpg', 'damages/'.$new_file_name.'-'.$damage['id'].'_thumb.jpg');
      }
    }
  }
}

?>
