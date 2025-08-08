<?php
    if (isset($_SESSION["user_id"])){
        $mysqli = require __DIR__ . "/database.php";
        $sql = "SELECT * FROM user WHERE id = {$_SESSION["user_id"]}";
        $result = $mysqli->query($sql);
        $user = $result->fetch_assoc();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="css/navbar.css">
</head>
<body>
<nav class="nav clearfix">
    <ul class="left">
        <li><a href="home.php"  class="navbar-text">Home</a></li>
    </ul>
    <ul class="right">
        <?php if (isset($user)): ?>
            <li><a href="logout.php"  class="navbar-text">Log Out</a></li>
            <li><a href="signup.php"  class="navbar-text">Sign Up</a></li>
        <?php else: ?>
            <li><a href="login.php"  class="navbar-text">Login</a></li>
            <li><a href="signup.php"  class="navbar-text">Sign Up</a></li>
        <?php endif; ?>
    </ul>
</nav>
</body>
</html>
