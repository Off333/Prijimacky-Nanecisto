<?php 
$RA = TRUE;
require_once "funkce.php";

if(!isset($eventInfo['StartRegDate']) || empty($eventInfo['StartRegDate']) || $eventInfo['StartRegDate'] == NULL) {
    header("Location: admin.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {

    $authorId = $db->table('spravci')->where('User', '=', $_SESSION['user'])->select()->first()['Id'];

    $db->table('emaily')->where('type', '=', $_POST['type'])->update([
        'Email' => $_POST['Email'],
        'Subject' => $_POST['Subject'],
        'LastChangedBy' => $authorId
    ]);
}

$type="register";
if(isset($_GET['type'])) {
    $type=$_GET['type'];
}

$mailPreview = $db->table('emaily')->where('Type', '=', $type)->select()->first();

$tempEmail = [
    "Id" => "6",
    "IP" => "127.0.0.1",
    "FirstName" => "Jan",
    "LastName" => "Novák",
    "School" => "SPŠBRNO, brno",
    "Email" => "email@seznam.cz",
    "Tel" => "+420 123 345 678",
    "VS" => "2019006",
    "RegDateTime" => "2019-06-05 22:21:21",
    "Paid" => "Ne",
    "PaidDate" => "",
    "MaxPaidDate" => "2019-06-07",
    "LogedOut" => "Ne",
    "Comment" => "poznamka",
    "akce_Id" => "2"
];

$title=$_SESSION['user']." - Změna Emailů";
$extScript="tinymce/tinymce.min.js";
$script='
    tinymce.init({
        selector: \'#WYCIWYG\',
        language: \'cs\'
    });';

include_once "html/head.php";
include_once "html/header.php";
include_once "html/nav.php";

$register = '';
$pay = '';
$logOut = '';
$reminder = '';
switch($type) {
    case "register":
        $register = 'selected';
        break;
    case "pay":
        $pay = 'selected';
        break;
    case "logOut":
        $logOut = 'selected';
        break;
    case "reminder":
        $reminder = 'selected';
        break;   
}

?>
<article>
    <div class="container bg-white mt-3 py-4">
        <form method="get">
            <p class="h3">Výběr typu emailu:
            <select name="type" onchange="this.form.submit()">
                <option value="register" <?php echo $register; ?>>Oznámení registarci</option>
                <option value="pay" <?php echo $pay; ?>>Potvrzení zaplacení</option>
                <option value="logOut" <?php echo $logOut; ?>>Oznámení odhlášení</option>
                <option value="reminder" <?php echo $reminder; ?>>Připomínka před akcí</option>
            </select>
            </p>
        </form>
        <button class="btn btn-info my-3" type="button" data-toggle="collapse" data-target="#hint" aria-expanded="false" aria-controls="hint">Nápověda k zadávání emailů</button>
        <div id="hint" class="collapse">
            <h5>Jak používat proměnné informace</h5>
            <p>Všechny proměnné se budou měnit vždy pro jednotlivé uživatele a podle právě probíhající akce</p>
            <p>Jejich zápis je malými znaky bez diakritiky: <strong>jmeno</strong></p>
            <p>Pro ohraničení proměnné informace je zapotřebí ji ohraničit svislítkami na obou stranách: <strong>| |</strong> </p>
            <p><i><small>svislítko se píše pomocí: <kbd>Pravý Alt+w</kbd> <br><a class="alert-link" href="http://znakynaklavesnici.cz/svisla-cara/">návod jak napsat |</a> </small></i></p>
            <p>Výsledný tvar tedy bude vypadat takto: <strong>|jmeno|</strong></p>
            <p>Zde je kompletní seznam možných proměnných:</p>
            <p>
            <small>
            <code>
                |den a datum testu|<br>
                |den a datum testu sklonene|<br>
                |Den a datum testu|<br>
                |Den a datum testu sklonene|<br>
                |datum testu|<br>
                |cas testu|<br>
                |Cas testu|<br>
                |den a datum vyhodnoceni|<br>
                |den a datum vyhodnoceni sklonene|<br>
                |Den a datum vyhodnoceni|<br>
                |Den a datum vyhodnoceni sklonene|<br>
                |datum vyhodnoceni|<br>
                |cas vyhodnoceni|<br>
                |Cas vyhodnoceni|<br>
                |zacatek testu|<br>
                |konec testu|<br>
                |zacatek vyhodnoceni|<br>
                |konec vyhodnoceni|<br>
                |pocet mist|<br>
                |volne misto|<br>
                |posledni datum zaplaceni|<br>
                |obecna lhuta|<br>
                |cena|<br>
                |VS|<br>
                |jmeno|<br>
                |prijmeni|<br>
                |lhuta platby|<br>
                |max datum zaplaceni|<br>
                |IP|<br>
                |skola|<br>
                |email|<br>
                |telefon|<br>
                |datum registrace|<br>
                |cas registrace|<br>
                |zaplaceno|<br>
                |odhlaseno|<br>
                |datum zaplaceni|<br>
                |komentar|<br>
                |pocet prihlasenych|<br>
                |oznameni predem|
            </code>
            </small>
            </p>
        </div>
    </div>
    <div class="container bg-white mt-3 py-4">
        <form method="post">
            <input name="type" value="<?php echo $type; ?>" hidden>
            <div class="form-group row ml-2">
            <label for="inputSubject" class="col-sm-2 col-form-label"><p class="h4">Předmět</p></label>
            <div class="col-sm-10 input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" title="Přijímačky nanečisto -">Přijímačky nanečisto -</span>
                </div>
                <input id="inputSubject" class="form-control" type="text" name="Subject" aria-label="Předmět" title="Předmět emailu" placeholder="Předmět" value="<?php echo $mailPreview['Subject']; ?>" required>
            </div>
        </div>
            <textarea name="Email" id="WYCIWYG" style="min-height: 600px; overflow:hidden"><?php echo $mailPreview['Email']; ?></textarea>
            <input class="btn btn-success my-2" type="submit" name="submit" value="Změnit">
        </form>
    </div>
    <div class="container bg-white mt-3 py-4">
        <button class="btn btn-warning my-3" type="button" data-toggle="collapse" data-target="#preview" aria-expanded="false" aria-controls="preview" onclick="setTimeout(function () {window.scrollTo(0,document.body.scrollHeight);}, 230);">Doplněný Email</button>
        
        <div id="preview" class="collapse container-fluid m-2"><?php echo EmailMessage($type, $db, $tempEmail, False); ?></div>
    </div>
</article>

<?php
include_once "html/footer.php";
?>