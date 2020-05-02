<?php
require_once "funkce.php";

$title="Informace - Příjímačky nanečisto";
include_once "html/head.php";
include_once "html/header.php";
include_once "html/nav.php";

isset($_GET['lenght']) ? $lenght = $_GET['lenght'] : $lenght = 5;
$konec = false; 
if(($db->table('info')->select()->count()) <= $lenght) $konec = true;
if(($db->table('info')->select()->count()) == 0) $lenght = 0;

if($konec) {
    $info = array_reverse($db->table('info')->select()->results());
} else {
    $info = $db->table('info')->select()->last($lenght);
}
if($lenght  < 1) {
    $info = [];
}

?>
<article class="mx-0 px-0 container-fluid">
<div class="row px-5 mr-0">
<div class="col-xl-1 col-0"></div>
<div class="col-xl-10 col-12">
<div class="mycontainer container-white">
    <p class="heading">Příspěvky</p>
</div>
    
<?php
foreach($info as $row) {
    ?>
    <div class="mycontainer container-white">
        <table style="width: 100%; height:100%;">
            <tr style="width: 100%; height:100%;">
                <td style="width: 100%; height:100%;">
                    <p style="text-align: right;"><small><i><?php echo date("j. n. Y", strtotime($row["date"])); ?></i></small></p>
                </td>
            </tr>
            <tr style="width: 100%; height:100%;">
                <td style="width: 100%; height:100%;"><?php echo $row["article"]; ?></td>
            </tr>
        </table>
    </div>
    <?php
}

if(!$konec) {
    ?>
    <div class="mycontainer container-white">
        &nbsp;
        <a class="btn btn-primary float-right mr-5" href='informace.php?lenght=<?php echo($lenght+5);?>'>zobrazit více</a>
    </div>
    
    <?php
}

?>
</div>
<div class="col-xl-1 col-0"></div>
</div>
</article>

<?php
include_once "html/footer.php";
?>