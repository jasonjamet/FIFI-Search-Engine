<?php 
session_start();
if (isset($_SESSION['id_key_document']))
	echo $_SESSION['id_key_document'];
?>
