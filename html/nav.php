<?php
//zacatek sessionu pokud jiz nebezi (projistotu)
if (session_status() == PHP_SESSION_NONE) {
    session_set_cookie_params(time() + 24 * 60 * 60, $_SERVER['REQUEST_URI']);
    session_start();
}
?>

<div class="container-fluid mx-0 px-0 my-auto static-top">
    <nav class="navbar navbar-expand-lg navbar-blue px-0">
        <div class="px-5 row w-100">
            <div class="col-xl-1 col-0">
                <button class="navbar-toggler my-3 px-2" type="button" data-toggle="collapse" data-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>
            <div class="col-10">
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav ml-lg-3 float-left mr-lg-auto w-100">
                        <li class="nav-item nav-first">
                        <a id="index" class="nav-link px-4 py-4" href="./">Hlavní&nbsp;stránka</a>
                        </li>
                        <li class="nav-item">
                        <a id="registrace" class="nav-link px-4 py-4" href="registrace.php">Elektronická&nbsp;přihláška</a>
                        </li>
                        <li class="nav-item">
                        <a id="informace" class="nav-link px-4 py-4" href="informace.php">Aktuální&nbsp;informace</a>
                        </li>
                        <li class="nav-item">
                        <a id="kontakt" class="nav-link px-4 py-4" href="kontakt.php">Kontakt</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="col-xl-1 col-0"></div>
        </div>    
    </nav>
    <?php 
    /**
     * zobrazení administrátorského panelu
     * nedovolí přístup na výpis pokud není vytvořena akce
     */
    if(!empty($_SESSION['user']) && !empty($_COOKIE['user'])) {         
        if(!isset($eventInfo['StartRegDate']) || empty($eventInfo['StartRegDate'])) {
            if($Occupation > 1) {
                $vypisHref = "javascript:alert('Ještě není vytvořena akce, ale jsou již zaregistrovaní účastsníci.\\nZ toho důvodu může docházet k chybám a nepředvídatelnému chování ve výpise!'); document.location.href='vypis.php'";

            } else {
            $vypisHref = "javascript:alert('Ještě není vytvořena akce!')";
            }

        } else {
            $vypisHref = "vypis.php";
            
        }
        ?>
        <nav class="navbar navbar-expand-lg navbar-blue static-top px-0">
            <div class="px-5 row w-100">
                <div class="col-xl-1 col-0">
                    <button class="navbar-toggler my-3 px-2" type="button" data-toggle="collapse" data-target="#navbarResponsiveAdmin" aria-controls="navbarResponsiveAdmin" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                </div>
                <div class="col-10">
                    <div class="collapse navbar-collapse" id="navbarResponsiveAdmin">
                        <ul class="navbar-nav ml-lg-3 float-left mr-lg-auto w-100">
                            <li class="nav-item">
                            <a id="admin" class="nav-link px-4 py-4" href="admin.php">Hlavní&nbsp;panel</a>
                            </li>
                            <li class="nav-item">
                            <a id="vypis" class="nav-link px-4 py-4" href="<?php echo $vypisHref; ?>">Výpis</a>
                            </li>
                            <li class="nav-item">
                            <a id="noveInfo" class="nav-link px-4 py-4" href="noveInfo.php">Přidat&nbsp;informace</a>
                            </li>
                            <li class="nav-item">
                            <a id="zmenaMailu" class="nav-link px-4 py-4" href="zmenaMailu.php">Změnit&nbsp;Emaily</a>
                            </li>
                            <li class="nav-item">
                            <a id="odhlaseni" class="nav-link px-4 py-4" href="odhlaseni.php">Odhlásit&nbsp;se</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="col-xl-1 col-0"></div>
            </div>  
        </nav>          
        <?php 
    } else {
        ?>
        <div class="row mx-0">
            <div class="col-11"></div>
            <div class="col-1">
                <a id="prihlaseni" class="nav-link px-4 py-4" href="prihlaseni.php">admin</a>
            </div>
        </div>
        <?php 
    }
    ?>
    </div>
</div>
<div id="page-content">