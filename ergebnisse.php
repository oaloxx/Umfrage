<?php
include_once('db.php');


$stmt = $db->query("SELECT * FROM frage ORDER BY id");
$fragen = $stmt->fetchAll(PDO::FETCH_ASSOC);
$fragenById = array_column($fragen, null, 'id');


$stmt = $db->query("
    SELECT ma.*, f.id AS fragen_id, COUNT(aa.id) AS anzahl_antworten
    FROM moeglicheantwort ma
    JOIN frage f ON ma.frageid = f.id
    LEFT JOIN abgegebeneantwort aa ON ma.id = aa.antwortid
    GROUP BY ma.id
");
$moegliche_antworten = $stmt->fetchAll(PDO::FETCH_ASSOC);


foreach ($moegliche_antworten as $antwort) {
    $fragenById[$antwort['fragen_id']]['antworten'][] = $antwort;
}


$stmt = $db->query("SELECT COUNT(*) FROM nutzertoken");
$gesamtanzahl_nutzer = $stmt->fetchColumn();


foreach ($fragenById as &$frage) {
    $frage['gesamtanzahl_antworten'] = array_sum(array_column($frage['antworten'], 'anzahl_antworten'));
    $frage['keine_antwort'] = $gesamtanzahl_nutzer - $frage['gesamtanzahl_antworten'];
}

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Umfrageergebnisse</title>
</head>
<body>
    <h1>Umfrageergebnisse</h1>
    <?php foreach ($fragenById as $frage): ?>
        <h2><?php echo htmlspecialchars($frage['fragentext']); ?></h2>
        <p>Gesamtanzahl Antworten: <?php echo $frage['gesamtanzahl_antworten']; ?></p>
        <ul>
            <?php foreach ($frage['antworten'] as $antwort): ?>
                <li>
                    <?php echo htmlspecialchars($antwort['antworttext']); ?>:
                    <?php echo $antwort['anzahl_antworten']; ?> 
                    (<?php echo round(($antwort['anzahl_antworten'] / $gesamtanzahl_nutzer) * 100, 2); ?>%)
                </li>
            <?php endforeach; ?>
            <li>
                Keine Antwort: <?php echo $frage['keine_antwort']; ?>
                (<?php echo round(($frage['keine_antwort'] / $gesamtanzahl_nutzer) * 100, 2); ?>%)
            </li>
        </ul>
    <?php endforeach; ?>
    <a href="json_export.php">Ergebnisse als JSON herunterladen</a>
</body>
</html>