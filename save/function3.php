<?php
try {
  $bdd = new PDO('mysql:host=localhost;dbname=chochoi;charset=utf8', 'root', 'root');
}
catch (Exception $e) {
  die('Erreur : ' . $e->getMessage());
}
//getArrayOfDocByWord("north");
//showDocWithResult("korea", "AP890101-0015") ;
//getArrayOfDocByArrayWord(array("north", "korea"), 1)
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
function getArrayOfDocByArrayWord($arrayWord, $page) {
    global $bdd;

    $pagination = 10;
    $limit_start = ($page - 1) * $pagination;
    $qMarks = str_repeat('?,', count($arrayWord) - 1) . '?';

    $req = $bdd->prepare("  SELECT SUM( log ) AS log2, name
                            FROM (

                            SELECT ( 1 + count( position ) * LOG10( (3102 / (
                            SELECT count( * )
                            FROM position
                            WHERE id_word = id_word ) ))
                            ) AS log, id_word, word, document.name
                            FROM document, position, word
                            WHERE word
                            IN ($qMarks)
                            AND id_document = document.id
                            AND id_word = word.id
                            GROUP BY id_document, id_word
                            ORDER BY word.word DESC
                            ) AS test
                            GROUP BY test.name
                            ORDER BY `log2` ASC
      ");
      $req->execute($arrayWord);
      $nb_total=count($req->fetchAll());
      $req->closeCursor();



    $response = $bdd->prepare("SELECT *, SUM( log ) AS pagerank, name FROM
    (SELECT (1 + LOG10((SELECT count(*) from position where id_word = word.id and id_document = document.id)) * LOG10((SELECT count(*) from document) / (SELECT count(*) FROM position WHERE id_word = id_word))) AS log, document.name FROM document, position, word
    WHERE word IN ($qMarks)
    AND id_document = document.id
    AND id_word = word.id
    GROUP BY id_document, id_word
    ORDER BY word.word DESC) AS test
GROUP BY test.name
ORDER BY pagerank ASC LIMIT ".$limit_start.", ".$pagination."
    ");
    $response->execute($arrayWord);

    $res = array();
    while ($donnees = $response->fetch()) {
      $res[$donnees[1]] = $donnees[0];
    }
    $response->closeCursor();
    $nb_pages = ceil($nb_total/ $pagination);

    return array($nb_total, $nb_pages, $res);
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

function showDocWithResult($arWord, $doc) {

  $count = 0;
  $file = explode("-", $doc)[0];

  if ((!$fp = fopen("Ressources/AP/$file","r"))) {
      throw new Exception("File Not Found", 1);
  }
  else
  {
    $word = stem_english(implode(",", $arWord));
    $arrayword = explode(",", $word);
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
            if(in_array(strtolower(stem_english($value)), $arrayword))
            {
              $text .= "<span style='color:red;'>$value </span>";
              $count++;
            }
            else
            {
                $text .= $value." ";
            }
          }
        }
        $text .= "</p>";
        $title = '<p class="Title_Result"> Document ' . $file . '</p> <br/> <br/>';
        $title .= '<p><span style="color:red;">'.$word.'</span> (or equivalent) has been found '.$count.' time(s)</p>';
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
