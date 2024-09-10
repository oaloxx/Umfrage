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

header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="umfrageergebnisse_' . date('Y-m-d') . '.json"');


echo json_encode(array_values($fragenById), JSON_PRETTY_PRINT);
?>