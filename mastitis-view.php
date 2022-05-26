<?php
date_default_timezone_set('Europe/Istanbul');
include 'conn.php';

$farmname = $_POST["farmname"];
$animalId = $_POST["animalId"];
$analysisKind = $_POST["analysisKind"];

if ($farmname && $animalId && $analysisKind) {
    $sql = "SELECT * FROM `mastitis_veri_kayit` WHERE ciftlik_isim = '".$farmname."' and hastalik_turu = '".$analysisKind."' AND hayvan_id = '".$animalId."';";


    $consult = $conn->query($sql);
    $arr = array();

    while ($extractdata = $consult->fetch_assoc()) {
        $arr[] =  $extractdata;
    }
    echo json_encode($arr);
    // print_r($result);
    print_r(($results));
    mysqli_close($conn);
}
