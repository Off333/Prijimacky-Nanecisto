<?php
$RA = TRUE;
require_once "funkce.php";

$authorId = $db->table('spravci')->where('User', '=', $_SESSION['user'])->select()->first()['Id'];
$headerId = $db->table('header')->select()->last()['Id'];

if(!isset($eventInfo) || empty($eventInfo)) {
    $eventInfo = [
        'StartRegDate' => '',
        'EndRegDate' => '',
        'LastPaidDate' => '',
        'PaidPeriod' => '',
        'Price' => '',
        'FreeSpace' => '',
        'EventDate' => '',
        'EventEndTime' => '',
        'EventFirstBlockLenght' => '',
        'EventPause' => '',
        'EvaluationDate' => '',
        'EvaluationEndTime' => '',
        'EvaluationFirstBlockLenght' => '',
        'EvaluatPause' => '',
        'ReminderEmail' => '',
        'header_Id' => $headerId,
        'LastChangedBy' => $authorId
    ];
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    if(isset($_POST['change'])) {

        $ReminderEmail = null;
        if(isset($_POST['ReminderEmail']) && !empty($_POST['ReminderEmail'])) {
            $ReminderEmail = $_POST['ReminderEmail'];
        }

        $eventData = [
            'FreeSpace' => $_POST['FreeSpace'],
            'Price' => $_POST['Price'],
            'StartRegDate' => $_POST['StartRegDate'],
            'EndRegDate' => $_POST['EndRegDate'],
            'LastPaidDate' => $_POST['LastPaidDate'],
            'PaidPeriod' => $_POST['PaidPeriod'],
            'EventDate' => $_POST['EventDate']." ".$_POST['EventTimeStart'],
            'EventEndTime' => $_POST['EventEndTime'],
            'EvaluationDate' => $_POST['EvaluationDate']." ".$_POST['EvaluationTimeStart'],
            'EvaluationEndTime' => $_POST['EvaluationEndTime'],
            'ReminderEmail' => $ReminderEmail,
            'header_Id' => $headerId,
            'LastChangedBy' => $authorId
        ];

        if(isset($eventInfo['Id'])) {
            $db->table('akce')->where('Id', $eventInfo['Id'])->update($eventData);

        } else {
            $db->table('akce')->where('Id', $eventInfo['Id'])->insert($eventData);
        }

        

        header("Location: admin.php");

    } elseif(isset($_POST['delete'])) {
        $db->table('akce')->insert([
            'Id' => NULL,
            'header_Id' => $headerId
        ]);

        if($Occupation > 0 && strtotime($eventInfo['EvaluationDate']) > time()) {
            ?>
            <script>
                if(confirm('Byly nalezeny záznamy v předčasně ukončené akci, chcete je přesunout do další­?')) {
                    window.location.replace('?pushOccupations');
                } else {
                    window.location.replace('?')
                }
            </script>

            <?php
        } else {
            header("Location: admin.php");
        }
    } elseif(isset($_POST['changeHeader'])) {
        $db->table('header')->insert(['EventHeader' => $_POST['EventHeader']]);
        $db->table('akce')->update(['header_Id' => ($headerId+1)]);
    }

} elseif($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['pushOccupations'])) {
    $db->table('prihlaseni')->where('LogedOut', '=', 'Ne')->where('akce_Id', ($eventInfo['Id']-1))->update(['akce_Id' => $eventInfo['Id']]);
    header("Location: admin.php");
}

//vypis navigace pro spravce
$title=$_SESSION['user']." - Hlavní­ panel";
$logInUser=$_SESSION['user'];
$extScript="tinymce/tinymce.min.js";
$script="
    tinymce.init({
        selector: '#WYCIWYG',
        language: 'cs',
        height : 600
    });
"; // JS Script
include_once "html/head.php";
include_once "html/header.php";
include_once "html/nav.php";

$eventDate = "09:00";
if(!empty($eventInfo['EventDate'])) {
    $eventDate = substr($eventInfo['EventDate'], 11);
}

$eventEndTime = "11:30";
if(!empty($eventInfo['EventEndTime'])) {
    $eventEndTime = $eventInfo['EventEndTime'];
}

$evaluationDate = "09:00";
if(!empty($eventInfo['EvaluationDate'])) {
    $evaluationDate = substr($eventInfo['EvaluationDate'], 11);
}

$evaluationEndTime = "11:30";
if(!empty($eventInfo['EvaluationEndTime'])) {
    $evaluationEndTime = $eventInfo['EvaluationEndTime'];
}

$changeButtonDisable = "";
$changeButtonText = "Vytvořit novou akci";
if((isset($eventInfo['Id'])) && (isset($eventInfo['StartRegDate'])) && (isset($eventInfo['EndRegDate']))) {

    $changeButtonText = "Změnit nastávají­cí­ akci";
    if(strtotime($eventInfo['StartRegDate']) < time()) {
        $changeButtonText = "Nelze změnit probí­hají­cí­ akci";
        $changeButtonDisable = "disabled";

    } elseif(strtotime($eventInfo['EndRegDate']) < time()) {
        $changeButtonText = "Akce již proběhla";
        $changeButtonDisable = "disabled";
    }
}

