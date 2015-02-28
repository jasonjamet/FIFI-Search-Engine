<?php
set_time_limit (0);
require_once('Class/ChargeurClass.php');

$Parser = new Parser();
@$shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "a", 0666, 0);
if (!empty($shm_parser)) {
  $shm_size = shmop_size($shm_parser);
  $Parser->unserialize(shmop_read($shm_parser, 0, $shm_size));
} else {
  $shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "c", 0666, 100000);
  $Parser->ParserStopWords();
  shmop_write($shm_parser, $Parser->serialize(), 0);
}

shmop_close($shm_parser);
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
$id_key_word = 0;
$id_key_document = 0;

// Vidage des tables
$vide_document = "TRUNCATE TABLE document";
$vide_word = "TRUNCATE TABLE word";
$vide_position = "TRUNCATE TABLE position";
$bdd->exec($vide_document);
$bdd->exec($vide_position);
$bdd->exec($vide_word);

// Tableau
$Tableau_Mot = array();
$Requete_doc = "INSERT INTO `document`(`name`) VALUES ";
$Requete_word = "INSERT INTO `word`(`word`) VALUES ";
$Requete_position = "INSERT INTO `position`(`id_word`, `id_document`, `position`) VALUES ";
$Array_requete_position = array();
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
        $id_key_document++;
        $position = 0;
        $Requete_doc.=  "('".array_splice(explode(" ", fgets($fp)), 1, -1)[0]."'),";
        //$insert1->execute(array('name' => array_splice(explode(" ", fgets($fp)), 1, -1)[0]));

        while(trim($line = fgets($fp)) != "<TEXT>"){
        }

        while(trim($line = fgets($fp)) != "</TEXT>"){
          $arrayWord = explode(" ", ereg_replace("[^a-zA-Z0-9 ]*","",trim($line)));
          foreach ($arrayWord as $value) {
            $position++;
            $value = strtolower($value);
            if(!in_array($value ,$Parser->__get("_StopWords"))) {
              $value= stem_english($value);
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
                $Array_requete_position[] = $Requete_position;
                $Requete_position = "INSERT INTO `position`(`id_word`, `id_document`, `position`) VALUES ('$id_key_word', '$id_key_document', '$position'),";
                $Limit_counter_position=0;

              }
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
$Requete_position = substr($Requete_position,0,-1).";";
$Array_requete_position[] = $Requete_position;

// Insertion Requete
$Requete_doc = substr($Requete_doc,0,-1).";";
$Requete_word = substr($Requete_word,0,-1).";";
$Requete_position = substr($Requete_position,0,-1).";";

//echo $Requete_word;
echo $Requete_position;
$bdd->exec($Requete_doc);
$bdd->exec($Requete_word);
foreach($Array_requete_position as $RP){
  $bdd->exec($RP);
}

closedir($dir);

?>
