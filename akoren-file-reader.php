<?php
echo "<pre>";
include 'conn.php';
require 'vendor/autoload.php';
//bad file check
$err_log_json = file_get_contents('error_log.json');
if ($err_log_json) {
  $decoded_json = json_decode($err_log_json);
  print_r($decoded_json);
  $error_start_num = $decoded_json->{'Error'}->{'id'};
  $error_file = $decoded_json->{'Error'}->{'file_name'};
  unlink('error_log.json');
}

$filenames = glob('*.xls');

$control_flag = false;
//file path
$user_file_path = "DataFlow2-do-not-reply@scr.co.il";
$base_file_path = 'unread-files/' . $user_file_path;
$files = open_file($base_file_path);

//create folder to move read files
$file_path_name = "read-files\\" . $user_file_path;
$olustur = mkdir($file_path_name);
if ($olustur) echo "Klasör Oluşturuldu1.<br>";
else echo "Klasör Oluşturulamadı1<br> ";
//*************************************** 

//Importing Excel file into folder
foreach ($files as $file) {
  if ($error_file == $file) {
    $control_flag = true;
  }
  $value = explode("-", $file);
  $value = $value[1];
  //excell reader
  $seleted_file = $base_file_path . "/" . $file;

  $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();

  if (!is_string($filenames)) {
    rename($seleted_file, $file);
    $filenames = $file;
  }
  echo $filenames;

  $sp = $reader->load($filenames);
  $wk = $sp->getActiveSheet();

  $items = [];
  foreach ($wk->getRowIterator() as $row) {
    $cell = $row->getCellIterator();
    $cell->setIterateOnlyExistingCells(false);
    $data = [];
    foreach ($cell as $cl) {
      $data[] = $cl->getValue();
    }
    array_push($items, $data);
  }
  //save excel database
  $insert_flag = false;

  if ($value == "Hareket.xls") {
    foreach ($items as  $key => $item) {
      if ($key == 0) continue;

      $UNIX_DATE = ($item[2] - 25569) * 86400;
      $animal_earring_id =  $item[1];
      $date = date("Y-m-d H:i:s", strtotime(gmdate("d-m-Y H:i:s", $UNIX_DATE)));
      $movement = $item[3];

      if ($movement < 1) {
        $insert_flag = true;
      } else {

        $sql =  "INSERT INTO `movement` (`id`,`farm_id`, `animal_id`, `transaction_date`, `movement`) VALUES (NULL,'1', '$animal_earring_id', ' $date', '$movement');";
        print_r($sql);
        echo "<br>";
        if (mysqli_query($conn, $sql)) {
          // echo "New record created successfully<br>";
        } else {
          incorrect_registration($sql, $conn, $file, $key, $log_file_name);
        }
      }
    }
  } else if ($value == "Gunluk_Sut.xls") {
    foreach ($items as  $key => $item) {

      if ($key == 0) continue;
      $UNIX_DATE = ($item[1] - 25569) * 86400;
      $date = date("Y-m-d H:i:s", strtotime(gmdate("d-m-Y H:i:s", $UNIX_DATE)));
      $animal_earring_id =   $item[2];
      $milk =  $item[3];
      $conductivity =   $item[4];

      if ($milk < 1 || $conductivity < 1) {
        $insert_flag = true;
      } else {
        $sql = "INSERT INTO `milk` (`id`,`farm_id`, `animal_id`, `transaction_date`, `conductivity`, `milk_quantity`) VALUES (NULL,'1', '$animal_earring_id', '$date', ' $conductivity', ' $milk');";
        print_r($sql);
        echo "<br>";
        if (mysqli_query($conn, $sql)) {
          // echo "New record created successfully<br>";
        } else {
          incorrect_registration($sql, $conn, $file, $key, $log_file_name);
        }
      }
    }
  }

  rename($filenames,  "read-files/" . $user_file_path . "/" . $file);
  $filenames = NULL;
}

function incorrect_registration($sql, $conn, $file, $key, $log_file_name)
{
  echo "Error: " . $sql . "<br>" . mysqli_error($conn);
  $arr = array('Error' =>
  array('Error' =>  mysqli_error($conn), 'sql_query' => $sql, 'date' =>  date("Y.m.d"), 'id' => $key, 'file_name' => $log_file_name));

  $log_file_name = "error_log.json";
  $içerik = "Error " . $sql . "\n" . mysqli_error($conn) . "\n" . $file;
  file_put_contents($log_file_name, json_encode($arr, JSON_PRETTY_PRINT));
  exit;
}


function open_file($file)
{
  $items = [];
  $klasor = opendir($file);
  while ($dosya = readdir($klasor)) {
    if (!is_dir($dosya)) {

      array_push($items, $dosya);
    }
  }
  return $items;
}
echo "</pre>";
