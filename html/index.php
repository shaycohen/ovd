<?php 

if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

### MAIN ###
$result = db_query("SELECT * from reports", array());
echoDebug("index::init", $result, 0);

?>
