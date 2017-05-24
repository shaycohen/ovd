<?php  
if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

if (isset($_POST['username']) and isset($_POST['password'])){
  $username = $_POST['username'];
  $pw = $_POST['password'];
  $result = db_query("SELECT * FROM `user` WHERE username=:username and pw=:pw", array('username' => $username, 'pw' => $pw));
  if ($result['fetch']['username'] == $username){
    $_SESSION = $result['fetch'];
  }else{
    redirect("/index.html?fmsg=invalid_credentials");
  }
}
redirect("/main.html");

?>
