<?php
require_once "funkce.php";

$title="Příjímačky nanečisto - Střední průmyslová škola Brno, Purkyňova";
include_once "html/head.php";
include_once "html/header.php";
include_once "html/nav.php";

$alert="";
if(isset($eventInfo['EventDate']) && !empty($eventInfo['EventDate'])) {
    $startReg = date("j", strtotime("1970-01-".substr($eventInfo['StartRegDate'], 8, 2))). ". ". dateMonth($eventInfo['StartRegDate']). " ". substr($eventInfo['StartRegDate'], 0, 4);
    $endReg = date("j", strtotime("1970-01-".substr($eventInfo['EndRegDate'], 8, 2))). ". ". dateMonth($eventInfo['EndRegDate']). " ". substr($eventInfo['EndRegDate'], 0, 4);
}

if(isset($_GET['alert'])){
    $alert = '<div class="container mt-3 py-auto">';
    switch($_GET['alert']) {
        case 'logedOut':
            $alert .= '
            <div class="alert alert-danger alert-dismissable" role="alert">
                Byly jste úspěšně odhlášeni!
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>';
            break;
        case 'logedOutInactivity':
        $alert .= '
        <div class="alert alert-danger alert-dismissable" role="alert">
            Byly jste automaticky odhlášeni z důvodu neaktivity! <a class="alert-link" href="prihlaseni.php">přihlásit se zpět</a>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>';
            break;
        case 'noSpaceAvailable':
            $alert .= "
        <div class='alert alert-warning'>
            Na PŘIJÍMAČKY NANEČISTO se již v tuto chvíli nelze přihlásit. Bylo dosaženo kapacity, kterou škola disponuje. Děkujeme za pochopení.
        </div>";
            break;
        case 'regTooSoon':
            $alert .= "
        <div class='alert alert-warning'>
            Na PŘIJÍMAČKY NANEČISTO se ještě v tuto chvíli nelze přihlásit. Přihlásit se budete moct od <strong>{$startReg}</strong> !
        </div>";
            break;
        case 'regTooLate':
            $alert .= "
        <div class='alert alert-warning'>
            Na PŘIJÍMAČKY NANEČISTO se již v tuto chvíli nelze přihlásit. Děkujeme za pochopení.
        </div>";
            break;
        case 'noEvent':
            $alert .= "
        <div class='alert alert-warning'>
        Na PŘIJÍMAČKY NANEČISTO se nelze přihlásit. <i>Termíny přihlášení a konání bude upřesněn na podzim.</i>
        </div>";
            break;
        case 'regSuccess':
            $alert .= "
        <div class='alert alert-success'>
        <p class='text-success h5'>Přihláška potvrzena!</p> Byl odeslám email s dalšími informacemi na adresu: <strong>".$_GET['email']."</strong><p><a class='alert-link' href='potvrzeni.php?email=".$_GET['email']."'>Odeslat email znovu</a></p></p>
        </div>";
            break;
        case 'regFail':
            $alert .= "
        <div class='alert alert-alert'>
        <p class='text-alert h5'>Email se nepodařilo odeslat!</p> Zadaná emailová adresa: <strong>".$_GET['email']."</strong><p><a class='alert-link' href='potvrzeni.php?email=".$_GET['email']."'>Odeslat email znovu</a></p></p>
        </div>";
            break;
        default:
            $alert .= "
        <div class='alert alert-warning'>
        ".filter_input(INPUT_GET, "alert", FILTER_SANITIZE_FULL_SPECIAL_CHARS)."
        </div>";
            break;
    }
    $alert .= "</div>";
}

?>
<article class="mx-0 px-0 container-fluid">
<div class="row px-5 mr-0">
<div class="col-xl-1 col-0"></div>
<div class="col-xl-10 col-12">

<?php echo $alert;?>

<div class='mycontainer container-white'>
    <?php echo $db->table('header')->where('Id', '=', $eventInfo['header_Id'])->select()->first()['EventHeader']; ?>
</div>

<div class='mycontainer container-white'>
<?php
if(isset($eventInfo['EndRegDate']) && date("Y-m-d") <= $eventInfo['EndRegDate']) {
    $eventDate = (date("j", strtotime("1970-01-".substr($eventInfo['EventDate'], 8, 2))). ". ". dateMonth($eventInfo['EventDate']). " ". substr($eventInfo['EventDate'], 0, 4). " od ". date("G:i", strtotime($eventInfo['EventDate'])). " do ". date("G:i",strtotime($eventInfo['EventEndTime'])). " hod.");
    $evaluationDate = date("j", strtotime("1970-01-".substr($eventInfo['EvaluationDate'], 8, 2))). ". ". dateMonth($eventInfo['EvaluationDate']). " ". substr($eventInfo['EvaluationDate'], 0, 4). " od ". date("G:i", strtotime($eventInfo['EvaluationDate'])). " do ". date("G:i",strtotime($eventInfo['EvaluationEndTime'])). " hod.";
    ?>
    
        <p>
            <strong><?php echo $eventDate; ?></strong> proběhne vypracování testu (1 hodina matematiky, 1 hodina českého jazyka)
        </p>
        <p>
            <strong><?php echo $evaluationDate; ?></strong> proběhne vyhodnocení testu (1 hodina matematiky, 1 hodina českého jazyka)
        </p>
        <p>
            PŘIJÍMAČKY NANEČISTO se uskuteční v budově SPŠ, Purkyňova 97, poplatek je <strong><?php echo $eventInfo['Price']; ?>,- Kč.</strong>
        </p>
        <p>
            &nbsp;
        </p>
    <?php
    if(date("Y-m-d") < $eventInfo['StartRegDate']) {
        ?>
        <p>
            Elektronickou přihlášku můžete vyplnit od <strong><?php echo $startReg; ?></strong>
        </p>        
        <?php
    } else {
        ?>
        <p>
            <b><a class="link" href="registrace.php">Elektronickou přihlášku</a></b> lze vyplnit až do <strong><?php echo $endReg; ?></strong>
        </p>
        <?php
    }
} else {
    ?>
    První proběhne vypracování testu (1 hodina matematiky, 1 hodina českého jazyka)<br>
    <i>Termín bude upřesněn těsně před začátkem registrace</i>

    Později proběhne vyhodnocení testu (1 hodina matematiky, 1 hodina českého jazyka)<br>
    <i>Termín bude upřesněn těsně před začátkem registrace</i>

    PŘIJÍMAČKY NANEČISTO se uskuteční v budově SPŠ, Purkyňova 97, <i>poplatek za účast bude stanoven těsně před začátkem registrace</i> 
    <?php
}
?>
</div>
<div class="mycontainer container-gray align-middle">
    <a href='informace.php' class='link align-middle'><img src="images/info.png" class="link-img"> Další informace</a>
</div>

</div>
<div class="col-xl-1 col-0"></div>
</div>
</article>
<?php
include_once "html/footer.php";
?>