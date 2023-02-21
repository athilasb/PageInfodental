<?php

require_once("lib/conf.php");
require_once("usuarios/checa.php");

include "includes/header.php";
include "includes/nav.php";


?>
<header class="header">
    <div class="header__content content">

        <div class="header__inner1">
            <section class="header-title">
                <h1>Estoque</h1>
            </section>
            <section class="tab">
                <a href="pg_estoque.php" class="active">Estoque</a>
            </section>
        </div>
    </div>
</header>

<main class="main">
    <div class="main__content content">
        <section class="box" style="overflow:hidden; width:calc(100vw - 210px);">
            <div class="cal-lost">
                <div class="cal-lost-slick">
                    <div>
                        EM DESENVOLVIMENTO
                    </div>
                </div>
            </div>
        </section>

    </div>
</main>


<?php include "includes/footer.php"; ?>