<?php
require_once('Class/ChargeurClass.php');
require_once('function.php');

function buildIndex() 
{

  global $Parser;

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

  // Hash
  $Tableau_Mot = new QuickHashStringIntHash( 1024 );
  $Tableau_StopWord = $Parser->__get('_StopWords');
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
        while(fgets($fp))
        {
          $id_key_document++;
          $position = 0;
          $Requete_doc.=  "('".array_splice(explode(" ", fgets($fp)), 1, -1)[0]."'),";

          while(trim($line = fgets($fp)) != "<TEXT>"){
          }

          while(trim($line = fgets($fp)) != "</TEXT>"){
             $arrayWord = preg_split("/\s+/", preg_replace('#[^0-9a-z ]+#i','',trim($line)));
            foreach ($arrayWord as $value) {
              $position++;
              $value = strtolower($value);
              if(!isset($Tableau_StopWord[$value])) {
                $value = stem_english($value);
                $key_word_search = "";
                if (!isset($Tableau_Mot[$value]))
                {
                  $id_key_word++;
                  $Requete_word .= "('$value'),";
                  $Tableau_Mot[$value] = $key_word_search = $id_key_word;
                }
                else
                {
                  $key_word_search = $Tableau_Mot[$value];
                }
                if($Limit_counter_position<100000){
                  $Requete_position.= "('$key_word_search', '$id_key_document', '$position'),";
                  $Limit_counter_position++;
                } else {
                  $Requete_position = substr($Requete_position,0,-1).";";
                  $Array_requete_position[] = $Requete_position;
                  $Requete_position = "INSERT INTO `position`(`id_word`, `id_document`, `position`) VALUES ('$key_word_search', '$id_key_document', '$position'),";
                  $Limit_counter_position=0;

                }
              }
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
  //echo $Requete_position;
  $bdd->exec($Requete_doc);
  $bdd->exec($Requete_word);
  foreach($Array_requete_position as $RP){
    $bdd->exec($RP);
  }

  closedir($dir);
}

if ((isset($_POST['recherche'])) || (isset($_POST['install'])))
{
  try
  {
    $Parser = new Parser();
   /* @$shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "w", 0666, 0);
    if (!empty($shm_parser)) {
      $shm_size = shmop_size($shm_parser);
        $Parser->unserialize(shmop_read($shm_parser, 0, $shm_size));
    } else {
      $shm_parser = shmop_open(ftok("Class/Parser_class.php",'c'), "c", 0666, 10000);
        $Parser->ParserStopWords();
    }*/

    // Search && Index
    $Parser->ParserStopWords();
    if (isset($_POST['recherche']))
    {
      if ($Parser->isIndex())
        $Parser->ParserRequest($_POST['recherche']);
      else
        echo 2;
    }
    else
    {
      if ((time() - $Parser->__get('_TimeIndexation')) < 0)
        echo 6;
      else
      {
        try
        {
          buildIndex();
          $Parser->__set('_TimeIndexation', time());
          echo 3;
        }
        catch (Exception $e)
        {
          echo 4;
        }
      }
    }
   /* if (!shmop_write($shm_parser, $Parser->serialize(), 0)) {
      throw new Exception("Memory Access", 2);
    }
    shmop_close($shm_parser);*/
  }
  catch (Exception $e)
  {
    if ($e->getCode() == 2)
      echo 1;
    else
      echo 5;
  }
}
else
{
  echo 1;
}
?>
