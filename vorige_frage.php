<?php
session_start();

if (!isset($_SESSION['fragen']) || !isset($_SESSION['fragenindex'])) {
    header('Location: index.php');
    exit;
}

if ($_SESSION['fragenindex'] > 0) {
    $_SESSION['fragenindex']--;
}

header('Location: index.php');
exit;
?>