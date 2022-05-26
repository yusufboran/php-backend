<?php
date_default_timezone_set('Europe/Istanbul');
include 'conn.php';

$farmname = $_POST["farmname"];
$animalId = $_POST["animalId"];
$analysisKind = $_POST["analysisKind"];

$lobOne = $_POST["lobOne"];
$lobTwo = $_POST["lobTwo"];
$lobThree = $_POST["lobThree"];
$lobFour = $_POST["lobFour"];

if($farmname && $animalId && $analysisKind){
    $sql ="INSERT INTO `mastitis_veri_kayit` (`id`, `transacation_date`, `hayvan_id`, `ciftlik_isim`, `hastalik_turu`, `birinci_lob`, `ikinci_lob`, `ucuncu_lob`, `dorduncu_lob`) 
    VALUES (NULL,'".date("Y-m-d")."', '".$animalId."', '".$farmname."', '".$analysisKind."', '".$lobOne."', '".$lobTwo."', '".$lobThree."', '".$lobFour."');";
    
    $consult = $conn->query($sql);
    mysqli_close($conn);
}


