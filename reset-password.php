<?php

$token = $_GET["token"];
$token_hash = hash("sha256", $token);

$mysqli = require __DIR__ . "/database.php";

$sql = "SELECT * FROM user WHERE reset_token_hash = ?";
$stmt = $mysqli->prepare($sql);

$stmt->bind_param("s", $token_hash);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if($user === null){
    die("token not found");
}
if (strtotime($user["reset_token_expires_at"]) <= time()){
    die("token has expired");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href = "css/passwordResetStyle.css">
    <title>User Registration Form</title>
</head>
<body>
    <div class="login-container">
        <h1 class="form-heading">Reset Password</h1>
        <form method="post" action="process-reset-password.php">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <label for="password">New password</label>
            <input type="password" id="password" name="password">

            <label for="password_confirmation">Repeat password</label>
            <input type="password" id="password_confirmation" name="password_confirmation">

            <button>Send</button>
        </form>
    </div>
</body>
</html>