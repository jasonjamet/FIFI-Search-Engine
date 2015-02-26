<?php
require_once('Class/ChargeurClass.php');
$fp = fopen("Ressources/AP/AP891217","r");

// Supprimer les stop words
// insert qui bug 3
// stemmer
// Memory ?
// Supprimer auto increment
// Supprimer BDD

try
{
  $bdd = new PDO('mysql:host=localhost;dbname=chochoi;charset=utf8', 'root', 'root');
}
catch (Exception $e)
{
        die('Erreur : ' . $e->getMessage());
}

$insert1 = $bdd->prepare('INSERT INTO document(name) VALUES(:name)');
$insert2 = $bdd->prepare('INSERT INTO word(word) VALUES(:word)');
$insert3 = $bdd->prepare('INSERT INTO position(id_word, id_document, position) VALUES(:id_word, :id_document, :position)');

$select1 = $bdd->prepare('SELECT MAX(LAST_INSERT_ID(id)) FROM word');
$select1->execute(array());
$id_key_word = $select1->fetch()[0];
$select1->closeCursor();

$select2 = $bdd->prepare('SELECT MAX(LAST_INSERT_ID(id)) FROM document');
$select2->execute(array());
$id_key_document = $select2->fetch()[0];
$select2->closeCursor();

$Tableau_Mot = array();

$Requete_word = "INSERT INTO `word`(`word`) VALUES ";

while(fgets($fp))
{
  $id_key_document++;
  $position = 0;
  $insert1->execute(array('name' => array_splice(explode(" ", fgets($fp)), 1, -1)[0]));
  while(trim($line = fgets($fp)) != "<TEXT>"){
  }
  while(trim($line = fgets($fp)) != "</TEXT>"){
    $arrayWord = explode(" ", ereg_replace("[^a-zA-Z0-9 ]*","",trim($line)));
    foreach ($arrayWord as $value) {
      $position++;
      $key_word_search = "";
      if (!array_key_exists(strtolower($value), $Tableau_Mot))
      {
        $id_key_word++;
        $Requete_word .= "('$value'),";
        //$insert2->execute(array('word' => $value));
        $Tableau_Mot[$value] = $id_key_word;
      }
      else
      {
        $key_word_search = array_search($value, $Tableau_Mot);
      }
      $insert3->execute(array('id_word' => '1', 'id_document' => 2, 'position' => 3));
    }
  }
  while(trim($line = fgets($fp)) != "</DOC>"){
  }
  break;
}


  fclose($fp);

  foreach ($Tableau_Mot as $key => $value) {
    echo $key ." / ".$value ."<br>";
  }

  $Requete_word = substr($Requete_word,0,-1).";";

  echo $Requete_word;
  $bdd->exec($Requete_word);
?>
