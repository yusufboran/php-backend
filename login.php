<?php
date_default_timezone_set('Europe/Istanbul');
include 'conn.php';
$username = $_POST["username"];
$password = $_POST["password"];

$consult = $conn->query("SELECT * FROM user WHERE username = '" . $username . "' ");

$result = $consult->fetch_assoc();

// $value= utf8_encode('01234');
// $hass_pass = hash('sha256', $value);

if($result["username"] == $username AND hash_equals($password, $result["password"])){
    $sql = "INSERT INTO `login_log_true` (`id`, `user_id`, `transaction_date`, `ip`) VALUES (NULL, '2', '".date("Y-m-d h:i:sa")."', '".$_SERVER['REMOTE_ADDR']  ."');";
   echo "true";
}
else{
    $sql = "INSERT INTO `login_log_false` (`id`, `user_id`, `transaction_date`, `ip`) VALUES (NULL, '615','".date("Y-m-d h:i:sa")."', '".$_SERVER['REMOTE_ADDR']  ."');";

    echo "false";
}
$consult = $conn->query($sql);


mysqli_close($conn);
