<?php
require_once "funkce.php";

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: registrace.php?chyba=Access method error - pristup");
    exit();
}

//Kontrola reCAPTCHE
//https://www.google.com/recaptcha/admin/site/346542945

// grab recaptcha library
include_once "recaptchalib.php";

$err = 'NESPECIFIKOVANY ERROR';

try {
    if (empty($_SESSION['user'])) {
        if($Occupation >= $eventInfo['FreeSpace'] || !isset($eventInfo['StartRegDate']) || time() < strtotime($eventInfo['StartRegDate']) || time() > strtotime($eventInfo['EndRegDate'])) {
            header("Location:registrace.php?chyba=Invalid parameter(s)- nesplnene parametry akce params(".($eventInfo['FreeSpace'] - $Occupation)." <= 0 OR ".$eventInfo['StartRegDate']." is not set OR ".time()." > ".strtotime($eventInfo['StartRegDate'])." OR ".time()." < ".strtotime($eventInfo['EndRegDate']).")");
            exit();
        }
    
        // your secret key
        $secret = "6LfPErwUAAAAAJZIK-QK8TnPoZXS0A9ZbI5ZZmXi";
        
        // empty response
        $response = null;
        
        // check secret key
        $reCaptcha = new ReCaptcha($secret);
    
        // if submitted check response
        if ($_POST["g-recaptcha-response"]) {
            $response = $reCaptcha->verifyResponse(
                $_SERVER["REMOTE_ADDR"],
                $_POST["g-recaptcha-response"]
            );
        }

        if (!($response != null && $response->success)) {
            throw new Exception("REcaptcha");
        }
    }

    if(substr($db->table('prihlaseni')->orderBy('VS', 'DESC')->select()->first()['VS'], 0, 4) === substr($eventInfo['EventDate'], 0, 4)) {
        $VS = (intval($db->table('prihlaseni')->orderBy('VS', 'DESC')->select()->first()['VS']) + 1);
    } else {
        $VS = (intval(substr($eventInfo['EventDate'], 0, 4)) * 1000 + 1);
    }

    $prefix = filter_input(INPUT_POST, 'Prefix', FILTER_SANITIZE_NUMBER_INT);
    if($prefix == "" || $prefix == null || $prefix < 100) {
        $prefix = 420;
    }

    $db->table('prihlaseni')->insert([
        'Id' => NULL, 
        'IP' => get_ip_address(), 
        'FirstName' => ucfirst(filter_input(INPUT_POST, 'FirstName', FILTER_SANITIZE_FULL_SPECIAL_CHARS)), 
        'LastName' => ucfirst(filter_input(INPUT_POST, 'LastName', FILTER_SANITIZE_FULL_SPECIAL_CHARS)), 
        'School' => filter_input(INPUT_POST, 'School', FILTER_SANITIZE_FULL_SPECIAL_CHARS), 
        'Email' => filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL), 
        'Tel' => "+". $prefix ." ". $_POST['Tel'], 
        'VS' => $VS, 
        'Paid' => 'Ne', 
        'PaidDate' => NULL,
        'MaxPaidDate' => date('Y-m-d', min(strtotime(substr(date("Y-m-d"), 0, 10)." +".$eventInfo['PaidPeriod']." days"), strtotime($eventInfo['LastPaidDate']))),
        'LogedOut' => 'Ne',
        'Comment' => filter_input(INPUT_POST, 'Comment', FILTER_SANITIZE_FULL_SPECIAL_CHARS),
        'akce_Id' => $eventInfo['Id']
    ]);

    $err = false;

} catch(Exception $e) {

    $err = $e->getMessage();
    header("Location:registrace.php?chyba=".$err);
    exit();

}
header("Location: potvrzeni.php?email=". (filter_input(INPUT_POST, 'Email', FILTER_SANITIZE_EMAIL)) ."&err=". urlencode($err));
exit();
?>	