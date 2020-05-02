<?php 
require_once "funkce.php";

$title="Kontakt - Příjímačky nanečisto";
$extScript="https://api.mapy.cz/loader.js";
$script = 'Loader.load()';
include_once "html/head.php";
include_once "html/header.php";
include_once "html/nav.php"; 
?>
<article class="mx-0 px-0 container-fluid">
<div class="row px-5 mr-0">
<div class="col-xl-1 col-0"></div>
<div class="col-xl-10 col-12">
    <div class="mycontainer container-white">
        <table>
            <tr>
                <td>
                    <b>Kontaktní osoba:</b>
                </td>
                <td class="pl-3">
                    Alena Klobásová
                </td>
            </tr>
            <tr>
                <td>
                    <b>Telefon:</b>
                </td>
                <td class="pl-3">
                    541 649 193
                </td>
            </tr>
            <tr>
                <td>
                    <b>E-mail:</b>
                </td>
                <td class="pl-3">
                    alena.klobasova@sspbrno.cz
                </td>
            </tr>
        </table>
    </div>
    <div class="mycontainer container-white">
        <table>
            <th colspan="2">
                <p class="heading">Kontaktní informace pro obecnou komunikaci se školou.</p>
            </th>
            <tr>
            </tr>
            <tr>
            </tr>
            <tr>
                <td>
                    <b>Název školy:</b>
                </td>
                <td>
                    Střední průmyslová škola Brno, Purkyňova,
                    příspěvková organizace
                </td>
            </tr>
            <tr>
            </tr>
            <tr>
                <td>
                    <b>Adresa:</b>
                </td>
                <td>
                    Purkyňova 97
                    Brno, 612 00
                </td>
            </tr>
            <tr>
            </tr>
            <tr>
                <td>
                    <b>Webové stránky:</b>
                </td>
                <td>
                    <a href="https://www.sspbrno.cz/">https://www.sspbrno.cz/</a>
                </td>
            </tr>
            <tr>
            </tr>
            <tr>
                <td>
                    <b>E-mail:</b>
                </td>
                <td>
                    posta(zavinac)sspbrno.cz
                </td>
            </tr>
            <tr>
            </tr>
            <tr>
                <td>
                    <b>Telefon:</b>
                </td>
                <td>
                    provolba - 541 649 klapka<br>
                    spojovatelka - 541 649 111
                </td>
            </tr>
        </table>
    </div>
    <div class="mycontainer container-gray">
        <div id="APImapy" class="ignore-css map"></div>
    </div>
</article>
<script type="text/javascript">
    var stred = SMap.Coords.fromWGS84(16.5803185, 49.2253724);
    var mapa = new SMap(JAK.gel("APImapy"), stred, 18);
    mapa.addDefaultLayer(SMap.DEF_BASE).enable();
    mapa.addDefaultControls();    	      
</script>
<?php
include_once "html/footer.php";
?>