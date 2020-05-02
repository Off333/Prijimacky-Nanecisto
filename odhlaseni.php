<?php 
require_once "funkce.php";
/**
 * přesměrování, pokud uživatel není přihlášen
 */
if (empty($_SESSION['user']) && empty($_COOKIE['user'])) { 
        
    if(isset($_GET['inactivity'])) {
        header("Location: ./?alert=logedOutInactivity");
    } else {
        header("Location: ./?alert=logedOut");
    }

/**
 * odhlášení uživatele
 */
} else {
    session_unset();
    session_destroy();
    setcookie('user', null, time() - 3600); 
    header("Location: odhlaseni.php");
    exit();  
}
?>