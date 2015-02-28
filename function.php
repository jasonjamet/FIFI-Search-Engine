<?php
try {
  $bdd = new PDO('mysql:host=localhost;dbname=chochoi;charset=utf8', 'root', 'root');
}
catch (Exception $e) {
  die('Erreur : ' . $e->getMessage());
}
//getArrayOfDocByWord("north");
//showDocWithResult("korea", "AP890101-0015") ;
// print_r(getArrayOfDocByArrayWord(array("north", "korea")));
//getNumberOfWordByArrayDoc(array("north", "korea"), getArrayOfDocByArrayWord(array("north", "korea")));
//getMinimalDocWithResult("north", "AP890101-0015");
function getArrayPosByWordAndDoc($word, $doc){

}


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
function getArrayOfDocByArrayWord($arrayWord) {
  global $bdd;

  $pagination = 15;
  $limit_start = ($page - 1) * $pagination;

  $qMarks = str_repeat('?,', count($arrayWord) - 1) . '?';
  /*$response = $bdd->prepare("SELECT distinct name
                              FROM document
                              WHERE id IN (
                                              SELECT distinct id_document
                                              FROM position
                                              WHERE id_word IN (
                                                                  SELECT distinct id
                                                                  FROM word
                                                                  WHERE word IN ($qMarks)
                                                                 )
                                             )
                            ");*/

  $response = $bdd->prepare(" SELECT `name`
                              FROM `document`
                              INNER JOIN `position`
                              INNER JOIN `word`
                              WHERE word.word IN ($qMarks)
                              AND document.id = position.id_document
                              AND word.id = position.id_word

                            ");

  $response->execute($arrayWord);
  $res = array_count_values($response->fetchAll(PDO::FETCH_COLUMN, 0));
  arsort($res);
  $response->closeCursor();

  $nb_pages = ceil($nb_total/ $pagination);

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

function showDocWithResult($arWord, $doc) {
  $file = explode("-", $doc)[0];
  $counterCurrentWordInDoc=0;
  $arrayPos =array();
  foreach($arWord as $word) {
    $arrayPos = array_merge($arrayPos, getArrayOfPosByWordAndDoc($word, $doc));
  }
  $fp = fopen("Ressources/OP/$file","r");
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
              if(in_array($counterCurrentWordInDoc, $arrayPos)) {

                echo "<span style='color:red;'>$word </span>";
              }else {
                echo "$word ";
              }
              if(preg_replace('#[^0-9a-z ]+#i','',trim($word))!="") {
                $counterCurrentWordInDoc++;
              }
            }
            $line = fgets($fp);
            $arrayWord = explode(" ", $line);
          }

        }
      }

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


    $fp = fopen("Ressources/OP/$file","r");
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
