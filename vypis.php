<?php 
$RA = TRUE;
require_once "funkce.php";
$OccupationLogedIn = $Occupation;
$OccupationAll = $db->table('prihlaseni')->where('akce_Id', '=', $eventInfo['Id'])->select()->count();
$OccupationPaid = $db->table('prihlaseni')->where('LogedOut', '=', 'Ne')->where('Paid', '=', 'Ano')->where('akce_Id', '=', $eventInfo['Id'])->select()->count();
$FreeSpace = 'Počet volných míst: <strong>'.strval($eventInfo['FreeSpace'] - $OccupationLogedIn).'</strong>';
if(($eventInfo['FreeSpace'] - $OccupationLogedIn) < 1) {
    $FreeSpace = 'Bylo dosaženo maximální kapacity!';
}
$title = $_SESSION['user']." - Výpis";
include_once "html/head.php";
include_once "html/header.php";
include_once "html/nav.php";
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark static-top">
<div class="container-fluid mx-0">
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarAdmin" aria-controls="navbarAdmin" aria-expanded="false" aria-label="Toggle admin navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarAdmin">
        <ul class="navbar-nav ml-auto mr-lg-auto">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Schovat&nbsp;sloupce</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#"><input id="hideItem1" class="changeBtn hide" name="hide0" type=checkbox> <label for="hideItem1"> Variabliní symbol</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem2" class="changeBtn hide" name="hide1" type=checkbox> <label for="hideItem2"> Datum přihlášení</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem3" class="changeBtn hide" name="hide2" type=checkbox> <label for="hideItem3"> Čas přihlášení</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem4" class="changeBtn hide" name="hide3" type=checkbox> <label for="hideItem4"> Příjmení</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem5" class="changeBtn hide" name="hide4" type=checkbox> <label for="hideItem5"> Křestní jméno</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem6" class="changeBtn hide" name="hide5" type=checkbox> <label for="hideItem6"> E-mail</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem7" class="changeBtn hide" name="hide6" type=checkbox> <label for="hideItem7"> Telefon</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem8" class="changeBtn hide" name="hide7" type=checkbox> <label for="hideItem8"> Základní škola</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem9" class="changeBtn hide" name="hide8" type=checkbox> <label for="hideItem9"> Termín zaplacení</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem10" class="changeBtn hide" name="hide9" type=checkbox> <label for="hideItem10"> Datum zaplacení</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem11" class="changeBtn hide" name="hide10" type=checkbox> <label for="hideItem11"> Poznámky</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem12" class="changeBtn hide" name="hide11" type=checkbox> <label for="hideItem12"> IP adresa</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem13" class="changeBtn hide" name="hide12" type=checkbox> <label for="hideItem13"> Zaplaceno</label></a>
                    <a class="dropdown-item" href="#"><input id="hideItem14" class="changeBtn hide" name="hide13" type=checkbox> <label for="hideItem14"> Odhlášen</label></a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Podrobnější&nbsp;skupiny</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item"  href="#"><input id="check1" class="changeBtn" name="LogedOut" type="radio" value="Ano"> <label for="check1">Zobrazit odhlášené</label></a>
                    <a class="dropdown-item" href="#"><input id="check3" class="changeBtn" name="LogedOut" type="radio" value="Ne"> <label for="check3">Zobrazit neodhlášené</label></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#"><input id="check4" class="changeBtn" name="Paid" type="radio" value="Ano"> <label for="check4">Zobrazit zaplacené</label></a>
                    <a class="dropdown-item" href="#"><input id="check2" class="changeBtn" name="Paid" type="radio" value="Ne"> <label for="check2">Zobrazit nezaplacené</label></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item" href="#"><label for="check2">Zobrazit měsíc</label> <select id="select" class="form-control selectpicker changeSelect" name="monthSelect"> 
                        <option value=""></option>
                        <option value="01">Leden</option>
                        <option value="02">Únor</option>
                        <option value="03">Březen</option>
                        <option value="04">Duben</option>
                        <option value="05">Květen</option>
                        <option value="06">Červen</option>
                        <option value="07">Červenec</option>
                        <option value="08">Srpen</option>
                        <option value="09">Září</option>
                        <option value="10">Říjen</option>
                        <option value="11">Listopad</option>
                        <option value="12">Prosinec</option>
                    </select></a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item changeBtn changeBtn" name="noRestriction" href="#">Zobrazit všechny</a>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Přidání&nbsp;účastníka</a>
                <div class="dropdown-menu">
                    <form class="mx-1 px-4 py-3" method="post" action="zapis.php">
                        <div class="form-group">
                        <label for="inputFirstName" class="col-sm-2 col-form-label">Jméno&nbsp;a&nbsp;přijímení</label>
                            <input id="inputFirstName" class="form-control" type="text" name="FirstName" placeholder="Křestní jméno" aria-label="First name" max="35" title="Povinné pole. Křestní jméno účastníka" required>
                            <input id="inputLastName" class="form-control" type="text" name="LastName" placeholder="Příjmení" aria-label="Last name" max="35" title="Povinné pole. Příjmení účastníka" required>
                        </div>
                        <div class="form-group">
                            <label for="inputSchool" class="col-sm-2 col-form-label">Základní&nbsp;škola</label>
                            <input id="inputSchool" class="form-control" type="text" name="School" aria-describedby="helpSchool" placeholder="Základní škola, město" max="100" title="zadejte základní školu a město, ve kterém se nachází" required>
                        </div>
                        <div class="form-group">
                            <label for="inputEmail" class="col-sm-2 col-form-label">Email</label>
                            <input id="inputEmail" class="form-control" type="email" name="Email" placeholder="E-mail" max="320" title='Povinné pole. Emailová adresa musí obsahovat znak: "@"' required>
                        </div>
                        <div class="form-group">
                            <label for="inputTel" class="col-sm-2 col-form-label">Telefon</label>
                            <input id="inputTel" class="form-control" type="tel" name="Tel" placeholder="Telefonní číslo" pattern="[0-9]{3}\s[0-9]{3}\s[0-9]{3}" title='Povinné pole. Formát telefonního čísla: "111 222 333"' required>         
                        </div>
                        <div class="form-group ml-2">
                            <label class="form-control-label col-sm-2" for="textAreaComment">Poznámka</label>
                            <textarea id="textAreaComment" class="form-control" name="Comment" rows="1" placeholder="zde můžete napsat jakékoliv poznámky nebo další údaje jako například kontakt na žáka" maxlength="350"></textarea>
                        </div>
                        <div class="my-4">
                            <input class="form-control btn btn-primary" type="submit" name="submit" value="Přidat účastníka">
                        </div>
                    </form>
                </div>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">Zobrazení</a>
                <div class="dropdown-menu">
                    <a class="dropdown-item changeBtn" name="saveParam" href="#">Uložit zobrazení</a>
                    <a class="dropdown-item loadBtn" name="loadParam" href="#">Načíst zobrazení</a>
                <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger loadBtn" name="resetParam" href="#">Resetovat zobrazení</a>
                </div>
            </li>
            <li class="nav-item">
                <a id="sendReminder" class="nav-link text-warning" name="sendReminder" href="javascript:if(confirm('Opravdu chcete odeslat připomínku?')) {myAjax($('#sendReminder'));}">Odeslat oznámení o akci</a>
            </li>
            <li>
                <a id="printBtn" class="nav-link text-warning" name="print" href="#"><img src="images/printer.png">  Tisk</a>
            </li>
            <li>
                <a class="nav-link text-warning changeBtn" name="export" href="#"><img src="images/download.png">  Stáhnout</a>
            </li>
            <li>
                <a id="logOutUnpaid" class="nav-link text-danger" name="logOutUnpaid" href="javascript:if(confirm('Opravdu chcete odhlásit všechny nezaplacené,\nkterým prošela lhůta splatnosti?')) {myAjax($('#logOutUnpaid'));}" title="Odhlásí pouze přihlášky, které do této chvíle nemají zaplaceno a termín zaplacení uběhl">Odhlásit nezaplacené</a>
            </li>
        </ul>
    </div>
