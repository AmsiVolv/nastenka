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
    ]);if($adminQuery->rowCount()<0){
        header('Location:index.php?adminStatus=no');
    }
}else{
    header('Location:index.php?loginStatus=no');
}
#konec

$categoryName ='';
$categoryId = '';
$postText = '';
if(!empty($_REQUEST['updateId'])){
        $categoryQuery=$db->prepare('SELECT * FROM categories WHERE category_id=:id LIMIT 1;');
        $categoryQuery->execute([':id' => $_REQUEST['updateId']]);
    if ($category=$categoryQuery->fetch(PDO::FETCH_ASSOC)){
        $categoryName=$category['name'];
        $categoryId=$category['category_id'];
    }else{
        exit('Příspěvek neexistuje.');//tady by mohl být i lepší výpis chyby :)
    }
}


$errors=[];

if(!empty($_POST)){

    #kontrolovani
    $postText=trim(@$_POST['text']);
    if (empty($postText)){
        $errors['text']='Musíte zadat text příspěvku.';
    }
    #konec kontrolovani

    if(empty($errors)){
        if($categoryId){
            #uprava dat
            $categoryQuery=$db->prepare('UPDATE categories SET name=:text WHERE category_id=:id LIMIT 1;');
            $categoryQuery->execute([
                ':text' => $postText,
                ':id' => $categoryId
            ]);
            #konec uprava
        }else{
            #vkladani
            $saveQuery=$db->prepare('INSERT INTO categories (name) VALUES (:text);');
            $saveQuery->execute([
                ':text'=>$postText
            ]);
            #konec vkladani
        }

        header('Location: category.php?status=complete');
        exit();

    }

}

?>



<form method="post">
    <div class="form-group">
        <label for="text"><?php if(!empty($_REQUEST['updateId'])){echo 'Upravit -<span class="bg-info">'.$categoryName.'</span>';}else{echo 'Přidat kategorii:';}?></label>
        <textarea name="text" id="text" required class="form-control <?php echo (!empty($errors['text'])?'is-invalid':''); ?>" placeholder="<?php if(!empty($_REQUEST['updateId'])){echo $categoryName;}else{echo 'Název';}?>"><?php echo htmlspecialchars($postText)?></textarea>
        <?php
        if (!empty($errors['text'])){
            echo '<div class="invalid-feedback">'.$errors['text'].'</div>';
        }
        ?>
    </div>
    <button type="submit" class="btn btn-primary">Uložit...</button>
    <a href="./category.php" class="btn btn-light">Zrušit</a>
</form>

<?php
//vložíme do stránek patičku
include 'inc/footer.php';
?>
