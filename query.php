<?php
header('Content-Type: application/json; charset=utf-8');
include 'conn.php';

if ($_POST["start_date"] && $_POST["finish_date"]) {

    $start_date = $_POST['start_date'];
    $finish_date = $_POST['finish_date'];

    if ($_POST["animal_id"]) {
        $animal_id = $_POST["animal_id"];
        $sql = "SELECT transaction_date,milk_quantity ,conductivity FROM `milk` WHERE animal_id = '" . $animal_id . "' ";
    } else {
        $sql = "SELECT transaction_date,  AVG(milk_quantity) as milk_quantity, avg(conductivity) as conductivity FROM `milk`
     WHERE transaction_date BETWEEN '" . $start_date . "' and  '" . $finish_date . "' GROUP BY transaction_date";
    }
} else if ($_POST["farm_id"]) {
    $farm_id = $_POST['farm_id'];
    $sql = "SELECT DISTINCT animal_id FROM `milk` where farm_id = 1";
}

if ($_POST["animal_id"]) {
    $animal_id = $_POST["animal_id"];
    $sql = "SELECT transaction_date,milk_quantity ,conductivity FROM `milk` WHERE animal_id = '" . $animal_id . "' ";
}

$consult = $conn->query($sql);

$milk = array();
$con = array();
$data = array();
while ($extractdata = $consult->fetch_assoc()) {
    $milk[] =  $extractdata["milk_quantity"];
    $con[] = $extractdata["conductivity"];
    $data[] = $extractdata;
}


$ss_milk = std_deviation($milk);
$ss_con = std_deviation($con);

$results["trend_milk"] = trend($milk, $data, $ss_milk);
$results["trend_conductivity"] = trend($con, $data, $ss_con);

function trend($list, $balanced, $ss)
{ // method that calculates the trend of the given list of numbers and returns the trend order
    $X = range(1, count($list));
    $Y = $list;

    // Now estimate $a and $b using equations from Math World
    $n = count($X);

    $mult_elem = function ($x, $y) {   //anon function mult array elements 
        $output = $x * $y;              //will be called on each element
        return $output;
    };

    $sumX2 = array_sum(array_map($mult_elem, $X, $X));

    $sumXY = array_sum(array_map($mult_elem, $X, $Y));
    $sumY = array_sum($Y);
    $sumX = array_sum($X);

    $bFit = ($n * $sumXY - $sumY * $sumX) /
        ($n * $sumX2 - pow($sumX, 2));
    $aFit = ($sumY - $bFit * $sumX) / $n;

    $Yfit = array();
    foreach ($X as $x) {
        $Yfit[] = $aFit + $bFit * $x;
    }
    $bottom_trend = array();
    $top_trend = array();
    foreach ($balanced as $key => $value) {
        $arr = array();
        $arr["transaction_date"] = $value["transaction_date"];
        $arr["value"] = $Yfit[$key] - $ss;
        $bottom_trend[] = $arr;

        $arr = array();
        $arr["transaction_date"] = $value["transaction_date"];
        $arr["value"] = $Yfit[$key] + $ss;
        $top_trend[] = $arr;
    }
    $return_list = array();
    $arr = array();
    $arr[] = $bottom_trend[0];
    $arr[] = end($bottom_trend);
    $return_list["bottom_trend"]=$arr;

    $arr = array();
    $arr[] = $top_trend[0];
    $arr[] = end($top_trend);
    $return_list["top_trend"]=$arr;

    return $return_list;
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
$arr = array();
$arr["data"] = $data;
$arr["trend"] = $results;

echo json_encode($arr); //JSON_PRETTY_PRINT
