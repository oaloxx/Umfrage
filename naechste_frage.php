// naechste_frage.php
<?php
session_start();

if (!isset($_SESSION['fragen']) || !isset($_SESSION['fragenindex'])) {
    header('Location: index.php');
    exit;
}

if (isset($_POST['ausgewaehlte_antwort'])) {
    include_once('db.php');
    $ausgewaehlte_antwort = intval($_POST['ausgewaehlte_antwort']);
    $aktuelle_frage = $_SESSION['fragen'][$_SESSION['fragenindex']];

    if ($aktuelle_frage['ausgewaehlteAntwortID'] != $ausgewaehlte_antwort) {
        if (!isset($_COOKIE['nutzertoken'])) {
            $db->query("INSERT INTO nutzertoken () VALUES ()");
            $nutzertoken = $db->insert_id;
            setcookie('nutzertoken', $nutzertoken, time() + (86400 * 30), "/"); 
        } else {
            $nutzertoken = $_COOKIE['nutzertoken'];
        }

        if ($ausgewaehlte_antwort == 0) {
            $stmt = $db->prepare("DELETE FROM abgegebeneantwort WHERE nutzertokenid = ? AND frageid = ?");
            $stmt->bind_param("ii", $nutzertoken, $aktuelle_frage['id']);
            $stmt->execute();
        } elseif ($aktuelle_frage['ausgewaehlteAntwortID'] == 0) {
            $stmt = $db->prepare("INSERT INTO abgegebeneantwort (nutzertokenid, frageid, antwortid) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $nutzertoken, $aktuelle_frage['id'], $ausgewaehlte_antwort);
            $stmt->execute();
        } else {
            $stmt = $db->prepare("UPDATE abgegebeneantwort SET antwortid = ? WHERE nutzertokenid = ? AND frageid = ?");
            $stmt->bind_param("iii", $ausgewaehlte_antwort, $nutzertoken, $aktuelle_frage['id']);
            $stmt->execute();
        }
        $stmt->close();

        $_SESSION['fragen'][$_SESSION['fragenindex']]['ausgewaehlteAntwortID'] = $ausgewaehlte_antwort;
    }

    $_SESSION['fragenindex']++;

    if ($_SESSION['fragenindex'] >= count($_SESSION['fragen'])) {
        header('Location: danke.html');
        exit;
    }
}

header('Location: index.php');
exit;
?>
