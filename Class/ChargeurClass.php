<?php
// Chargeur de classe
function chargerClasse($classe)
{
  require_once $classe . '_class.php';
}

spl_autoload_register('chargerClasse');
?>