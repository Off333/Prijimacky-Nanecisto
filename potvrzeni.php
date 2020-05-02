<?php
/**
 * kontrola přítomnosti emailu v datech
 */
if(!isset($_GET['email'])) {
    header("Location:registrace.php?chyba=nezadan%20Email");
    exit();
}

require_once "funkce.php";

/**
 * výběr posledního přihlášeného s daným emailem
 */
$Email = $db->table('prihlaseni')->where('Email', '=', $_GET['email'])->select()->last();

/**
 * odeslání emailu
 */
$send = emailMessage('register', $db, $Email);

/**
 * přesměrování na registraci se stavem odeslání
 */
if($send == True) {
    header("Location:./?alert=regSuccess&email=".$_GET['email']);
    exit();
} else {
    header("Location:./?alert=regFail&email=".$_GET['email']);
    exit();
}
?>