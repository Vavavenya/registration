<?php
session_start();
$data = $_SESSION['data'] ?: [];
$email = $_POST['email'] ?? '';
$errorLog = [];
$isRegister = isRegister($email, $data);

if (isset($_POST['reg'])) {
    if ($isRegister) {
        $errorLog[] = 'You are already register';
    } elseif (!empty($email) && !empty($_POST['pass'])) {
        $pass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
        $data[] = ['email' => $email, 'name'=>substr(md5(microtime()),rand(0,26),5) , 'pass' => $pass];
    }
} elseif (isset($_POST['login'])) {
    if ($isRegister) {
        if (checkPassword($email, $_POST['pass'], $data)){
            $_SESSION[session_id()] = getIdByEmail($email, $data);
            setcookie('SESSION_ID', session_id(), time() + 3600);
            header('Refresh:0');
        } else {
            $errorLog[] = 'Wrong password';
        }
    } else {
        $errorLog[] = 'You are not register';
    }
} elseif (isset($_POST['exit'])) {
    setcookie('SESSION_ID', '', time() - 3600);
    header('Refresh:0');
}

$_SESSION['data'] = isset($_POST['reset']) ? [] : $data;

function getIdByEmail(string $email, array $data): int
{
    return array_search($email, array_column($data, 'email')) ?: -1;
}

function isRegister(string $email, array $data): bool
{
    return is_numeric(array_search($email, array_column($data, 'email')));
}

function checkPassword(string $email, string $enteredPassword, array $data): bool
{
    $userId = getIdByEmail($email, $data);

    if ($userId !== -1) {
        return password_verify($enteredPassword, $data[$userId]['pass']);
    }

    return false;
}

function printErrorLog(array $errorLog): void
{
    if (!empty($errorLog)) {
        foreach ($errorLog as $error) {
            echo sprintf('<div style=\'color: red;\'>%s</div>', $error);
        }
    }
}
?>

<?php var_dump($_COOKIE); ?>
<hr>

<?php if (isset($_COOKIE['SESSION_ID'])): ?>
    <?php $activeUser = $data[$_SESSION[$_COOKIE['SESSION_ID']]]; ?>
    Привет <?php echo $activeUser['name'] ?>
    <br>
    Твой email <?php echo $activeUser['email'] ?>
    <form method="post">
        <button type="submit" name="exit">Exit</button>
    </form>
<?php else: ?>
    <form method="post">
        <label for="email">Email:</label><br>
        <input type="text" id="email" name="email"><br>
        <label for="pass">Password:</label><br>
        <input type="password" id="pass" name="pass">
        <br> <br>
        <button type="submit" name="reg">Registration</button>
        <button type="submit" name="login">Login</button>
        <button type="submit" name="reset">Reset</button>
    </form>
<?php endif; ?>

<?php printErrorLog($errorLog); ?>

<hr>
<?php
foreach ($data as $user) {
    echo sprintf('%s - %s -<br>', $user['email'], $user['pass']);
}
?>