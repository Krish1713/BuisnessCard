<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration Form</title>
    <link rel="stylesheet" href="style.css"> <!-- Include your custom CSS file if needed -->
</head>
<body>
    <h1>User Registration Form</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
        <label for="card_name">Name:</label>
        <input type="text" id="card_name" name="card_name" required><br>
        <label for="card_email">Email:</label>
        <input type="email" id="card_email" name="card_email" required><br>
        <label for="card_phone">Phone:</label>
        <input type="tel" id="card_phone" name="card_phone" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" placeholder="123-456-7890"><br>
        <label for="card_linkedin">LinkedIn:</label>
        <input type="text" id="card_linkedin" name="card_linkedin"><br>
        <input type="submit" value="Generate" id="generateButton">
    </form>
</body>
</html>
<?php
include("database.php");
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $card_name = filter_input(INPUT_POST, "card_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $card_email = filter_input(INPUT_POST, "card_email", FILTER_SANITIZE_SPECIAL_CHARS);
    $card_phone = filter_input(INPUT_POST, "card_phone", FILTER_SANITIZE_SPECIAL_CHARS);
    $card_linkedin = filter_input(INPUT_POST, "card_linkedin", FILTER_SANITIZE_SPECIAL_CHARS);

    // Generate a unique code
    $unique_code = bin2hex(random_bytes(10)); // This will generate a 20-character long unique code

    $sql = "INSERT INTO user (card_name, card_email, card_phone, card_linkedin, link) VALUES ('$card_name', '$card_email', '$card_phone', '$card_linkedin', '$unique_code')";
    if ($mysqli->query($sql)) {
        echo "User data inserted successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $mysqli->error;
    }
}

$mysqli->close();
?>