</div>
</nav>
<div class="row text-light bg-dark static-top mx-0">
    <p class="col-1">&nbsp;</p>
    <p class="h5 col-2">Celkový počet míst: <strong><?php echo $eventInfo['FreeSpace']; ?></strong></p>
    <p class="h5 col-2"><?php echo $FreeSpace; ?></p>
    <p class="h5 col-2">Počet přihlášených: <strong><?php echo $OccupationLogedIn; ?></strong></p>
    <p class="h5 col-2">Z toho již zaplaceno: <strong><?php echo $OccupationPaid; ?></strong></p>
    <p class="h5 col-2">Celkový počet přihlášek: <strong><?php echo $OccupationAll; ?></strong></p>
</div>
<div class="row bg-dark static-top mx-0">
    <div class="col-5">
        <div id="pagNav" class="change my-4"></div>        
    </div>
    <div class="col-2">
        <input id="searchInput" class="nav-link text-primary change my-4" placeholder="Vyhledávat ve výběru" name="search">
    </div>
    <div class="col-3">
        <button id="changeRows" class="btn btn-success float-right mx-2 my-4" data-loading=true type="submit" name="changeRows" value="Potvrdit změny" data-retry=0>Potvrdit změny</button>
        <button id="resetRows" class="btn btn-danger float-right changeBtn my-4" data-loading=true type="reset" name="resetRows" value="vrátit změny" form="vypisForm">vrátit změny</button>
    </div>
    <div class="col-2">
        <p class="row"><span class="col-2 float-right mr-0 pr-0"><input id="sendEmail" class="form-control nav-link text-primary my-4" type="checkbox" name="sendEmail" checked></span> <label class="align-middle py-3 col-10 mx-0 px-0 float-left text-white text-bold" for="sendEmail">Při změně odeslat Emaily</label></p>
    </div>
</div>

<article id="print" class="float-left w-100">
    <table class="table table-hover table-stripped mb-0">
        <thead id="sheetHead" class="thead-dark w-100">
        </thead>
        <tbody id="sheetBody">
        </tbody>
        <tfoot id="sheetFoot" data-printRemove class="thead-dark w-100">
        </tfoot>
    </table>
</article>
<div id="loading" hidden>  
    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: rgba(255, 255, 255, 0); display: block;" width="105px" height="24px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
    <circle cx="50" cy="50" fill="none" stroke="#000000" stroke-width="9" r="32" stroke-dasharray="150.79644737231007 52.26548245743669" transform="rotate(228.016 50 50)">
    <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
    </circle>
    </svg>
</div>

<?php
$endScript="JS/ajax.js";
include_once "html/footer.php";
?>