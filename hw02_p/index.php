<?php
//načteme připojení k databázi a inicializujeme session
require_once 'inc/user.php';

//vložíme do stránek hlavičku
include 'inc/header.php';

if (!empty($_REQUEST['adminStatus'])) {
    alert('Nejste admin, omluvám se');
}
if (!empty($_REQUEST['loginStatus'])) {
    alert('Nejste přihlašen, omluvám se');
}
if (!empty($_REQUEST['failStatus'])) {
    alert('Hm, něco se nepovedlo');
}
if (!empty($_REQUEST['operationStatus'])) {
    alert('Operace proběhla uspešné');
}

function alert($msg)
{
    echo "<script type='text/javascript'>alert('$msg');</script>";
}

if (!empty($_GET['category'])) {
    #region výběr příspěvků z konkrétní kategorie
    $query = $db->prepare('SELECT
                           posts.*, users.name AS user_name, users.email, categories.name AS category_name
                           FROM posts JOIN users USING (user_id) JOIN categories USING (category_id) WHERE posts.category_id=:category ORDER BY updated DESC;');
    $query->execute([
        ':category' => $_GET['category']
    ]);
    #endregion výběr příspěvků z konkrétní kategorie
} else {
    #region výběr příspěvků bez ohledu na kategorii
    $query = $db->prepare('SELECT
                           posts.*, users.name AS user_name, users.email, categories.name AS category_name
                           FROM posts JOIN users USING (user_id) JOIN categories USING (category_id) ORDER BY updated DESC;');
    $query->execute();
    #region výběr příspěvků bez ohledu na kategorii
}

#region formulář s výběrem kategorií

echo '<form method="get" id="categoryFilterForm">
          <label for="category">Kategorie:</label>
          <select name="category" id="category" onchange="document.getElementById(\'categoryFilterForm\').submit();">
            <option value="">--nerozhoduje--</option>';

$categories = $db->query('SELECT * FROM categories ORDER BY name;')->fetchAll(PDO::FETCH_ASSOC);

if (!empty($categories)) {
    foreach ($categories as $category) {
        echo '<option value="' . $category['category_id'] . '"';//u category_id nemusí být ošetření speciálních znaků, protože jde o číslo
        if ($category['category_id'] == @$_GET['category']) {
            echo ' selected="selected" ';
        }
        echo '>' . htmlspecialchars($category['name']) . '</option>';
    }
}

echo '  </select>
          <input type="submit" value="OK" class="d-none" />
        </form>';

#region formulář s výběrem kategorií

$posts = $query->fetchAll(PDO::FETCH_ASSOC);
if (!empty($posts)) {
    #region výpis příspěvků
    echo '<div class="row">';
    foreach ($posts as $post) {
        echo '<article class="col-12 col-md-6 col-lg-4 col-xxl-3 border border-dark mx-1 my-1 px-2 py-1">';
        echo '  <div><span class="badge badge-secondary">' . htmlspecialchars($post['category_name']) . '</span></div>';
        echo '  <div>' . nl2br(htmlspecialchars($post['text'])) . '</div>';
        echo '  <div class="small text-muted mt-1">';
        echo htmlspecialchars($post['user_name']);
        echo ' ';
        echo date('d.m.Y H:i:s', strtotime($post['updated']));//datum získané z databáze převedeme na timestamp a ten pak do českého tvaru

        #Kontrolujeme, zda nějaký uživatel přihlášen
        if (!empty($_SESSION['user_id'])) {
            #pokud ano -> kontrolujeme je nebo neni adminem
            $adminQuery = $db->prepare('SELECT * FROM users WHERE user_id=:user_id and isadmin=1 LIMIT 1;');
            $adminQuery->execute([
                ':user_id' => $_SESSION['user_id']
            ]);
            #pokud je adminem -> muže upravovat příspěvky všech uživatelů
            if ($adminQuery->rowCount() > 0) {
                $isAdmin = true;
                echo ' - <a href="edit.php?id=' . $post['post_id'] . '" class="text-danger">upravit</a>';
                echo '                
                 <button type="button" class="close" aria-label="Close">
                     <a href="./edit.php?fromAdmin=1&removeId='.$post['post_id'].'">
                        <span aria-hidden="true">&times;</span>
                     </a>
                </button>';
            } #pokud neni admin kontrolujeme je autorem prispevku nebo ne
            else {
                $isAdmin = false;
                $userId = $post['user_id'];
                if ($_SESSION['user_id'] === $userId) {
                    echo ' - <a href="edit.php?id=' . $post['post_id'] . '" class="text-danger">upravit</a>';
                    echo '                
                 <button type="button" class="close" aria-label="Close">
                     <a href="./edit.php?fromAdmin=0&removeId='.$post['post_id'].'">
                        <span aria-hidden="true">&times;</span>
                     </a>
                </button>';
                }
            }
        }
        echo '  </div>';
        echo '</article>';
    }
    echo '</div>';
    #endregion výpis příspěvků
} else {
    echo '<div class="alert alert-info">Nebyly nalezeny žádné příspěvky.</div>';
    if (!empty($_SESSION['user_id'])) {
        $adminQuery = $db->prepare('SELECT * FROM users WHERE user_id=:user_id and isadmin=1 LIMIT 1;');
        $adminQuery->execute([
            ':user_id' => $_SESSION['user_id']
        ]);
        if ($adminQuery->rowCount() > 0) {
            $isAdmin = true;
        } else {
            $isAdmin = false;
        }
    }
}

if (!empty($_SESSION['user_id'])) {
    echo '<div class="row my-3">
            <a href="edit.php?category=' . @$_GET['category'] . '" class="btn btn-primary">Přidat příspěvek</a>
          </div>';
    if ($isAdmin) {
        echo '<div class="row my-3">
            <a href="category.php" class="btn btn-dark">Kategorii</a>
          </div>';
    }
}


//vložíme do stránek patičku
include 'inc/footer.php';