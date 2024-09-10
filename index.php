<?php
session_start();


include_once('db.php');

if ($db->connect_error) {
    die("Verbindung fehlgeschlagen: " . $db->connect_error);
}

if (!isset($_SESSION['fragen'])) {
    $result = $db->query("SELECT * FROM frage ORDER BY id");
    $_SESSION['fragen'] = [];
    while ($row = $result->fetch_assoc()) {
        $_SESSION['fragen'][] = $row;
    }
    $result->free();
}


if (!isset($_SESSION['fragenindex'])) {
    $_SESSION['fragenindex'] = 0;
}


if ($_SESSION['fragenindex'] >= count($_SESSION['fragen'])) {
    $_SESSION['fragenindex'] = 0;
}

$aktuelle_frage = $_SESSION['fragen'][$_SESSION['fragenindex']];


$stmt = $db->prepare("SELECT * FROM moeglicheantwort WHERE frageid = ?");
$stmt->bind_param("i", $aktuelle_frage['id']);
$stmt->execute();
$result = $stmt->get_result();
$moegliche_antworten = [];
while ($row = $result->fetch_assoc()) {
    $moegliche_antworten[] = $row;
}
$stmt->close();


if (isset($_COOKIE['nutzertoken'])) {
    $nutzertoken = $_COOKIE['nutzertoken'];
    $stmt = $db->prepare("SELECT frageid, antwortid FROM abgegebeneantwort WHERE nutzertokenid = ?");
    $stmt->bind_param("i", $nutzertoken);
    $stmt->execute();
    $result = $stmt->get_result();
    $abgegebene_antworten = [];
    while ($row = $result->fetch_assoc()) {
        $abgegebene_antworten[$row['frageid']] = $row['antwortid'];
    }
    $stmt->close();

    foreach ($_SESSION['fragen'] as &$frage) {
        $frage['ausgewaehlteAntwortID'] = isset($abgegebene_antworten[$frage['id']]) ? $abgegebene_antworten[$frage['id']] : 0;
    }
} else {
    foreach ($_SESSION['fragen'] as &$frage) {
        $frage['ausgewaehlteAntwortID'] = 0;
    }
}

$db->close();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umfrage</title>
</head>
<body>
    <h1>Umfrage</h1>
    <form action="naechste_frage.php" method="post">
        <h2><?php echo htmlspecialchars($aktuelle_frage['fragentext']); ?></h2>
        <ul>
            <?php foreach ($moegliche_antworten as $antwort): ?>
                <li>
                    <input type="radio" name="ausgewaehlte_antwort" 
                           value="<?php echo $antwort['id']; ?>" 
                           id="antwort_<?php echo $antwort['id']; ?>"
                           <?php echo ($aktuelle_frage['ausgewaehlteAntwortID'] == $antwort['id']) ? 'checked' : ''; ?>>
                    <label for="antwort_<?php echo $antwort['id']; ?>">
                        <?php echo htmlspecialchars($antwort['antworttext']); ?>
                    </label>
                </li>
            <?php endforeach; ?>
            <li>
                <input type="radio" name="ausgewaehlte_antwort" 
                       value="0" id="keine_antwort"
                       <?php echo ($aktuelle_frage['ausgewaehlteAntwortID'] == 0) ? 'checked' : ''; ?>>
                <label for="keine_antwort">Keine Antwort</label>
            </li>
        </ul>
        <input type="submit" value="Nächste Frage">
    </form>
    <?php if ($_SESSION['fragenindex'] > 0): ?>
        <a href="vorige_frage.php">Vorige Frage</a>
        <!-- <button onclick="history.back()">Vorige Frage</button> -->
    <?php endif; ?>
</body>
</html>

