<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/forgotPasswordStyle.css">
    <title>Login</title>
</head>
<body>
    <?php
        include "navbar.php";
    ?>
    <div class="login-container">
        <form action="send-password-reset.php" method="post">
            <label for="email">Enter your email:</label>
            <input type="email" name="email" id="email">
            <button>Send</button>
        </form>
    </div>
</body>
</html>
