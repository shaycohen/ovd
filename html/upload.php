<?php

if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

$damage_id = set_damage();
$target_dir = "/var/www/html/damages/";
$target_file = $target_dir . $damage_id . '.jpg';
$target_thumb = $target_dir . $damage_id . '_thumb.jpg';
$uploadOk = 1;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        $uploadOk = 1;
    } else {
        echoError("upload::getimagesize", "False");
        $uploadOk = 0;
    }
}
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
    }
    redirect("/damage.html?manifest_id=$_POST[manifest_id]");
} else {
    echoError("upload::move_uploaded_file", "Failed");
}

echoDebug("damage::post_upload", $_FILES, 5);

