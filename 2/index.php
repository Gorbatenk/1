<?php
$items = [];
$error = null;

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = urlencode($_GET['search']);

    $apiKey = "AIzaSyCvUVTJsalca_YsBIhbB2nR-FLYEJavmnI";
    $cx = "e0b6afe431c814802";

    $url = "https://www.googleapis.com/customsearch/v1?key=$apiKey&cx=$cx&q=$search";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resultJson = curl_exec($ch);
    curl_close($ch);

    $resultArray = json_decode($resultJson, true);
    if (isset($resultArray['error'])) {
        $error = $resultArray['error']['message'];
    } else {
        $items = $resultArray['items'] ?? [];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Browser</title>
</head>
<body>
<h2>My Browser</h2>
<form method="GET" action="/index.php">
    <label for="search">Search:</label>
    <input type="text" id="search" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <input type="submit" value="Submit">
</form>

<hr>

<?php if ($error): ?>
    <p>Error: <?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($items)): ?>
    <ul>
        <?php foreach ($items as $item): ?>
            <li>
                <a href="<?= $item['link'] ?>" target="_blank"><?= $item['title'] ?></a><br>
                <?= $item['snippet'] ?>
            </li>
            <br>
        <?php endforeach; ?>
    </ul>
<?php elseif (isset($_GET['search']) && !$error): ?>
    <p>No results found for "<b><?= htmlspecialchars($_GET['search']) ?></b>"</p>
<?php endif; ?>
</body>
</html>
