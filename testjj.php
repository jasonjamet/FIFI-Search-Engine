<?php
  echo "<div id='ajax'>Compteur<div>\n";
  echo "bonjour\n";

  session_start();

  require_once('Class/ChargeurClass.php');
  $dico = new Dictionary;
  $dico->getArrayOfDocByWord("the");
  $dico->getResultDocByWordAndDoc("the", "AP891217-0002");
?>
