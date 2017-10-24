<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

define('DOMAIN_FQDN', 'OVRS.CO.IL');
define('LDAP_SERVER', 'dc01.ovrs.co.il');

if ((include 'common.php') != TRUE ) {
	echo "\necho 'Error with loading common functions file'";
	return false;
}

echoDebug("login::init", "-", 1);
if (isset($_POST['submit']))
{
    $user = strip_tags($_POST['username']) .'@'. DOMAIN_FQDN;
    $pass = stripslashes($_POST['password']);

    $conn = ldap_connect("ldap://". LDAP_SERVER ."/");
    echoDebug("login::conn", $conn, 1);

    if (!$conn)
        $err = 'Could not connect to LDAP server';
    else
    {
        #define('LDAP_OPT_DIAGNOSTIC_MESSAGE', 0x0032);

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $bind = @ldap_bind($conn, $user, $pass);

        ldap_get_option($conn, LDAP_OPT_DIAGNOSTIC_MESSAGE, $extended_error);

        if (!empty($extended_error))
        {
            $errno = explode(',', $extended_error);
            $errno = $errno[2];
            $errno = explode(' ', $errno);
            $errno = $errno[2];
            $errno = intval($errno);

            if ($errno == 532)
                $err = 'Unable to login: Password expired';
        }

        elseif ($bind)
        {
            $base_dn = array(
                "OU=Ashdod Users,OU=Ashdod,DC=". join(',DC=', explode('.', DOMAIN_FQDN)),
                "CN=Users,DC=". join(',DC=', explode('.', DOMAIN_FQDN)), 
                "OU=DMG,DC=". join(',DC=', explode('.', DOMAIN_FQDN)),
                "OU=Haifa Users,OU=Haifa,DC=". join(',DC=', explode('.', DOMAIN_FQDN)),
                "OU=Test,DC=". join(',DC=', explode('.', DOMAIN_FQDN)),
                "OU=Natbag Users,OU=Natbag,DC=". join(',DC=', explode('.', DOMAIN_FQDN))
            );

            echoDebug("login::verify_base_dn", $base_dn, 1);
            $result = ldap_search(array($conn,$conn,$conn,$conn,$conn,$conn), $base_dn, "(cn=*)");
            echoDebug("login::verify_search_result", $result, 1);

            echoDebug("login::verify_ldap_foreach_user",$user,2);
            if (!count($result)){ 
                $err = 'Unable to login: '. ldap_error($conn);
                echoDebug("login::verify_ldap_error",ldap_error($conn),2);
            } else {
                echoDebug("login::verify_ldap_success",ldap_error($conn),2);
                foreach ($result as $res)
                {
                    echoDebug("login::verify_ldap_foreach",$res,2);
                    $info = ldap_get_entries($conn, $res);
                    echoDebug("login::verify_ldap_foreach_info",$info,2);
                    for ($i = 0; $i < $info['count']; $i++)
                    {
                        if (isset($info[$i]['userprincipalname'])) echoDebug("login::verify_ldap_foreach_userprincipalname",$info[$i]['userprincipalname'][0],2);
                        if (isset($info[$i]['samaccountname'])) echoDebug("login::verify_ldap_foreach_samaccountname",$info[$i]['samaccountname'][0],2);
                        #echoDebug("login::verify_ldap_foreach_samaccountname",$info[$i]['samaccountname'][0],2);
                        if (isset($info[$i]['userprincipalname']) AND strtolower($info[$i]['userprincipalname'][0]) == strtolower($user))
                        {
                            echoDebug("login::verify_ldap_foreach_user_match",true,2);
                            if (is_session_started() === FALSE) session_start();

                            $username = explode('@', $user);
                            $_SESSION['user'] = $username;
                            $_SESSION['name'] = $info[$i]['givenname'][0];
                            //$_SESSION['result'] = $info[$i];

                            // set session variables...

                            break;
                        }
                    }
                }
            }
        }
    }

    // session OK, redirect to home page
    if (isset($_SESSION['user']))
    {
        header('Location: /main.php');
        exit();
    }

    elseif (!isset($err)) $err = 'Unable to login: '. ldap_error($conn);

    ldap_close($conn);
}
?>
<html ng-app="ui.bootstrap.ovd">
  <head><title>Welcome to OVD</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta meta charset=utf-8>
    <script src="/ext/angular.js"></script>
    <script src="/ext/angular-animate.js"></script>
    <script src="/ext/angular-sanitize.js"></script>
    <script src="/ext/ui-bootstrap-tpls-2.5.0.js"></script>
    <script src="/ext/jquery.min.js"></script>
    <script src="/ext/bootstrap.min.js"></script>
    <script src="/ext/lodash.js"></script>
    <script src="main.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <link rel="stylesheet" href="/style.css">

  </head>
  <body>

<div ng-controller="mainCtrl">
<form class="form-group" method="POST" action="index.php">
  <h2 class="form-group-heading">{{ ui.login }}</h2>
  <div class="input-group">
<span class="input-group-addon" id="basic-addon1">@</span>
<input type="text" name="username" class="form-control" placeholder="Username" required>
</div>
  <label for="inputPassword" class="sr-only">Password</label>
  <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
  <button class="btn btn-lg btn-primary btn-block" name="submit" type="submit">Login</button>
  <button class="btn btn-lg btn-warning btn-block" ng-if="get_fmsg == 'invalid_credentials'">{{ ui.invalid_credentials }}</button>
  <?php if (isset($err))
    echo '<button class="btn btn-lg btn-warning btn-block">'. $err . '</button>';
  ?>
</form>
</body></html>
