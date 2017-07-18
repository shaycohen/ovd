<?php 

if ((require_once 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

$tally = array();
foreach (glob("import/TALLY*.TXT") as $file) {
  $fileh = fopen($file, "r") or die("Unable to open file!");
  while(!feof($fileh)) {
    $line = explode(',', fgets($fileh));
    if ($line[0] == '') continue;
    echoDebug('import::read_file', $line, 2);
    if (! is_array($tally[$line[0] . $line[1]][$line[2] . $line[3]])) { 
      $tally[$line[0] . $line[1]][$line[2] . $line[3]] = array();
    }
    array_push($tally[$line[0] . $line[1]][$line[2] . $line[3]], $line[4]);
    
  }
  fclose($fileh);
}

echoDebug('import::all_records', $tally, 1);

foreach ($tally as $warehouse => $containers) {
  $warehouse_id = db_query("SELECT * FROM warehouse where description like ?", array($warehouse))['fetch'];
  if ($warehouse_id == false) { 
    echo "creating warehouse";
    $result = db_query ("INSERT INTO warehouse (description) VALUES(?)", array($warehouse));
    $warehouse_id = db_query("SELECT * FROM warehouse where description like ?", array($warehouse))['fetch'];
  }
  $warehouse_id = $warehouse_id['id'];
  foreach ($containers as $container => $serials) {
    $container_id = db_query("SELECT * FROM container where description like ?", array($container))['fetch'];
    if ($container_id == false) { 
      echo "creating container<BR>";
      $result = db_query ("INSERT INTO container (description, warehouse_id) VALUES(:description, :warehouse_id)", array(':description' => $container, ':warehouse_id' => $warehouse_id));
      $container_id = db_query("SELECT * FROM container where description like ?", array($container))['fetch'];
    }
    $container_id = $container_id['id'];
    foreach ($serials as $serial) {
      $serial_id = db_query("SELECT * FROM serial where description like ?", array($serial))['fetch'];
      if ($serial_id == false) { 
        echo "creating serial<BR>cid = $container_id<BR>wid = $warehouse_id<br>";
        $result = db_query ("INSERT INTO serial (description, container_id) VALUES(:description, :container_id)", array(':description' => $serial, ':container_id' => $container_id));
        $serial_id = db_query("SELECT * FROM serial where description like ?", array($serial))['fetch'];
      }
    $serial_id = $serial_id['id'];
    echo "S $serial_id<BR>";
    }
  }
}


?>
