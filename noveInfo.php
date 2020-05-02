<?php 
$RA = TRUE;
require_once "funkce.php";

isset($_GET['lenght']) ? $lenght = $_GET['lenght'] : $lenght = 1;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $authorId = $db->table('spravci')->where('User', '=', $_SESSION['user'])->select()->first()['Id'];

    $db->table('info')->insert([
        'article' => $_POST['article'],
        'author' => $authorId
    ]);
    header("Location: ?lenght=".$lenght);
    exit();

} elseif($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['delete'])) {
    $db->table('info')->where('Id', $_GET['Id'])->select()->delete();
    header("Location: ?lenght=". $_GET['lenght']);
    exit();
}

//vypis navigace pro spravce
$title=$_SESSION['user']." - Hlavní nabídka";
$extScript="tinymce/tinymce.min.js";
$script="
    tinymce.init({
        selector: '#WYCIWYG',
        language: 'cs',
        height : 450
    });
";
include_once "html/head.php";
include_once "html/header.php";
include_once "html/nav.php";

?>
<article> 
<div class="container bg-white border border-primary rounded rounded-lg mt-3 py-auto pt-2">
    <form method="post" action="noveInfo.php" novalidate>
        <div class="mb-3 mt-4 ml-3">
            <p class="h5">Nová informace</p>
        </div>
        <textarea id="WYCIWYG" name="article" rows="15" cols="40" required></textarea>
        <input class="btn btn-success my-2" type="submit" name="submit" value="Přidat příspěvek">
    </form>
</div>
<?php
$konec = false; 
if(($db->table('info')->select()->count()) <= $lenght) $konec = true;
if(($db->table('info')->select()->count()) == 0) $lenght = 0;
if($konec) {
    $info = array_reverse($db->table('info')->select()->results());
} else {
    $info = $db->table('info')->select()->last($lenght);
}

foreach($info as $row) {
    ?>
    <div class="container bg-white border border-primary rounded rounded-lg mt-3 py-auto">
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
        <form method="get" action="" onSubmit="return confirm('Opravdu chcete vymazat příspěvek?')">
            <input class="btn btn-danger my-2" type="submit" name="delete" value="Smazat příspěvek">
            <input name="Id" value="<?php echo $row["Id"]; ?>" hidden>
            <input name="lenght" value="<?php echo $lenght; ?>" hidden>
        </form>
    </div>
    <?php
}

if(!$konec) {
    ?>
    <div class="container bg-white border border-primary rounded rounded-lg mt-3 py-4">
        &nbsp;
        <a class="btn btn-primary float-right mr-5" href='?lenght=<?php echo($lenght+5);?>'>zobrazit více</a>
    </div>
    
    <?php
} else {

}

?>
</article>

<?php
include_once "html/footer.php";
?>