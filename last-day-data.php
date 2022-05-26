<?php
include 'conn.php';
$items = array();

$sql = "SELECT transaction_date,  AVG(milk_quantity) as milk_quantity, avg(conductivity) as conductivity FROM `milk`
  GROUP BY transaction_date  
ORDER BY `milk`.`transaction_date`  DESC LIMIT 1";
$consult = $conn->query($sql);

$result = array();
while ($extractdata = $consult->fetch_assoc()) {
    $result[] = $extractdata;
}

$last_day = $result[0]["transaction_date"];
$arr = array();
$arr["transaction_date"] = $result[0]["transaction_date"];
$arr["conductivity"] = $result[0]["conductivity"];
$arr["milk_quantity"] = $result[0]["milk_quantity"];
$items["last_day_average"] = $arr;

$arr = array();
$sql = "SELECT * FROM `milk` WHERE transaction_date = '" . $last_day . "' ORDER BY `milk`.`milk_quantity` DESC LIMIT 1";
$consult = $conn->query($sql);

$result = array();
while ($extractdata = $consult->fetch_assoc()) {
    $result[] = $extractdata;
}

$arr["transaction_date"] = $result[0]["transaction_date"];
$arr["conductivity"] = $result[0]["conductivity"];
$arr["milk_quantity"] = $result[0]["milk_quantity"];
$items["last_highest_data"] = $arr;

echo json_encode($items,JSON_PRETTY_PRINT); //JSON_PRETTY_PRINT