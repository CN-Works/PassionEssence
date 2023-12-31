<?php

$categories = $result["data"]['categories'];
use App\DAO;

$card_count = 1;
    
?>

<div class="allcategory-main">
    <div class="general-toppage-titletexts">
        <h2 class="general-toppage-title">Catégories</h2>

        <p class="general-toppage-subtitle">
            Toutes les différentes catégories de topics disponible sur forum.
            Choissisez-en une pour découvrir le thème que vous voulez !
            <?php
            // Ici la fonctionnalité de création de catégorie est autorisé uniquement au admins
            if (isset($_SESSION["user"]) && $_SESSION["user"]->getRole() == "admin") {
                ?>
                <br>
                <br>
                <a class="general-toppage-redactionlink unselectable" href="index.php?ctrl=forum&action=CreateCategoryForm">Nouvelle catégorie</a>
                <?php
            }
            ?>
        </p>
    </div>
    <div class="allcategory-allcards">

        <?php

        if (isset($categories)) {
            foreach($categories as $category) {
                ?>
                <a class="allcategory-card" href="index.php?ctrl=forum&action=TopicsByCategory&id=<?= $category->getId()?>">
                    <?php
                        $banner = $category->getImage();
    
                        if (isset($banner)) {
                            echo "<figure class='allcategory-card-bannerfig'><img class='allcategory-card-banner unselectable' src='$banner' alt='Bannière de la catégorie ".$category->getLabel()."'></figure>";
                        }
                    ?>
                    <div class="allcategory-card-textside">
                        <h2 class="allcategory-card-title"><?= $category->getLabel() ?></h2>
                        <p class="allcategory-card-subtitle"><?= $category->getDescription() ?></p>
                    </div>
                </a>
                <?php
            }
        }

        ?>
    </div>
</div>