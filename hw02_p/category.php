<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';

//vložíme do stránek hlavičku
include 'inc/header.php';

#kontrolujeme pristup k teto strance
if (!empty($_SESSION['user_id'])){
    $adminQuery=$db->prepare('SELECT * FROM users WHERE user_id=:user_id and isadmin=1 LIMIT 1;');
    $adminQuery->execute([
        ':user_id'=>$_SESSION['user_id']
    ]);if($adminQuery->rowCount()>0){
        if(!empty($_REQUEST['removeId'])){
            $categoryRemoveQuery=$db->prepare('DELETE FROM `categories` WHERE category_id=:id LIMIT 1;');
            $categoryRemoveQuery->execute([':id'=>$_REQUEST['removeId']]);
        }
    }else{
        header('Location:index.php?adminStatus=no');
    }
}else{
    header('Location:index.php?loginStatus=no');
}
#konec

?>


<form metod="post">

    <div class="d-flex bd-highlight" id="remove">
        <?php
        $categoryQuery = $db->prepare('SELECT * FROM categories ORDER BY name;');
        $categoryQuery->execute();
        $categories = $categoryQuery->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($categories)) {
            foreach ($categories as $category) {
                echo '
            <div class="p-2 flex-fill bd-highlight border border-primary"> '.htmlspecialchars($category['name']).'<a style="margin-left: 50%; font-size: 12px" href="./categoryedit.php?updateId='.$category['category_id'].'">-Upravit</a>
                <button type="button" class="close" aria-label="Close">
                     <a href="./category.php?removeId='.$category['category_id']. '">
                        <span aria-hidden="true">&times;</span>
                     </a>
                </button>
            </div>';
            }
        }
        ?>
    </div>

    <div style="margin-top: 25px">
        <a href="./categoryedit.php" class="btn btn-primary">Přidat kategorii</a>

        <a href="index.php" class="btn btn-light">Zrušit</a>
    </div>
</form>


<?php
//vložíme do stránek patičku
include 'inc/footer.php';
?>

