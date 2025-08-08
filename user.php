<?php
require_once 'database.php'; // Include your database connection file

$linkFromURL = isset($_GET['link']) ? $_GET['link'] : null;

if ($linkFromURL) {
    // Using prepared statements to prevent SQL injection
    $stmt = $mysqli->prepare("SELECT * FROM user WHERE link = ?");
    $stmt->bind_param("s", $linkFromURL);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Fetch user links
        $stmt = $mysqli->prepare("SELECT * FROM user_links WHERE user_id = ?");
        $stmt->bind_param("i", $row["id"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_links = $result->fetch_all(MYSQLI_ASSOC);
    } else {
        echo "User not found.";
        exit; // Stop the script if the user is not found
    }
} else {
    echo "No user specified.";
    exit; // Stop the script if no link is provided
}

$mysqli->close();

// Handle vCard generation if requested
if (isset($_GET['action']) && $_GET['action'] === 'download_vcard') {
    $vcard = "BEGIN:VCARD\n";
    $vcard .= "VERSION:3.0\n";
    $vcard .= "FN:" . htmlspecialchars($row['card_name']) . "\n";
    $vcard .= "EMAIL:" . htmlspecialchars($row['card_email']) . "\n";
    $vcard .= "TEL:" . htmlspecialchars($row['card_phone']) . "\n";
    $vcard .= "END:VCARD\n";

    header('Content-Type: text/vcard');
    header('Content-Disposition: attachment; filename="contact.vcf"');
    echo $vcard;
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Business Card</title>
    <link rel="stylesheet" href="css/userStyle.css">
    <script src="https://kit.fontawesome.com/9f694c8a5b.js" crossorigin="anonymous"></script>
</head>
<body>
<div id="header" style="background-image: url('<?= htmlspecialchars($row["card_picture"]) ?>');">
    <div class="header-text">
        <h1><span><?php echo "<p>" . htmlspecialchars($row['card_name']) . "</p>"; ?></span></h1>
    </div>
</div>

<div id="contacts">
    <div class="container">
        <h1 class="sub-title">Contact</h1>
        <div class="contact-list">
            <div>
                <h2><i class="fa-solid fa-envelope"></i> Email</h2>
                <a href="mailto:<?php echo htmlspecialchars($row['card_email']); ?>" target="_blank" style="text-decoration: none; font-size: 24px; color: #fff; z-index: 1; pointer-events: auto;"><?php echo "<p>" . htmlspecialchars($row['card_email']) . "</p>"; ?></a>
            </div>
            <div>
                <h2><i class="fa-solid fa-phone"></i> Phone</h2>
                <a href="tel:<?php echo htmlspecialchars($row['card_phone']) ?? ''; ?>" style="text-decoration: none; font-size: 24px; color: #fff; z-index: 1; pointer-events: auto;"><?php echo htmlspecialchars($row['card_phone']) ?? 'N/A'; ?></a>
            </div>
            <div>
                <h2><i class="fa-brands fa-linkedin"></i> LinkedIn</h2>
                <a href="<?php echo htmlspecialchars($row['card_linkedin']); ?>" target="_blank" style="text-decoration: none; font-size: 24px; color: #fff; z-index: 1; pointer-events: auto;"><?php echo htmlspecialchars($row['card_linkedin']); ?></a>
            </div>
            <?php foreach ($user_links as $link): ?>
                <div>
                    <h2><i class="fa-solid fa-link"></i> <?= htmlspecialchars($link["link_name"]) ?></h2>
                    <a href="<?= htmlspecialchars($link["link_url"]) ?>" target="_blank" style="text-decoration: none; font-size: 24px; color: #fff; z-index: 1; pointer-events: auto;"><?= htmlspecialchars($link["link_url"]) ?></a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<div class="save-contacts">
    <a href="?link=<?= urlencode($linkFromURL) ?>&action=download_vcard" style="text-decoration: none; font-size: 24px; color: #fff; z-index: 1; pointer-events: auto;">
        <button>Save Contacts</button>
    </a>
</div>

<script>
    function getTextColor(rgb) {
        var cols = rgb.match(/^rgb\((\d+),\s*(\d+),\s*(\d+)\)$/);
        var rValue = cols[1];
        var gValue = cols[2];
        var bValue = cols[3];
        var luminance = (0.299 * rValue + 0.587 * gValue + 0.114 * bValue) / 255;

        // Choose text color based on luminance
        return luminance > 0.5 ? '#000' : '#fff';
    }

    // Get the computed background color of the header
    var headerBackgroundColor = window.getComputedStyle(document.getElementById('header')).backgroundColor;

    // Set the text color based on the background color
    var headerTextColor = getTextColor(headerBackgroundColor);
    document.getElementById('header').style.color = headerTextColor;
</script>
</body>
</html>
