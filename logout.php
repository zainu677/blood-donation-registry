<?php
header("Cache-Control: no-cache, no-store, must-validate, private");
header("Pragma: no-cache");
header("Expires: 0");

// logout.php
session_start();
$_SESSION = array();

if(ini_get("session.use_cookies")){
    $paramas = session_get_cookie_params();
    setcookie(
        session_name(),'',time() - 4200,
        $params["path"], $paramas["domain"],
        $paramas["secure"], $paramas["httponly"]
    );}

session_destroy();
header('Location: LOGIN.html');
exit;
