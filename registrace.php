<?php
require_once "funkce.php";

if(!isset($eventInfo['StartRegDate'])) {
    header("Location:./?alert=noEvent");
    exit();

} elseif(date("Y-m-d") < $eventInfo['StartRegDate']) {
    header("Location:./?alert=regTooSoon");
    exit();

} elseif(date("Y-m-d") > $eventInfo['EndRegDate']) {
    header("Location:./?alert=regTooLate");
    exit();

} elseif(($eventInfo['FreeSpace'] - $Occupation) < 1) {
    header("Location:./?alert=noSpaceAvailable");
    exit();

} else {

    $title="Registrace - Příjímačky nanečisto";
    include_once "html/head.php";
    include_once "html/header.php";
    include_once "html/nav.php";

    $chyba='';
    if(isset($_GET['chyba'])) {
        $chyba = "
        <div class='container mt-3 py-auto'>
        <div class='alert alert-danger'>
            <p class='text-danger h4'>Přihláška nebyla přijata!</p> Při zadávání informací nastaval chyba: ". $_GET['chyba'] ."
        </div>
        </div>";
    }

?>
<article class="mx-0 px-0 container-fluid">
<div class="row px-5 mr-0">
<div class="col-xl-1 col-0"></div>
<div class="col-xl-10 col-12">

    <div class="mycontainer container-white">
        <p class="heading">Elektronický&nbsp;formulář&nbsp;na PŘIJÍMAČKY&nbsp;NANEČISTO</p>
    </div>

    <!--<div class='mycontainer container-gray'>
        <p class="link align-middle"><img src="images/warning.png" class="link-img">  Toto přihlášení slouží k testovacím účelům! Přihlásit se lze na: <a class="alert-link" href="https://nanecisto.sspbrno.cz">nanecisto.sspbrno.cz</a></p>
    </div>-->

    <?php echo $chyba;?>

    <div class="mycontainer container-gray">
        <p class="link align-middle"><img src="images/info.png" class="link-img">  Počet volných míst: <?php echo ($eventInfo['FreeSpace'] - $Occupation); ?></p>
    </div>

    <div class="mycontainer container-white">
        <form method="post" action="zapis.php" onsubmit="/*alert('Toto přihlášení slouží k testovacím účelům! Přihláška nebude přijata');*/">
            <div class="mb-2 mt-4 ml-3">
                <p class="heading2">Údaje o zúčastněném</p>
            </div>

            <div class="form-group row ml-2">
                <label for="inputFirstName" class="col-sm-2 col-form-label form-label">Jméno&nbsp;a příjmení</label>
                <div class="col-sm-10 input-group">
                    <input id="inputFirstName" class="form-control form-input" type="text" name="FirstName" placeholder="Křestní jméno" aria-label="First name" maxlength="35" title="Povinné pole. Křestní jméno účastníka" required>
                    <div class="input-group-prepend input-group-append">
                        <span class="input-group-text form-separator">&nbsp;</span>
                    </div>
                    <input id="inputLastName" class="form-control form-input" type="text" name="LastName" placeholder="Příjmení" aria-label="Last name" maxlength="35" title="Povinné pole. Příjmení účastníka" required>
                </div>
            </div>

            <div class="form-group row ml-2">
                <label for="inputSchool" class="col-sm-2 col-form-label form-label">Základní škola</label>
                <div class="col-sm-10">
                    <input id="inputSchool" class="form-control form-input" type="text" name="School" aria-describedby="helpSchool" placeholder="Základní škola, město" maxlength="100" title="Povinné pole. Škola, kterou účastník studuje a město, ve kterém se škola nachází" required>
                    <small id="helpSchool" class="form-text text-muted">Zadejte školu a město, ve kterém se škola nachází.</small>
                </div>
            </div>

            <div class="mb-2 mt-4 ml-3">
                <p class="heading2">Kontaktní údaje zákonného zástupce</p>
            </div>

            <div class="form-group row ml-2">
                <label for="inputEmail" class="col-sm-2 col-form-label form-label">E-mail</label>
                <div class="col-sm-10">
                    <input id="inputEmail" class="form-control form-input" type="email" name="Email" placeholder="E-mail" maxlength="320" title='Povinné pole. Emailová adresa musí obsahovat znak: "@"' required>
                </div>
            </div>

            <div class="form-group row ml-2">
                    <label for="inputTel" class="col-sm-2 col-form-label form-label">Telefon</label>
                <div class="col-sm-10 input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text form-separator">+</span>
                    </div>
                    <input id="inputPrefix" class="form-control col-3 col-sm-1 col-md-3 form-input" type="number" name="Prefix" placeholder="Předčíslí" maxlength="3" value="420" title="Povinné pole. Předčíslí (CZ: 420, SK: 421)">
                    <div class="input-group-prepend input-group-append">
                        <span class="input-group-text form-separator">&nbsp;</span>
                    </div>
                    <input id="inputTel" class="form-control form-input" type="tel" name="Tel" placeholder="Telefonní číslo" pattern="[0-9]{3}\s*[0-9]{3}\s*[0-9]{3}" title='Povinné pole. Formát telefonního čísla: "111 222 333" nebo "111222333"' required>
                    
                </div>
            </div>

            <div class="form-group">
                <label class="form-control-label col-sm-2 heading2" for="textareaComment">Poznámka</label>
                <div class="cols-sm-10 ml-4">
                    <textarea id="textareaComment" class="form-control form-input" name="Comment" rows="3" placeholder="zde můžete napsat jakékoliv poznámky nebo další údaje jako například kontakt na žáka" maxlength="350"></textarea>
                </div>
            </div>

            <div class="form-group ml-2 float-right">
                <div class="g-recaptcha" data-sitekey="6LfPErwUAAAAAMbjkGyHA3bRdfCasCSwhsde394X"></div>
            </div>

            <div class="form-group form-check ml-2 w-50">
                <input id="checkGDPR" class="form-check-input form-checkbox" type="checkbox" required>
                <label class="form-check-label form-label" for="checkGDPR">Souhlasím s ukládáním a zpracováním osobních dat</label>
            </div>

            <div class="button-align">
                <input class="form-button" type="submit" name="submit" value="Odeslat">
            </div>
    </form>
    </div>
    </div>
<div class="col-xl-1 col-0"></div>
</div>
</article>
<!--google js-->
<script src="https://www.google.com/recaptcha/api.js"></script>
<?php
}
include_once "html/footer.php";
?>

