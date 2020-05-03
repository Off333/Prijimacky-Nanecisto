<?php
require_once "funkce.php";

$errMsg = '';

//kontrola prihlaseni uzivatele
$user = $db->table('spravci')->where('User', '=', $_SESSION['user'])
->where('User', '=', $_COOKIE['user'])->select()->last();
if($user['User'] != $_SESSION['user'] && $user['User'] != $_COOKIE['user']){

    //overeni odkazu s pomoci formulare s metodou post
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $Password = $db->table('spravci')->
        where('User', '=', filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW))->
        select()->first()['Password'];

        //autentizace = verifikace = overeni uzivatele
        if (!empty($_POST['passw']) && hash_equals($Password, md5($_POST['passw']))) {

            //povoleni pristupu uzivatele pomoci promenne v session
            $spravceJmeno = $db->table('spravci')
            ->where('User', '=', $_POST['user'])->select()->first()['User'];
            setcookie('user', $spravceJmeno, (time() + 24 * 60 * 60));
            $_SESSION['user'] = $spravceJmeno;
            $_SESSION['IP'] = get_ip_address();

            $_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

            //presmerovani na rozcestnik admin.php
            header("Location: admin.php");
            exit();

        //selhani autentizace = verifikace = overeni uzivatele
        } else {
            $errMsg = "<div class='alert alert-danger'>Nesprávné uživatelské jméno nebo heslo</div>";
        }
    } 

    $title="Přihlášení - Příjímačky nanečisto";
    include_once "html/head.php";
    include_once "html/header.php";
    include_once "html/nav.php";

    ?>
    <article>
        <div class="container mt-3 py-auto">
        <div class="alert alert-warning">
            Toto přihlášení slouží POUZE pro správu těchto stránek
        </div>      
        <?php echo $errMsg; ?>
        </div>
        <div class="container bg-white mt-3 py-auto pb-1">
            <form method="post" action="prihlaseni.php">
                <fieldset>
                    <legend>Přihlášení</legend>
                    <table>
                        <tr>
                            <td><label>Uživatelské jméno:<label></td>
                            <td><input type="text" name="user" placeholder="Uživatelské jméno" autocomplete="username"></td>
                        </tr>  
                        <tr>
                            <td><label>Heslo:<label></td>  
                            <td><input type="password" name="passw" placeholder="Hesl0" autocomplete="current-password"></td>
                        </tr> 
                    </table>
                </fieldset>
                <div class="mx-5 pb-3">
                &nbsp;
                    <input class="btn btn-primary float-right mr-5" type="submit" name="submit" value="Odeslat">
                </div>
            </form>
        </div>
    </article>
    
    <?php
    include_once "html/footer.php";

//uzivatel jiz prihlasen  
} else {
    //presmerovani na rozcestnik admin.php
    header("Location: admin.php");
}
?>