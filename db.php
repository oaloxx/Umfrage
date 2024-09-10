<?php
$db=new mysqli('localhost','root','','Umfrage');

if ($db->connect_error) {
    die($db->connect_error);
}
?>