<?php

require_once("Class/Parser_class.php");

try {
  $bdd = new PDO('mysql:host=localhost;dbname=chochoi;charset=utf8', 'root', 'root');
}
catch (Exception $e) {
  die('Erreur : ' . $e->getMessage());
}
//getArrayOfDocByWord("north");
//showDocWithResult("korea", "AP890101-0015") ;
//getArrayOfDocByArrayWord(array("north", "korea"), 1) ;
//getNumberOfWordByArrayDoc(array("north", "korea"), getArrayOfDocByArrayWord(array("north", "korea")));
//getMinimalDocWithResult(array("north", "korea"), "AP891228-0004");
//getArrayOfPosByArrayWordAndDoc(array("junior", "steven", "traveled", "miles"), "AP891218-0001");


function getArrayOfDocByWord($word) {
  global $bdd;
  $response = $bdd->prepare(' SELECT `name`
                              FROM `document`
                              WHERE `id` IN (
                                              SELECT `id_document`
                                              FROM `position`
                                              WHERE `id_word` = (
                                                                  SELECT `id`
                                                                  FROM `word`
                                                                  WHERE `word` = ?
                                                                )
                                            )
                            ');
  $response->execute(array($word));
  while ($donnees = $response->fetch()) {
    echo "$donnees[0] <br/>";
  }
  $response->closeCursor();
}
// function getArrayOfDocByArrayWord($arrayWord, $page) {
//     global $bdd;
//
//     $pagination = 10;
//     $limit_start = ($page - 1) * $pagination;
//     $qMarks = str_repeat('?,', count($arrayWord) - 1) . '?';
//
//     if (!isset($_SESSION['Compteur_Page']))
//     {
//         $req = $bdd->prepare("SELECT count(distinct id_document)
//                               FROM document, position, word
//                               WHERE word IN ($qMarks)
//                                       AND id_document = document.id
//                                       AND id_word = word.id
//
//           ");
//           $req->execute($arrayWord);
//           $nb_total=$req->fetch()[0];
//           $_SESSION['Compteur_Page'] = $nb_total;
//           $req->closeCursor();
//     }
//     else
//     {
//       echo "Création";
//       $nb_total = $_SESSION['Compteur_Page'];
//     }
//
//     $response = $bdd->prepare("SELECT name, (SUM(log) * COUNT(log) * COUNT(log) * COUNT(log)) as pagerank
//                                 FROM
//                                   (SELECT *,(LOG(document / occurence_total) * (occurence_dans_document / nombre_mot_doc)) as log
//                                     FROM
//                                     (SELECT word.word, document.name,
//                                     (SELECT count(*)
//                                       FROM position WHERE position.id_word = word.id and position.id_document = document.id) as occurence_dans_document,
//                                     (SELECT count(*) as nb_document
//                                       FROM document) as document,
//                                     (SELECT count(distinct id_document) as nb_occurence
//                                       FROM position WHERE position.id_word = word.id) as occurence_total,
//                                     (SELECT count(*)
//                                       FROM position WHERE id_document = document.id) as nombre_mot_doc
//                                   FROM document, position, word
//                                   WHERE word IN ($qMarks)
//                                   AND id_document = document.id
//                                   AND id_word = word.id
//                                   GROUP BY id_document, id_word) as newtable) as newtable2
//                                   GROUP BY name
//                                   ORDER BY `pagerank`  DESC
//                                   LIMIT ".$limit_start.", ".$pagination."
//     ");
//     $response->execute($arrayWord);
//
//     $res = array();
//     while ($donnees = $response->fetch()) {
//       $res[$donnees[0]] = $donnees[1];
//     }
//     $response->closeCursor();
//     $nb_pages = ceil($nb_total/ $pagination);
//
//     return array($nb_total, $nb_pages, $res);
//   }
function getArrayOfDocByArrayWord($arrayWord, $page) {
  global $bdd;
  $pagination = 10;
  $limit_start = ($page - 1) * $pagination;



  //$qMarks = str_repeat('?|', count($arrayWord) - 1) . '?';
  $arrayWord = implode("|",$arrayWord);

   if (!isset($_SESSION['arrayResult'])) {

    $response = $bdd->prepare("   SELECT word, document.name, position
                                  FROM document, position, word
                                  WHERE word  REGEXP ?
                                  AND id_document = document.id
                                  AND id_word = word.id
                                  GROUP BY document.name, word, position
                                  ORDER BY word.word ASC
                              ");


    $response->execute(array("^($arrayWord)$"));
    $idf = array();
    while ($donnees = $response->fetch()) {
      $idf[$donnees[0]][$donnees[1]][] = $donnees[2];
    }
    $response->closeCursor();

    $res = array();
    foreach($idf as $word => &$arrayFileArrayPos) {
      $numberOfFile = count($arrayFileArrayPos);
      foreach($arrayFileArrayPos as $file => &$arrayPos) {
        $response = $bdd->prepare(" SELECT count(*)
                                    FROM position
                                    WHERE id_document = 	(   SELECT id
                                                              FROM document
                                                              WHERE name = ?)
                                  ");
        $response->execute(array($file));
        $bf = $response->fetch()[0];
        $log = (count($arrayPos)/$bf)*log(3102/$numberOfFile);
        $response->closeCursor();


        if(array_key_exists($file, $res)) {
          $res[$file] += $log*10 + (similarity($oldArrayPos[$file], $arrayPos));
        } else {
          $res[$file] = $log;
        }
        $oldArrayPos[$file] = $arrayPos;
      }
    }



    arsort($res);
    // foreach($res as $file => $rtf) {
    //   $resultat[] = $file;
    //   // echo "$file.....";
    //   // echo $rtf;
    //   // echo "<br/>";
    // }
    $nb_total = count($res);
    $resByPage;
    $i=0;
    $j=0;
    foreach($res as $file => $rtf) {
      if(($i%10)!=0) {
        $resByPage[$j][$file] = $rtf;
        $i++;
      } else {
        $j++;
        $i++;
        $resByPage[$j][$file] = $rtf;
      }
    }


    $nb_pages = ceil($nb_total/ $pagination);
    if(isset($resByPage)) {
      $_SESSION['arrayResult'] = serialize(array($nb_total, $nb_pages, $resByPage));
      return array($nb_total, $nb_pages, $resByPage[1]);
    } else {
      return array(0, 0, array());
    }

  } else {
    $tmp = unserialize($_SESSION['arrayResult']);
    return array($tmp[0], $tmp[1], $tmp[2][$page]);
  }

}

function similarity($array1, $array2) {
  $res=0;
  foreach($array1 as $pos1) {
    foreach($array2 as $pos2) {
      if(abs($pos1 - $pos2) < 3) {
        $res++;
      }
    }
  }
  // echo $res;
  // echo "<BR/>";
  return $res;
}



function getNumberOfWordByArrayDoc($arrayWord, $arrayDoc){
  global $bdd;
  $qMarks_doc = str_repeat('?,', count($arrayDoc) - 1) . '?';
  $qMarks_word = str_repeat('?,', count($arrayWord) - 1) . '?';

  $response = $bdd->prepare(" SELECT count(*)
                              FROM `position`
                              WHERE `id_document` IN ($qMarks_doc)
                              AND `id_word` IN ($qMarks_word)
                           ");

  $response->execute(array_merge($arrayDoc, $arrayWord));
  print_r($response);
  print_r(array_merge($arrayDoc, $arrayWord));
  $res = array();
  while ($donnees = $response->fetch()) {
    echo $donnees[0];
  }
  $response->closeCursor();

}



function getArrayOfPosByWordAndDoc($word, $doc) {
  global $bdd;
  $response = $bdd->prepare(' SELECT `position`
                              FROM `position`
                              WHERE `id_word` = (
                                                  SELECT `id`
                                                  FROM `word`
                                                  WHERE `word` = ?
                                                )
                              AND `id_document` = (
                                                  SELECT `id`
                                                  FROM `document`
                                                  WHERE `name` = ?
                                                  )
                           ');
  $response->execute(array($word, $doc));
  $res = array();
  while ($donnees = $response->fetch()) {
    $res[] = $donnees[0];
  }
  $response->closeCursor();
  return $res;
}

function getArrayOfPosByArrayWordAndDoc($arrayWord, $doc) {
  global $bdd;
  $qMarks_word = str_repeat('?,', count($arrayWord) - 1) . '?';

  $response = $bdd->prepare(" SELECT `position`, `id_word`
                              FROM `position`
                              WHERE `id_word` IN (
                                                  SELECT `id`
                                                  FROM `word`
                                                  WHERE `word` IN ($qMarks_word)
                                                )
                              AND `id_document` IN(
                                                    SELECT `id`
                                                    FROM `document`
                                                    WHERE `name` = ?
                              )
                              ORDER BY `position`.`position` ASC
  ");
  $arrayWord[]=$doc;
  $response->execute($arrayWord);
  $res = array();
  while ($donnees = $response->fetch()) {
    $res[$donnees[1]][] = $donnees[0];
  }
  print_r($res);
  $response->closeCursor();
  getReferenceValueByArrayPos($res);
  return $res;
}
function getReferenceValueByArrayPos($arrayOfArrayPos) {
  echo "<br />";
  echo "<br />";
  echo "<br />";
  sort($arrayOfArrayPos);
  print_r($arrayOfArrayPos);
  foreach($arrayOfArrayPos as $arrayPos) {
    foreach($arrayPos as $pos) {

    }
  }
}

function showDocWithResult($arWord, $doc, $stopWord) {

  $count = 0;
  $file = explode("-", $doc)[0];


  if ((!$fp = fopen("Ressources/AP/$file","r"))) {
      throw new Exception("File Not Found", 1);
  }
  else
  {
    $word = stem_english(implode(",", $arWord));
    $arrayword = explode(",", $word);


    //Tableau stop word
    $wordStopping = implode(",", $stopWord);
    $arrayWordStopping = explode(",", $wordStopping);

    while(fgets($fp))
    {
      $docnum = array_splice(explode(" ", fgets($fp)), 1, -1)[0];
      if ($docnum == $doc)
      {
        while(trim($line = fgets($fp)) != "<TEXT>"){
        }

        $text = '<p class="Contenu_Result">';
        while(trim($line = fgets($fp)) != "</TEXT>"){
          $arrayWord = preg_split("/\s+/", trim($line));
          
          foreach ($arrayWord as $value) {
            if(!in_array($value, $arrayWordStopping))
            {
              // if(in_array(strtolower(stem_english(trim($value, ",."))), $arrayword))
              // {
              //     $text .= "<span style='color:red;'>$value </span>";
              //     $count++;
              // }
              $trouve = false;
              foreach ($arrayword as $val) {
                if (ereg("^$val$", strtolower(stem_english(trim($value,",.")))))
                {
                  $text .= "<span style='color:red;'>$value </span>";
                  $count++;
                  $trouve = true;
                  break;
                }
              }
              if ($trouve == false)
              { 
                  $text .= $value." ";
              }
            }
            else
              $text .= "<span style='color:#0000FF;'>$value </span>";

          }
        }
        $text .= "</p>";
        $title = '<p class="Title_Result"> Document ' . $file . '</p> <br/> <br/>';
        $title .= '<p>Search words : <span style="color:red;">'.$word.'</span> ('.$count.' found ) </p>';
        break;
      }
      else
      {
        while(trim($line = fgets($fp)) != "</DOC>"){
        }
        $text = "<p class='center'><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge center'>The system encountered an internal error !<br/> <br/> Document Not Found.</p>";
      }
    }
    fclose($fp);

    if(isset($title))
    {
      $text = $title . $text;
    }

    return $text;
  }
}

  function getMinimalDocWithResult($arWord, $doc) {
    $file = explode("-", $doc)[0];
    $counterCurrentWordInDoc=0;
    $res="";
    $arrayPos =array();
    foreach($arWord as $word) {
      $arrayPos = array_merge($arrayPos, getArrayOfPosByWordAndDoc($word, $doc));
    }


    $fp = fopen("Ressources/AP/$file","r");
    while(!feof($fp)) {
      $line = fgets($fp);
      $arrayWord = explode(" ", $line);
      if($arrayWord[0]=="<DOCNO>") {
        if($arrayWord[1]=="$doc") {
          do {
            $line = fgets($fp);
            $arrayWord = explode(" ", $line);
          }  while(trim($line)!="<TEXT>");

          while(trim($line)!="</TEXT>") {
            foreach ($arrayWord as $word) {
              if($counterCurrentWordInDoc < 20) {
                if(in_array($counterCurrentWordInDoc, $arrayPos)) {

                  $res.="<span style='color:red;'>$word </span>";
                }else {
                  $res.="$word ";
                }
                if(preg_replace('#[^0-9a-z ]+#i','',trim($word))!="") {
                  $counterCurrentWordInDoc++;
                }
              }
              else {
                return $res.="...";
              }
            }
            $line = fgets($fp);
            $arrayWord = explode(" ", $line);
          }

        }
      }

    }
  }

?>
