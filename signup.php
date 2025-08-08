<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/signupStyle.css">
    <style>
        .error-text {
            color: red;
        }
    </style>
</head>
<body>
    <?php
        include("database.php");
        $username = $email = $password = $confirm_password = "";
        $username_err = $email_err = $password_err = $confirm_password_err = "";

        // Function to generate a unique 20-character alphanumeric code
        function generateUniqueCode($length = 20) {
            return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if(empty(trim($_POST["username"]))){
                $username_err = "Please enter a username.";
            } else{
                $username = trim($_POST["username"]);
            }

            // Validate email
            if(empty(trim($_POST["email"]))){
                $email_err = "Please enter an email.";
            } else{
                $email = trim($_POST["email"]);
                // Check if email already exists in the database
                $sql = "SELECT id FROM user WHERE email = ?";
                if($stmt = $mysqli->prepare($sql)){
                    $stmt->bind_param("s", $param_email);
                    $param_email = $email;
                    if($stmt->execute()){
                        $stmt->store_result();
                        if($stmt->num_rows > 0){
                            $email_err = "This email is already in use.";
                        }
                    } else{
                        echo "Something went wrong. Please try again later.";
                    }
                    $stmt->close();
                }
            }
            
            // Validate password
            if(empty(trim($_POST["password"]))){
                $password_err = "Please enter a password.";     
            } elseif(strlen(trim($_POST["password"])) < 8){
                $password_err = "Password must have at least 8 characters.";
            } else{
                $password = trim($_POST["password"]);
            }
            
            // Validate confirm password
            if(empty(trim($_POST["confirm-password"]))){
                $confirm_password_err = "Please confirm password.";     
            } else{
                $confirm_password = trim($_POST["confirm-password"]);
                if(empty($password_err) && ($password != $confirm_password)){
                    $confirm_password_err = "Password did not match.";
                }
            }
            
            // Check input errors before inserting in database
            if(empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
                // Generate unique link code
                $link_code = generateUniqueCode();

                $sql = "INSERT INTO user (username, email, password_hash, link) VALUES (?, ?, ?, ?)";
                 
                if($stmt = $mysqli->prepare($sql)){
                    $stmt->bind_param("ssss", $param_username, $param_email, $param_password, $param_link);
                    $param_username = $username;
                    $param_email = $email;
                    $param_password = password_hash($password, PASSWORD_DEFAULT);
                    $param_link = $link_code;
                    
                    if($stmt->execute()){
                        header("location: home.php");
                    } else{
                        echo "Something went wrong. Please try again later.";
                    }
                    $stmt->close();
                }
            }
            $mysqli->close();
        }
    ?>
    <?php include "navbar.php"; ?>
    <div id="header">
        <div class="signup-form">
            <h1 class="form-heading">Sign-Up Form</h1><br>
            <form action="signup.php" method="post" id="signup" novalidate>
                <div>
                    <label for="username">Name:</label><br>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>"><br>
                    <span class="error-text"><?php echo $username_err; ?></span>
                </div>
                <div>
                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>"><br>
                    <span class="error-text"><?php echo $email_err; ?></span>
                </div>
                <div>
                    <label for="password">Password:</label><br>
                    <input type="password" id="password" name="password"><br>
                    <span class="error-text"><?php echo $password_err; ?></span>
                </div>
                <div>
                    <label for="confirm-password">Repeat Password:</label><br>
                    <input type="password" id="confirm-password" name="confirm-password"><br>
                    <span class="error-text"><?php echo $confirm_password_err; ?></span>
                </div>
                <button class="btn" type="submit">Sign Up</button>    
            </form>
        </div>
    </div>
</body>
</html>
