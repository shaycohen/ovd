<?php

if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

if (strlen($_POST['file_name']) <= 0) { 
	echo $_POST['file_name'];
        echoError("upload::get_file_name", "File name must be set");
	return false;
}
$damage_file_name = $_POST['file_name']; 

$damage_id = set_damage($damage_file_name);
$target_dir = "/var/www/html/damages/";
$target_file =  $target_dir . $damage_file_name . '-'. $damage_id . '.jpg';
$target_thumb = $target_dir . $damage_file_name . '-'. $damage_id . '_thumb.jpg';
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check == false) {
        echoError("upload::getimagesize", "False");
        $uploadOk = 0;
    }
}
echoDebug("damage::pre_upload", $_POST, 5);
if (file_exists($target_file)) {
    echoError("upload::file_exists", "True");
    $uploadOk = 0;
}
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echoError("upload", "File format is not supported");
    $uploadOk = 0;
}

if (move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $target_file)) {
  $convert_to_thumb = "convert -thumbnail 200 $target_file $target_thumb 2>&1";
  $output = system($convert_to_thumb, $retval);
  if ($retval > 0) { 
    echoError("upload::create_thumbnail", "output [" . $output . "] retval [" . $retval ."] cmd[" . $convert_to_thumb . "]");
    $uploadok = 0;
  } else { 
    $uploadok = 1;
  } 
} else {
  echoError("upload::move_uploaded_file", "Failed");
  $uploadok = 0;
}

if ($uploadok == 1) { 
  set_damage_enabled($damage_id);
  if ($_POST['serial_id'] > 0) { 
    $url="/damage.php?serial_id=$_POST[serial_id]&container_id=$_POST[container_id]&warehouse_id=$_POST[warehouse_id]";
  } elseif ($_POST['container_id'] > 0) { 
    $url="/damage.php?container_id=$_POST[container_id]&warehouse_id=$_POST[warehouse_id]";
  } 
  redirect($url);
}

echoDebug("damage::post_upload", $_FILES, 5);

