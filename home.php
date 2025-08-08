<?php
require __DIR__ . "/database.php";
ob_start();
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Handle link removal
    if (isset($_POST["remove_link_id"])) {
        $stmt = $mysqli->prepare("DELETE FROM user_links WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $_POST["remove_link_id"], $_SESSION["user_id"]);
        $stmt->execute();
        header("Location: home.php"); // Redirect to the home page
        exit;
    }

    // Handle user details update
    $card_name = filter_input(INPUT_POST, "card_name", FILTER_SANITIZE_SPECIAL_CHARS);
    $card_email = filter_input(INPUT_POST, "card_email", FILTER_SANITIZE_EMAIL);
    $card_phone = filter_input(INPUT_POST, "card_phone", FILTER_SANITIZE_SPECIAL_CHARS);
    $card_linkedin = filter_input(INPUT_POST, "card_linkedin", FILTER_SANITIZE_URL);

    if (isset($_FILES["card_picture"]) && $_FILES["card_picture"]["error"] !== UPLOAD_ERR_NO_FILE) {
        $target_dir = "uploads/";
        $target_file = $target_dir . uniqid() . "-" . basename($_FILES["card_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $check = getimagesize($_FILES["card_picture"]["tmp_name"]);

        if ($check !== false && $_FILES["card_picture"]["size"] <= 26214400 && in_array($imageFileType, ["jpg", "jpeg", "png", "gif"])) {
            if (move_uploaded_file($_FILES["card_picture"]["tmp_name"], $target_file)) {
                $stmt = $mysqli->prepare("SELECT card_picture FROM user WHERE id = ?");
                $stmt->bind_param("i", $_SESSION["user_id"]);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if ($user && !empty($user['card_picture']) && file_exists($user['card_picture'])) {
                    unlink($user['card_picture']);
                }

                $stmt = $mysqli->prepare("UPDATE user SET card_picture = ? WHERE id = ?");
                $stmt->bind_param("si", $target_file, $_SESSION["user_id"]);
                $stmt->execute();
            }
        }
    }

    // Update user details, excluding the `link`
    $stmt = $mysqli->prepare("UPDATE user SET card_name = ?, card_email = ?, card_phone = ?, card_linkedin = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $card_name, $card_email, $card_phone, $card_linkedin, $_SESSION["user_id"]);
    $stmt->execute();

    // Handle new links
    if (isset($_POST["new_links"])) {
        $stmt = $mysqli->prepare("INSERT INTO user_links (user_id, link_name, link_url) VALUES (?, ?, ?)");
        foreach ($_POST["new_links"] as $link) {
            $link_name = filter_var($link["name"], FILTER_SANITIZE_SPECIAL_CHARS);
            $link_url = filter_var($link["url"], FILTER_SANITIZE_URL);
            $stmt->bind_param("iss", $_SESSION["user_id"], $link_name, $link_url);
            $stmt->execute();
        }
    }

    // Handle existing links update
    if (isset($_POST["links"])) {
        $stmt = $mysqli->prepare("UPDATE user_links SET link_name = ?, link_url = ? WHERE id = ? AND user_id = ?");
        foreach ($_POST["links"] as $id => $link) {
            $link_name = filter_var($link["name"], FILTER_SANITIZE_SPECIAL_CHARS);
            $link_url = filter_var($link["url"], FILTER_SANITIZE_URL);
            $stmt->bind_param("ssii", $link_name, $link_url, $id, $_SESSION["user_id"]);
            $stmt->execute();
        }
    }

    header("Location: home.php"); // Redirect to the home page
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <link rel="stylesheet" href="css/homeStyle.css">
    <link rel="stylesheet" href="css/mobile.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Poppins&display=swap">
    <script src="https://kit.fontawesome.com/9f694c8a5b.js" crossorigin="anonymous"></script>
    <script src="js/homeScript.js" defer></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <?php if (isset($user)): ?>
        <div class="container">
            <div class="greeting-and-buttons">
                <h2 class="greeting">Hello, <?= htmlspecialchars($user["username"]) ?></h2>
                <?php if (isset($user["link"]) && !empty($user["link"])): ?>
                    <p>You can see a preview of your page down below:</p>
                <?php else: ?>
                    <p>Add information to the form to create your site.</p>
                <?php endif; ?>
            </div>
            <div class="box">
                <div id="editForm" style="display: none;">
                    <form action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST" enctype="multipart/form-data">
                        <label for="card_name">Name:</label>
                        <input type="text" id="card_name" name="card_name" value="<?= htmlspecialchars($user["card_name"]) ?>" required><br>
                        <label for="card_email">Email:</label>
                        <input type="email" id="card_email" name="card_email" value="<?= htmlspecialchars($user["card_email"]) ?>" required><br>
                        <label for="card_phone">Phone:</label>
                        <input type="tel" id="card_phone" name="card_phone" value="<?= htmlspecialchars($user["card_phone"]) ?>"><br>
                        <label for="card_linkedin">LinkedIn:</label>
                        <input type="text" id="card_linkedin" name="card_linkedin" value="<?= htmlspecialchars($user["card_linkedin"]) ?>"><br>
                        <label for="card_picture">Profile Picture:</label>
                        <input type="file" id="card_picture" name="card_picture" accept="image/*"><br>
                        <div id="linksContainer">
                            <?php
                            $stmt = $mysqli->prepare("SELECT * FROM user_links WHERE user_id = ?");
                            $stmt->bind_param("i", $_SESSION["user_id"]);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $user_links = $result->fetch_all(MYSQLI_ASSOC);
                            ?>
                            <?php foreach ($user_links as $link): ?>
                                <div class="link" data-link-id="<?= htmlspecialchars($link["id"])?>">
                                    <label for="links[<?= htmlspecialchars($link["id"]) ?>][name]">Link Name:</label>
                                    <input type="text" id="links[<?= htmlspecialchars($link["id"]) ?>][name]" name="links[<?= htmlspecialchars($link["id"]) ?>][name]" value="<?= htmlspecialchars($link["link_name"]) ?>"><br>
                                    <label for="links[<?= htmlspecialchars($link["id"]) ?>][url]">Link URL:</label>
                                    <input type="text" id="links[<?= htmlspecialchars($link["id"]) ?>][url]" name="links[<?= htmlspecialchars($link["id"]) ?>][url]" value="<?= htmlspecialchars($link["link_url"]) ?>"><br>
                                    <button type="submit" name="remove_link_id" value="<?= htmlspecialchars($link["id"]) ?>">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="newLinksContainer"></div>
                        <button type="button" id="addLinkButton">Add Link</button>
                        <input type="submit" value="Submit" id="submitButton">
                    </form>
                    <button type="button" id="backButton">Back</button>
                </div>
                <div id="previewDiv">
                    <div class="profile-picture-container">
                        <?php if (!empty($user["card_picture"])): ?>
                            <img src="<?= htmlspecialchars($user["card_picture"]) ?>" alt="Profile Picture" class="profile-picture">
                        <?php endif; ?>
                        <div class="fade-overlay"></div>
                        <div class="profile-name"><?= htmlspecialchars($user["card_name"]) ?></div>
                    </div>
                    <div class="profile-info">
                        <p>Email: <br><?= htmlspecialchars($user["card_email"]) ?></p>
                    </div>
                    <div class="profile-info">
                        <p>Phone: <br><?= htmlspecialchars($user["card_phone"]) ?></p>
                    </div>
                    <div class="profile-info">
                        <p>LinkedIn: <br><a href="<?= htmlspecialchars($user["card_linkedin"]) ?>" target="_blank"><?= htmlspecialchars($user["card_linkedin"]) ?></a></p>
                    </div>
                    <?php if (!empty($user_links)): ?>
                        <div class="profile-info">
                            <?php foreach ($user_links as $link): ?>
                                <p><?= htmlspecialchars($link["link_name"]) ?>: <br><a href="<?= htmlspecialchars($link["link_url"]) ?>" target="_blank"><?= htmlspecialchars($link["link_url"]) ?></a></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="buttons">
            <button id="editButton" onclick="toggleEditForm()"><i class="fa-solid fa-pen-to-square"></i> Edit Page</button>
            <button id="visitPage"><i class="fa-solid fa-arrow-up-right-from-square"></i><a href="<?= htmlspecialchars($user["link"]) ?>" target="_blank"> Visit Site</a></button>
            <!--<button id="deleteButton" onclick="deleteUser()"><i class="fa-solid fa-trash"></i> Delete</button>-->
        </div>
        <?php else: ?>
    <div class="wrapper">
    <div id="header">
        <div class="main">
            NETWORKING<br> AT<br> EASE
        </div>
        <div class="button-container">
            <button class="btn" onclick="window.location.href = 'login.php'"><span>Get Started</span></button>
        </div>
    </div>

    <div class="dark-grey-section">
        <div class="content-wrapper">
            <div class="text-section">
                <p>NFC business cards offer a sleek and efficient way to share your contact information.
                     With just a tap on a smartphone, your details are instantly transferred, eliminating the need for manual entry or additional apps. 
                     These cards retain the feel of traditional business cards while integrating new technology, ensuring you leave a lasting impression.
                    Elevate your networking experience with the convenience of NFC business cards.</p>
            </div>
            <div class="image-section">
                <img src="img/nfc.jpg">
            </div>
        </div>
    </div>
    </div>
<?php endif; ?>

</body>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var linkCount = 0;

    function addLink() {
        var newLinksContainer = document.getElementById('newLinksContainer');
        var linkDiv = document.createElement('div');
        linkDiv.className = 'link';
        linkDiv.innerHTML = `
            <label for="new_links[${linkCount}][name]">Link Name:</label>
            <input type="text" id="new_links[${linkCount}][name]" name="new_links[${linkCount}][name]" required><br>
            <label for="new_links[${linkCount}][url]">Link URL:</label>
            <input type="text" id="new_links[${linkCount}][url]" name="new_links[${linkCount}][url]" required><br>
            <button type="button" class="remove-link">Remove Link</button>
        `;
        newLinksContainer.appendChild(linkDiv);
        bindRemoveLinkListeners();
        linkCount++;
    }

    document.getElementById('addLinkButton').addEventListener('click', addLink);

    function toggleEditForm() {
        document.getElementById('editForm').style.display = 'block';
        document.getElementById('previewDiv').style.display = 'none';
    }

    function toggleBack() {
        document.getElementById('editForm').style.display = 'none';
        document.getElementById('previewDiv').style.display = 'block';
    }

    function bindRemoveLinkListeners() {
        document.querySelectorAll('.remove-link').forEach(button => {
            button.removeEventListener('click', removeLink);
            button.addEventListener('click', removeLink);
        });
    }

    function removeLink(event) {
        event.target.closest('.link').remove();
    }

    document.getElementById('editButton').addEventListener('click', toggleEditForm);
    document.getElementById('backButton').addEventListener('click', toggleBack);
});
</script>
</html>
