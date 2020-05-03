<?php
//zacatek sessionu pokud jiz nebezi (projistotu)
if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params(time() + 24 * 60 * 60, $_SERVER['CONTEXT_PREFIX']);
    session_start();
}

//Emailovací systém
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

//přístup k databázi
//https://github.com/anzawi/php-database-class
require_once "database/vendor/autoload.php";

use PHPtricks\Orm\Database;;
$db = Database::connect();

//ACCESS RESTRICTION
if (isset($RA) && $RA) {
    if(!isset($_SESSION['user']) || !isset($_COOKIE['user'])) {
        //presmerovani na prihlaseni
        header("Location: prihlaseni.php");
        exit();
    }
    $user = $db->table('spravci')->where('User', '=', $_SESSION['user'])->where('User', '=', $_COOKIE['user'])->select()->last();
    if($user['User'] != $_SESSION['user'] && $user['User'] != $_COOKIE['user']){
        //presmerovani na prihlaseni
        header("Location: prihlaseni.php");
        exit();
    }
} 

//inactivyty timer for admins
if(isset($_SESSION['LAST_ACTIVITY'])){
    if (time() - $_SESSION['LAST_ACTIVITY'] > 60*30) {
        // last request was more than 30 minutes ago
        session_unset();     // unset $_SESSION variable for the run-time 
        session_destroy();   // destroy session data in storage
        setcookie('user', null, time() - 3600); 
        header("Location: odhlaseni.php?inactivity");
    }
    $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp
}

