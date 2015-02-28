<?php
session_start();
set_time_limit (0);
require_once('Class/ChargeurClass.php');


// Supprimer les stop words
// insert qui bug 3
// Memory ?


$compte = 0;

try
{
  $bdd = new PDO('mysql:host=localhost;dbname=chochoi;charset=utf8', 'root', 'root');
}
catch (Exception $e)
{
  die('Erreur : ' . $e->getMessage());
}

$insert1 = $bdd->prepare('INSERT INTO document(name) VALUES(:name)');
$insert3 = $bdd->prepare('INSERT INTO position(id_word, id_document, position) VALUES(:id_word, :id_document, :position)');

// id key
$_SESSION['id_key_document'] = 0;
$id_key_document = 0;

// Vidage des tables
$vide_document = "TRUNCATE TABLE document";
$vide_position = "TRUNCATE TABLE word";
$vide_word = "TRUNCATE TABLE position";
$bdd->exec($vide_document);
$bdd->exec($vide_position);
$bdd->exec($vide_word);

// Tableau
$Tableau_Mot = array();
$Requete_word = "INSERT INTO `word`(`word`) VALUES ";
$Requete_position = "INSERT INTO `position`(`id_word`, `id_document`, `position`) VALUES ";
$Limit_counter_position = 0;

$dirname = 'Ressources/AP/';
$dir = opendir($dirname);
while($file = readdir($dir))
{
  if($file != '.' && $file != '..' && !is_dir($dirname.$file))
  {
    if (!$fp = fopen($dirname.$file,"r")) {
      throw new Exception("Attribut Introuvable !", 1);
    }
    else
    {
      //$fp = fopen("Ressources/AP/AP890101","r");
      while(fgets($fp))
      {
        $_SESSION['id_key_document']++;
        $position = 0;
        $insert1->execute(array('name' => array_splice(explode(" ", fgets($fp)), 1, -1)[0]));

        while(trim($line = fgets($fp)) != "<TEXT>"){
        }

        while(trim($line = fgets($fp)) != "</TEXT>"){
          $arrayWord = explode(" ", ereg_replace("[^a-zA-Z0-9 ]*","",trim($line)));
          foreach ($arrayWord as $value) {
            $value = strtolower(stem_english($value));
            $position++;
            $key_word_search = "";
            if (!array_key_exists($value, $Tableau_Mot))
            {
              $id_key_word++;
              $Requete_word .= "('$value'),";
              $Tableau_Mot[strtolower($value)] = $id_key_word;
            }
            else
            {
              $key_word_search = array_search($value, $Tableau_Mot);
            }
            if($Limit_counter_position<100000){
              $Requete_position.= "('$id_key_word', '$id_key_document', '$position'),";
              $Limit_counter_position++;
            } else {
              $Requete_position = substr($Requete_position,0,-1).";";
              $bdd->exec($Requete_position);
              $Requete_position = "INSERT INTO `position`(`id_word`, `id_document`, `position`) VALUES ('".$_SESSION['id_key_document']."', '$id_key_document', '$position'),";
              $Limit_counter_position=0;

            }
            //$insert3->execute(array('id_word' => '1', 'id_document' => 2, 'position' => 3));
          }
        }

        while(trim($line = fgets($fp)) != "</DOC>"){
        }
      }
      fclose($fp);
    }
  }
}

// Insertion Requete
$Requete_word = substr($Requete_word,0,-1).";";
$Requete_position = substr($Requete_position,0,-1).";";
//echo $Requete_word;
echo $Requete_position;
$bdd->exec($Requete_word);
$bdd->exec($Requete_position);

//closedir($dir);

?>
