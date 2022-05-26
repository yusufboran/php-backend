<?php
include 'conn.php';
// Ortalama bulma
//echo "<pre>";
$sql = "SELECT transaction_date,  AVG(milk_quantity) as milk_quantity FROM `milk`
  GROUP BY transaction_date  
ORDER BY `milk`.`transaction_date`  DESC LIMIT 1";

$consult = $conn->query($sql);
$result = array();
while ($extractdata = $consult->fetch_assoc()) {
    $result[] = $extractdata;
}

$average = json_encode(floatval($result[0]["milk_quantity"]));
$date = json_encode($result[0]["transaction_date"]);

// Ortalamanın üstünde değerleri listesini alma
$results = array();

$sql = "SELECT animal_id, conductivity ,milk_quantity FROM `milk` WHERE milk_quantity > '" . $average . "' and transaction_date = " . $date . " ORDER BY `milk`.`milk_quantity` DESC LIMIT 20 ";
$consult = $conn->query($sql);

$result = array();
while ($extractdata = $consult->fetch_assoc()) {
    $result[] = $extractdata["animal_id"];
}
$results["high_yield"] = listDetail($result, $conn);

$sql = "SELECT animal_id, conductivity ,milk_quantity FROM `milk` WHERE milk_quantity < '" . $average . "' and transaction_date = " . $date . " ORDER BY `milk`.`milk_quantity` ASC LIMIT 20 ";
$consult = $conn->query($sql);

$result = array();
while ($extractdata = $consult->fetch_assoc()) {
    $result[] = $extractdata["animal_id"];
}
$results["low_yield"] =  listDetail($result, $conn);

$sql = "SELECT DISTINCT animal_id FROM `milk` where farm_id = 1";
$consult = $conn->query($sql);
$result = array();
while ($extractdata = $consult->fetch_assoc()) {
    $result[] = $extractdata["animal_id"];
}

$sql = "SELECT DISTINCT animal_id FROM `movement` where farm_id = 1";
$consult = $conn->query($sql);
while ($extractdata = $consult->fetch_assoc()) {
    if (!array_search($extractdata["animal_id"], $result))
        $result[] = $extractdata["animal_id"];
}
$final_result = array();
foreach ($result as $item) {

    $sql = "SELECT  milk_quantity  FROM `milk` WHERE animal_id = " . $item . "  ORDER BY `milk`.`transaction_date` DESC";
    $list = array();
    $consult = $conn->query($sql);
    while ($extractdata = $consult->fetch_assoc()) {
        $list[] = $extractdata["milk_quantity"];
    }
    $last_day_milk = $list[0];
    $milk_ss = std_deviation($list);
    $average_milk = array_sum($list) / count($list);

    if ($last_day_milk <= $average_milk - (2 * $milk_ss)) {
        $final_result[] = $item;
    } else {
        $sql = "SELECT  movement  FROM `movement` WHERE animal_id = " . $item . "  ORDER BY `movement`.`transaction_date` DESC";
        $list = array();
        $consult = $conn->query($sql);
        while ($extractdata = $consult->fetch_assoc()) {
            $list[] = $extractdata["movement"];
        }
        $last_day_milk = $list[0];
        $milk_ss = std_deviation($list);
        $average_milk = array_sum($list) / count($list);

        if ($last_day_milk >= $average_milk + (2 * $milk_ss)) {
            $final_result[] = $item;
        }
    }
}

$results["anomaly_list"] = listDetail($final_result, $conn);

print_r(json_encode($results, JSON_PRETTY_PRINT));

function listDetail($final_result, $conn)
{
    $items = array();
    foreach ($final_result as $item) {
        $arr = array();
        $result = array();

        $sql = "SELECT * FROM `milk` WHERE animal_id = " . $item . " ORDER BY `milk`.`transaction_date` DESC LIMIT 1";
        $consult = $conn->query($sql);
        $result = $consult->fetch_assoc();
        $arr["animal_id"] = $item;
        $arr["transaction_date"] = $result["transaction_date"];
        $arr["conductivity"] = $result["conductivity"];
        $arr["milk_quantity"] = $result["milk_quantity"];


        $sql = "SELECT * FROM `movement` WHERE animal_id = " . $item . " ORDER BY `movement`.`transaction_date` DESC LIMIT 1";
        $consult = $conn->query($sql);
        $result = $consult->fetch_assoc();
        $arr["movement"] = $result["movement"];
        $items[] = $arr;
    }
    return $items;
}

function std_deviation($my_arr)
{
    $no_element = count($my_arr);
    $var = 0.0;
    $avg = array_sum($my_arr) / $no_element;
    foreach ($my_arr as $i) {
        $var += pow(($i - $avg), 2);
    }
    return (float)sqrt($var / $no_element);
}
