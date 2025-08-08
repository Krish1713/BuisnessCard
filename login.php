<?php
$is_invalid = false;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $mysqli = require __DIR__ . "/database.php";
    $sql = sprintf("SELECT * FROM user WHERE email = '%s'", $mysqli->real_escape_string($_POST["email"]));

    $result = $mysqli->query($sql);
    $user = $result->fetch_assoc();

    if ($user) {
        if (password_verify($_POST["password"], $user["password_hash"])) {
            session_start();

            session_regenerate_id();

            $_SESSION["user_id"] = $user["id"];

            header("Location: home.php");
            exit;
        }
    }
    $is_invalid = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/loginStyle.css">
</head>

<body>
    <?php include "navbar.php"; ?>
    <div class="login-form">
        <h1 class="form-heading">Login</h1>
        <?php if ($is_invalid): ?>
            <em>Invalid Login</em>
        <?php endif; ?>
        <form method="post">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" value="<?= htmlspecialchars($_POST["email"] ?? "") ?>"><br>

            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password"><br>

            <a href="forgot-password.php" class="forgot-password">Forgot password?</a><br>
            <button>Log in</button><br>
        </form>
        <div class="separator"></div>
        <div class="signup-link">
            No account? <a href="signup.php">Create one</a>
        </div>
    </div>
</body>

</html>