//ziskani IP adresy
//https://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php
function get_ip_address() {

    // Check for shared Internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // Check for IP addresses passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {

        // Check if multiple IP addresses exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (validate_ip($ip))
                    return $ip;
            }
        }
        else {
            if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    //Write to error log
    //error_log("Failed to find IP address!");
    // Return unreliable IP address since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

//
if (isset($RA) && $RA) {
  if(!($_SESSION['IP'] == get_ip_address())) {
          exit("");
      }
}

//preklad mesice do cestiny
function dateMonth($date) {
    $months=[
        '01'=>'ledna',
        '02'=>'února',
        '03'=>'března',
        '04'=>'dubna',
        '05'=>'května',
        '06'=>'června',
        '07'=>'července',
        '08'=>'srpna',
        '09'=>'září',
        '10'=>'října',
        '11'=>'listopadu',
        '12'=>'prosince',
    ];
    return $months[substr($date, 5, 2)];
}

//preklad dnu do cestiny
function dayName($date, $sklon = False, $cap = False) {
    $day=strtotime($date);
    /*
    MON pondělí v pondělí X
    TUE úterý v úterý X
    WED středa ve středu Y
    THU Čtvrtek ve čtvrtek X
    FRI Pátek v pátek X
    SAT Sobota v sobotu Y
    SUN Neděle v neděli Y
    */
    if($sklon && $cap) {
        $dayNames=[
            'Mon'=>'Pondělí',
            'Tue'=>'Úterý',
            'Wed'=>'Středu',
            'Thu'=>'Čtvrtek',
            'Fri'=>'Pátek',
            'Sat'=>'Sobotu',
            'Sun'=>'Neděli'
        ];
    } elseif(!$sklon && $cap) {
        $dayNames=[
            'Mon'=>'Pondělí',
            'Tue'=>'Úterý',
            'Wed'=>'Středa',
            'Thu'=>'Čtvrtek',
            'Fri'=>'Pátek',
            'Sat'=>'Sobota',
            'Sun'=>'Neděle'
        ];
    } elseif($sklon && !$cap) {
        $dayNames=[
            'Mon'=>'pondělí',
            'Tue'=>'úterý',
            'Wed'=>'středu',
            'Thu'=>'čtvrtek',
            'Fri'=>'pátek',
            'Sat'=>'sobotu',
            'Sun'=>'neděli'
        ];
    } else {
        $dayNames=[
            'Mon'=>'pondělí',
            'Tue'=>'úterý',
            'Wed'=>'středa',
            'Thu'=>'čtvrtek',
            'Fri'=>'pátek',
            'Sat'=>'sobota',
            'Sun'=>'neděle'
        ];
    }
    return $dayNames[date("D", $day)];
}

//vytvoreni emailu
function emailMessage($type, $db, $EmailInput, $sendEmail = True) {
    
    include $_SERVER['DOCUMENT_ROOT'].'/PrijimackyNanecisto/config/config.php';

    $eventInfo = $db->table('akce')->select()->last();
    $Occupation = $db->table('prihlaseni')->where('LogedOut', '=', 'Ne')->where('akce_Id', '=', $eventInfo['Id'])->select()->count();
    
    if($eventInfo['ReminderEmail'] == NULL) {
        $ReminderEmail = "Připomínka není nastavena!";
    } else {
        $ReminderEmail = $eventInfo['ReminderEmail'];
    }

    if(!isset($EmailInput[0]) || !(gettype($EmailInput[0]) == 'array')) {

        $Email = $EmailInput;

        if(!isset($Email['RegDateTime'])) {
            $Email['RegDateTime'] = $Email['RegDate']." ".$Email['RegTime'];
        }

        if(!empty($Email['PaidDate'])) {
            $paidDate = date("j", strtotime("1970-01-".substr($Email['PaidDate'], 8, 2))).'. '.dateMonth($Email['PaidDate']).' '.substr($Email['PaidDate'], 0, 4);
            $lhuta = 0;
        } else {
            $lhuta = round(abs(strtotime($Email['MaxPaidDate'])-strtotime($Email['RegDateTime'])) / (60 * 60 * 24));
            $paidDate = "Nenalezeno!";
        }

        $infoTranslate = [
            "|den a datum testu|" => dayName($eventInfo['EventDate']).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|Den a datum testu|" => dayName($eventInfo['EventDate'], False, True).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|den a datum testu sklonene|" => dayName($eventInfo['EventDate'], True).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|Den a datum testu sklonene|" => dayName($eventInfo['EventDate'], True, True).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|datum testu|" => date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|cas testu|" => 'od '.date("G:i", strtotime($eventInfo['EventDate'])).' do '.date("G:i", strtotime($eventInfo['EventEndTime'])).' hodin',
            "|Cas testu|" => 'Od '.date("G:i", strtotime($eventInfo['EventDate'])).' do '.date("G:i", strtotime($eventInfo['EventEndTime'])).' hodin',
            "|den a datum vyhodnoceni|" => dayName($eventInfo['EvaluationDate']).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|Den a datum vyhodnoceni|" => dayName($eventInfo['EvaluationDate'], False, True).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|den a datum vyhodnoceni sklonene|" => dayName($eventInfo['EvaluationDate'], True).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|Den a datum vyhodnoceni sklonene|" => dayName($eventInfo['EvaluationDate'], True, True).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|datum vyhodnoceni|" => date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4), 
            "|cas vyhodnoceni|" => 'od '.date("G:i", strtotime($eventInfo['EvaluationDate'])).' do '.date("G:i",strtotime($eventInfo['EvaluationEndTime'])).' hodin', 
            "|Cas vyhodnoceni|" => 'Od '.date("G:i", strtotime($eventInfo['EvaluationDate'])).' do '.date("G:i",strtotime($eventInfo['EvaluationEndTime'])).' hodin', 
            "|zacatek testu|" => date("G:i", strtotime($eventInfo['EventDate'])),
            "|konec testu|" => date("G:i", strtotime($eventInfo['EventEndTime'])),
            "|zacatek vyhodnoceni|" => date("G:i", strtotime($eventInfo['EvaluationDate'])),
            "|konec vyhodnoceni|" => date("G:i", strtotime($eventInfo['EvaluationEndTime'])),
            "|pocet mist|" => strval($eventInfo['FreeSpace']),
            "|volne misto|" => strval($eventInfo['FreeSpace']-$Occupation['Occupation']),
            "|posledni datum zaplaceni|" => date("j", strtotime("1970-01-".substr($eventInfo['LastPaidDate'], 8, 2))).'. '.dateMonth($eventInfo['LastPaidDate']).' '.substr($eventInfo['LastPaidDate'], 0, 4),
            "|obecna lhuta|" => strval($eventInfo['PaidPeriod']),
            "|cena|" => $eventInfo['Price'], 
            "|pocet prihlasenych|" => $Occupation,
            "|oznameni predem|" => strval($ReminderEmail),
            "|VS|" => $Email['VS'],
            "|jmeno|" => ucfirst($Email['FirstName']),
            "|prijmeni|" => ucfirst($Email['LastName']),
            "|lhuta platby|" => strval($lhuta),
            "|max datum zaplaceni|" => date("j", strtotime("1970-01-".substr($Email['MaxPaidDate'], 8, 2))).'. '.dateMonth($Email['MaxPaidDate']).' '.substr($Email['MaxPaidDate'], 0, 4),
            "|IP|" => $Email['IP'],
            "|skola|" => $Email['School'],
            "|email|" => $Email['Email'],
            "|telefon|" => $Email['Tel'],
            "|datum registrace|" => date("j", strtotime("1970-01-".substr($Email['RegDateTime'], 8, 2))).'. '.dateMonth($Email['RegDateTime']).' '.substr($Email['RegDateTime'], 0, 4),
            "|cas registrace|" => date("G:i", strtotime($Email['RegDateTime'])),
            "|zaplaceno|" => $Email['Paid'],
            "|odhlaseno|" => $Email['LogedOut'],
            "|datum zaplaceni|" => strval($paidDate),
            "|komentar|" => $Email['Comment']
        ];

    } else {
        $infoTranslate = [
            "|den a datum testu|" => lcfirst(dayName($eventInfo['EventDate'])).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|Den a datum testu|" => ucfirst(dayName($eventInfo['EventDate'])).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|den a datum testu sklonene|" => lcfirst(dayName($eventInfo['EventDate'], True)).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|Den a datum testu sklonene|" => ucfirst(dayName($eventInfo['EventDate'], True)).' '.date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|datum testu|" => date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))).'. '.dateMonth($eventInfo['EventDate']).' '.substr($eventInfo['EventDate'], 0, 4),
            "|cas testu|" => 'od '.date("G:i", strtotime($eventInfo['EventDate'])).' do '.date("G:i", strtotime($eventInfo['EventEndTime'])).' hodin',
            "|Cas testu|" => 'Od '.date("G:i", strtotime($eventInfo['EventDate'])).' do '.date("G:i", strtotime($eventInfo['EventEndTime'])).' hodin',
            "|den a datum vyhodnoceni|" => lcfirst(dayName($eventInfo['EvaluationDate'])).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|Den a datum vyhodnoceni|" => ucfirst(dayName($eventInfo['EvaluationDate'])).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|den a datum vyhodnoceni sklonene|" => lcfirst(dayName($eventInfo['EvaluationDate'], True)).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|Den a datum vyhodnoceni sklonene|" => ucfirst(dayName($eventInfo['EvaluationDate'], True)).' '.date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4),
            "|datum vyhodnoceni|" => date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))).'. '.dateMonth($eventInfo['EvaluationDate']).' '.substr($eventInfo['EvaluationDate'], 0, 4), 
            "|cas vyhodnoceni|" => 'od '.date("G:i", strtotime($eventInfo['EvaluationDate'])).' do '.date("G:i",strtotime($eventInfo['EvaluationEndTime'])).' hodin', 
            "|Cas vyhodnoceni|" => 'Od '.date("G:i", strtotime($eventInfo['EvaluationDate'])).' do '.date("G:i",strtotime($eventInfo['EvaluationEndTime'])).' hodin', 
            "|zacatek testu|" => date("G:i", strtotime($eventInfo['EventDate'])),
            "|konec testu|" => date("G:i", strtotime($eventInfo['EventEndTime'])),
            "|zacatek vyhodnoceni|" => date("G:i", strtotime($eventInfo['EvaluationDate'])),
            "|konec vyhodnoceni|" => date("G:i", strtotime($eventInfo['EvaluationEndTime'])),
            "|pocet mist|" => strval($eventInfo['FreeSpace']),
            "|volne misto|" => strval($eventInfo['FreeSpace']-$Occupation['Occupation']),
            "|posledni datum zaplaceni|" => date("j", strtotime("1970-01-".substr($eventInfo['LastPaidDate'], 8, 2))).'. '.dateMonth($eventInfo['LastPaidDate']).' '.substr($eventInfo['LastPaidDate'], 0, 4),
            "|obecna lhuta|" => strval($eventInfo['PaidPeriod']),
            "|cena|" => $eventInfo['Price'], 
            "|pocet prihlasenych|" => $Occupation,
            "|oznameni predem|" => strval($ReminderEmail),
        ];
    }

    $messageRaw = $db->table('emaily')->where('Type', '=', $type)->select()->first();

    $message = strtr($messageRaw['Email'], $infoTranslate);
    
    if($sendEmail) {

        $mail = new PHPMailer(true);

        try{
            //SEND EMAIL
            if(!isset($EmailInput[0]) || !(gettype($EmailInput[0]) == 'array')) {
                $to = $EmailInput['Email'];
            } else {
                $to = $Email_user;
                foreach($EmailInput as $Email) {
                    $mail->addBCC($Email['Email']);
                }
            }
            $subject = $messageRaw['Subject'];

            //Server settings
            $mail->SMTPDebug  = 0;                                      // Enable verbose debug output
            $mail->isSMTP();                                            // Set mailer to use SMTP
            $mail->Host       = $Email_host;                                  // Specify main and backup SMTP servers
            $mail->SMTPAuth   = $Email_SMTPAuth;                                   // Enable SMTP authentication
            $mail->Username   = $Email_user;                              // SMTP username
            $mail->Password   = $Email_psw;                              // SMTP password
            $mail->SMTPSecure = $Email_SMTPSecure;                            // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = $Email_port;                                  // TCP port to connect to

            //Reply
            $mail->addReplyTo($Email_replyTo, $Email_replyTo_visibleName);
            //Recipients
            $mail->setFrom($Email_user, $Email_visible_name);

            $mail->addAddress($to);                                     
            

            // Content
            $mail->isHTML(true);                                      // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $message;
            $mail->AltBody = strip_tags(strtr($message, ['</p>' => '</p><br>', '</div>' => '</div><br>']), '<br><br />');

            $mail->setLanguage('cs', '/optional/path/to/language/directory/');

            $mail->send();
        } catch(Exception $e) {
            return $mail->ErrorInfo;
        }
        return True;
    } else {
        return $message;
    }
}

$eventInfo = $db->table('akce')->select()->last();
$Occupation = $db->table('prihlaseni')->where('LogedOut', '=', 'Ne')->where('akce_Id', $eventInfo['Id'])->select()->count();