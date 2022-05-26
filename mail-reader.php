<?php
include 'conn.php';
date_default_timezone_set('Europe/Istanbul');
echo "<pre>";

//get user data list

$userlist = array();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

//!!
$sql = "SELECT mail FROM `user`";
$result = $conn->query($sql);
if ($result->num_rows > 0) {

    while ($row = $result->fetch_assoc()) {
        $userlist[]= $row["mail"];
    }
} else {
    echo "0 results";
}
$conn->close();
print_r($userlist);

//check mail of user list
foreach ($userlist as  &$user) {

    $user_name = DeleteTurkishCharacters($user);
    print_r($user);
    echo "<br>";
    //create user folder
    $file_path_name = "unread-files\\" . $user_name;
    $olustur = mkdir($file_path_name);
    if ($olustur) echo "Klasör Oluşturuldu.<br>";
    else echo "Klasör Oluşturulamadı<br> ";

    set_time_limit(1000);

    /* connect to gmail with your credentials */
    $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}INBOX'; //'{imap.gmail.com:993/imap/ssl}INBOX'
    $username = 'makuuygar@gmail.com'; # e.g somebody@gmail.com
    $password = 'makuuygar.2021';

    /* try to connect */
    $inbox = imap_open($hostname, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

$emails = imap_search($inbox, 'from "' . $user . '"');
print_r($emails);
    if ($emails) { 

        $count = 1;

        /* for every email... */
        foreach ($emails as $email_number) {

            /* get information specific to this email */
            $overview = imap_fetch_overview($inbox, $email_number, 0);
            echo "<br>";
            $username =  $overview[0]->from;
            $username =  imap_utf8($username);
            // print_r($overview);

            $seleted_id = $overview[0]->uid;
            $seleted_date = $overview[0]->date;

            //    if ($seleted_id > $last_save_id) {

            /* get mail structure */
            $structure = imap_fetchstructure($inbox, $email_number);

            $attachments = array();

            /* if any attachments found... */
            if (isset($structure->parts) && count($structure->parts)) {
                for ($i = 0; $i < count($structure->parts); $i++) {
                    $attachments[$i] = array(
                        'is_attachment' => false,
                        'filename' => '',
                        'name' => '',
                        'attachment' => ''
                    );

                    if ($structure->parts[$i]->ifdparameters) {
                        foreach ($structure->parts[$i]->dparameters as $object) {
                            if (strtolower($object->attribute) == 'filename') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['filename'] = $object->value;
                            }
                        }
                    }

                    if ($structure->parts[$i]->ifparameters) {
                        foreach ($structure->parts[$i]->parameters as $object) {
                            if (strtolower($object->attribute) == 'name') {
                                $attachments[$i]['is_attachment'] = true;
                                $attachments[$i]['name'] = $object->value;
                            }
                        }
                    }

                    if ($attachments[$i]['is_attachment']) {
                        $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);

                        /* 4 = QUOTED-PRINTABLE encoding */
                        if ($structure->parts[$i]->encoding == 3) {
                            $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                        }
                        /* 3 = BASE64 encoding */ elseif ($structure->parts[$i]->encoding == 4) {
                            $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                        }
                    }
                }
            }

            /* iterate through each attachment and save it */
            foreach ($attachments as $attachment) {

                if ($attachment['is_attachment'] == 1) {
                    $filename = imap_utf8($attachment['name']);
                    if (empty($filename)) $filename = $attachment['filename'];

                    if (empty($filename)) $filename = time() . ".dat";

                    $date = strtotime($seleted_date);
                    $new_filename = DeleteTurkishCharacters($filename);
                    echo  $new_filename . "<br>";

                    $fp = fopen($file_path_name . "\\" . $date . "-" . $new_filename, "w+");
                    fwrite($fp, $attachment['attachment']);

                    fclose($fp);
                }
            }
            //Belirtilen iletiyi silindi olarak imler
            // imap_delete($inbox, $email_number);
        }
    }
}
echo "</pre>";

// Silindi imli tüm iletileri gerçekten siler
// imap_expunge($inbox); 

imap_close($inbox);

echo "Done";



function DeleteTurkishCharacters($user)
{
    $user = mb_convert_encoding($user, "UTF-8", "auto");
    $search = array('Ç', 'ç', 'Ğ', 'ğ', 'ı', 'İ', 'Ö', 'ö', 'Ş', 'ş', 'Ü', 'ü', 'ü', ' '); //Do not touch
    $replace = array('C', 'c', 'G', 'g', 'i', 'I', 'O', 'o', 'S', 's', 'U', 'u', 'u', '_');
    $file_path_name =  str_replace($search, $replace, $user);
    return  $file_path_name;
}
