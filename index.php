

<?php
/* Сайт с отзывами на игры, фильмы и книги

Что в нем должно быть:
    1. связь с БД для того что бы сохранять отзывы
    2. Собственно говоря место где можно написать отзыв
    3. Место где можно просмотреть отзывы

*/
session_start();

$serverName = "DAIRY\SQLEXPRESS";
$database = "temp";
$user = "";
$password = "";

$connectionInfo = [
    "Database" => $database,
    "UID" => $user,
    "PWD" => $password,
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionInfo);

if ($conn === false) {
    die(print_r(sqlsrv_errors(), true));
}

$isLoggedIn = isset($_SESSION["isLoggedIn"]) ? $_SESSION["isLoggedIn"] : false;
$username = isset($_SESSION["username"]) ? $_SESSION["username"] : "Гость";
$message = '';

$products = [];
$productsSql = "SELECT name, category, price, description, image_url FROM products ORDER BY category, name";
$productsStmt = sqlsrv_query($conn, $productsSql);

if ($productsStmt !== false) {
    while ($product = sqlsrv_fetch_array($productsStmt, SQLSRV_FETCH_ASSOC)) {
        $products[] = $product;
    }
}

if ($_POST) {
    if (isset($_POST['logout'])){
        $isLoggedIn = false;
        $username = 'Гость';
    } 
    elseif (isset($_POST['register'])) {
        $login = trim($_POST['login']);
        $password = trim($_POST['password']);       
        if (empty($login) || empty($password)) {
            $message = 'Заполните все поля!';
        } else {
            $sql = "SELECT id FROM users WHERE login = ?";
            $params = array($login);
            $stmt = sqlsrv_query($conn, $sql, $params);   
            if ($stmt == false) {
                $message = 'Ошибка запроса: ' . print_r(sqlsrv_errors(), true);
            } elseif (sqlsrv_has_rows($stmt)) {
                $message = 'Пользователь уже существует!';
            } else {
                $sql = "INSERT INTO users (login, password) VALUES (?, ?)";
                $params = array($login, $password);
                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt == false) {
                    $message = 'Ошибка регистрации: ' . print_r(sqlsrv_errors(), true);
                } else {
                    $message = 'Вы зарегистрировались!';
                }
            }
        }
    } else {
        $login = trim($_POST['login']);
        $password = trim($_POST['password']);

        $sql = "SELECT * FROM users WHERE login = ? AND password = ?";
        $params = [$login, $password];
        $stmt = sqlsrv_query($conn, $sql, $params);

        if ($stmt == false) {
                $message = "Ошибка авторизации";

        } else {
            $user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

            if ($user) {
                $isLoggedIn = true;
                $username = $login;
                $message = 'Добро пожаловать, ' . $login . '!';

                $_SESSION['isLoggedIn'] = True;
                $_SESSION['username'] = $login;
            }

            else {
                $message = "Неверный логин или пароль";
            }
        }
    }   
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <span class="title">Мой сайт</span>
        <span class="hello"><?php echo $username; ?></span>
    </header>
    <section class="section-1">
        <?php if ($isLoggedIn): ?>
            <form method="POST">
                <input type="submit" name="logout" value="Выйти" class="logout_btn"></input>
            </form>
            <div class="catalog">
                <h2>Каталог товаров</h2>
                <div class="products">
                    <?php foreach ($products as $product): ?>
                        <div class="product">
                        <div class="product-image">
                            <img src="<?php echo ($product['image_url']); ?>" >
                            </div>
                            <h3><?php echo ($product['name']); ?></h3>
                            <p class="category">Категория: <?php echo ($product['category']);?></p>
                            <p class="price">Цена: <?php echo ($product['price']);?></p>
                            <p class="description"><?php echo ($product['description']);?></p>
                        </div>
                        <?php endforeach; ?>
                </div>
            </div>

        <?php else: ?>
            <form method="POST" class="main_form">
                <input type="text" placeholder="Логин" name="login" required>
                <input type="text" placeholder="Пароль" name="password" required>
                <input type="submit" value="Войти" class="auth_btn"></input>
                <input type="submit" name="register" class="reg_btn" value="Регистрация"></input>
                <span><?php echo $message; ?></span>
            </form>
        <?php endif; ?>
    </section>  
</body>
</html>