$deleteButtonText = "Ukončit předešlou akci";
if(strtotime($eventInfo['StartRegDate']) > time()) {
    $deleteButtonText = "Předčasně ukončit nastávají­cí­ akci";
} elseif(strtotime($eventInfo['EndRegDate']) > time()) {
    $deleteButtonText = "Ukončit probí­hají­cí­ akci";
}

?>
<article> 

<?php
if((isset($eventInfo['Id'])) && (!isset($eventInfo['StartRegDate'])) && (!isset($eventInfo['EndRegDate'])) && $Occupation > 0) {

    ?>
    <div class="container mt-3 py-auto">
    <div class="alert alert-warning">
        <p>V této akci jsou již zaregistrovaní­ účastsní­ci!</p>
        <small>Bylo by dobré, co nejdří­ve akci vytvořit.</small>
    </div>
    <?php
}

?>
<form method="post" action="" onsubmit="return validate()">
    <div class="container bg-white mt-3 py-auto pt-2">
        <a class="btn btn-secondary ml-3 my-3" data-toggle="collapse" role="button" href="#inputWYSIWYG" aria-expanded="false" aria-controls="inputWYSIWIG"><legend for="WYCIWYG" class="form-label h5">Popis akce na hlavní­ stránce</legend></a>
    <div class="form-group collapse" id="inputWYSIWYG">
            <textarea id="WYCIWYG" class="form-control" name="EventHeader"> <?php echo $db->table('header')->where('Id', '=', $headerId)->select()->first(); ?></textarea>
        <button class="btn btn-success my-2" type="button" name="changeHeader">Uložit popis</button>
    </div>
    </div>

    <div class="container bg-white mt-3 py-auto pt-2">
        <div class="mb-2 mt-4 ml-3">
                <p class="h5">Termí­n registrací­ a platba</p>
        </div>

        <div class="form-group row ml-2">
            <label for="inputStartRegDate" class="col-sm-2 col-form-label">Termí­n registrace</label>
            <div class="col-sm-10 input-group">
                <input id="inputStartRegDate" onchange="changeMin(this, 'EndRegDate')" class="form-control" type="date" name="StartRegDate" placeholder="Začátek registrace" aria-label="Začátek registrace" title="Začátek registrace. První­ den, během kterého lze vyplnit a poslat el. přihlášku" value="<?php echo $eventInfo['StartRegDate']; ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text" title="až">-</span>
                </div>
                <input id="inputEndRegDate" onchange="changeMin(this, 'LastPaidDate'); changeMax(this, 'StartRegDate'); calculateReminder()" class="form-control" type="date" name="EndRegDate" placeholder="Konec registrace" aria-label="Konec registrace" title="Konec registrace. Poslední­ den, do kterého lze vyplnit a poslat el. přihlášku" value="<?php echo $eventInfo['EndRegDate']; ?>" required <?php echo $changeButtonDisable; ?>>
            </div>
        </div>

        <div class="form-group row ml-2">
            <label for="inputLastPaidDate" class="col-sm-2 col-form-label">Lhůty platby</label>
            <div class="col-sm-10 input-group">
                <input id="inputLastPaidDate" class="form-control" type="date" name="LastPaidDate" aria-label="Poslední­ možné datum zaplacení­" title="Poslední­ možný den zaplacení­" onchange="changeMin(this, 'EventDate'); changeMax(this, 'EndRegDate')" placeholder="Poslední­ možné datum zaplacení­" value="<?php echo $eventInfo['LastPaidDate']; ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text" title="nebo">|</span>
                </div>
                <input id="inputPaidPeriod" class="form-control" type="number" name="PaidPeriod" aria-label="Lhůta zaplacení­" title="Lhůta jednotlivého přihlášeného na zaplacení­" placeholder="Lhůta zaplacení­" min="1" value="<?php echo $eventInfo['PaidPeriod']; ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-append">
                    <span class="input-group-text" title="dnů po přihlášení­">dnů po přihlášení­</span>
                </div>
            </div>
        </div>

        <div class="form-group row ml-2">
            <label for="inputReminderEmail" class="col-sm-2 col-form-label">Připomí­nka</label>
            <div class="col-sm-10 input-group">
                <input id="reminderEmail" class="form-control" type="number" name="ReminderEmail" aria-label="Odeslání­ hromadné Emailu" title="Odeslání­ hromadné Emailu" placeholder="Odeslání­ hromadné Emailu" min="1" value="<?php echo $eventInfo['ReminderEmail']; ?>" <?php echo $changeButtonDisable; ?>>
                <div class="input-group-append">
                    <span class="input-group-text" title="dnů před akcí­">dnů před akcí­</span>
                </div>
            </div>
        </div>
        
        <div class="mb-2 mt-4 ml-3">
                <p class="h5">Cena a počet mí­st</p>
        </div>

        <div class="form-group row ml-2">
            <label for="inputPrice" class="col-sm-2 col-form-label">Cena akce</label>
            <div class="col-sm-10 input-group">
                <input id="inputPrice" class="form-control" type="number" name="Price" aria-label="Cena akce" title="Cena akce" placeholder="Cena akce" min="0" value="<?php echo $eventInfo['Price']; ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-append">
                    <span class="input-group-text" title="Korun českých">Kč</span>
                </div>
            </div>
        </div>

        <div class="form-group row ml-2">
            <label for="inputFreeSpace" class="col-sm-2 col-form-label">Počet volných mí­st</label>
            <div class="col-sm-10">
                <input id="inputFreeSpace" class="form-control" type="number" name="FreeSpace" aria-label="Počet volných mí­st" title="Počet volných mí­st. Může být i vyšší­ pokud bude přihlašovat správce" placeholder="Počet volných mí­st" min="1" value="<?php echo $eventInfo['FreeSpace']; ?>" required <?php echo $changeButtonDisable; ?>>
            </div>
        </div>

        <div class="mb-2 mt-4 ml-3">
                <p class="h5">Specifikace testu</p>
        </div>

        <div class="form-group row ml-2">
            <label for="inputEventDate" class="col-sm-2 col-form-label">Test</label>
            <div class="col-sm-10 input-group">
                <input id="inputEventDate" class="form-control" type="date" name="EventDate" aria-label="Datum testu" title="Datum testu" onchange="changeMin(this, 'EvaluationDate'); changeMax(this, 'LastPaidDate'); calculateReminder()" placeholder="Datum testu" value="<?php echo substr($eventInfo['EventDate'], 0, 10); ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text">&nbsp;</span>
                </div>
                <input id="inputEventTimeStart" class="form-control" type="time" name="EventTimeStart" aria-label="Začátek testu" title="Začátek testu" onchange="changeMin(this, 'EventEndTime')" placeholder="Začátek testu" value="<?php echo $eventDate; ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text" title="až">-</span>
                </div>
                <input id="inputEventEndTime" class="form-control" type="time" name="EventEndTime" aria-label="Konec testu" title="Konec testu" onchange="changeMax(this, 'EventTimeStart')" placeholder="Konec testu" value="<?php echo $eventEndTime; ?>" required <?php echo $changeButtonDisable; ?>>
            </div>
        </div>

        <div class="form-group row ml-2">
            <label for="inputEvaluationDate" class="col-sm-2 col-form-label">Vyhodnocení­</label>
            <div class="col-sm-10 input-group">
                <input id="inputEvaluationDate" class="form-control" type="date" name="EvaluationDate" aria-label="Datum vyhodnodení­" title="Datum vyhodnodení­" onchange="changeMax(this, 'EventDate')" placeholder="Datum vyhodnodení­" value="<?php echo substr($eventInfo['EvaluationDate'], 0, 10); ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text">&nbsp;</span>
                </div>
                <input id="inputEvaluationTimeStart" class="form-control" type="time" name="EvaluationTimeStart" aria-label="Začátek vyhodnodení­" title="Začátek vyhodnodení­" onchange="changeMin(this, 'EvaluationEndTime')" placeholder="Začátek vyhodnodení­" value="<?php echo $evaluationDate; ?>" required <?php echo $changeButtonDisable; ?>>
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text" title="až">-</span>
                </div>
                <input id="inputEvaluationEndTime" class="form-control" type="time" name="EvaluationEndTime" aria-label="Konec vyhodnodení­" title="Konec vyhodnodení­" onchange="changeMax(this, 'EvaluationTimeStart')" placeholder="Konec vyhodnodení­" value="<?php echo $evaluationEndTime; ?>" required <?php echo $changeButtonDisable; ?>>
            </div>
        </div>
    </div>

    <div class="container bg-white mt-3 py-auto py-2">
        <div class="ml-3 my-3">
        <input class="btn btn-warning" type="submit" name="change"<?php echo "value=\"". $changeButtonText. "\" ". $changeButtonDisable; ?>>
        </div>
    

<?php
if((isset($eventInfo['Id'])) && (isset($eventInfo['StartRegDate'])) && (isset($eventInfo['EndRegDate']))) {
    ?>
    <div class="ml-3 mb-3">
        <form method="post" action="" onSubmit="return confirm('Ukončit akci?')">
            <input class="btn btn-danger" type="submit" name="delete" value="<?php echo $deleteButtonText; ?>">
        </form>
    </div>
<?php 
}


?>
    </div>
</form>
</article>
<?php
$endScript="JS/admin.js";
include_once "html/footer.php";
?>