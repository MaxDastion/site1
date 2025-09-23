<?php

session_start();

$serverName = "FANATSLARKA\SQLEXPRESS";
$database = "temp";
$user = "";
$password = "";
$products = 

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