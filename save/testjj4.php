<?php
require_once('../Class/ChargeurClass.php');
require_once('../function.php');
try {
  $bdd = new PDO('mysql:host=localhost;dbname=chochoi;charset=utf8', 'root', 'root');
}
catch (Exception $e) {
  die('Erreur : ' . $e->getMessage());
}

getPOPO(array("north","korea"));

function getPOPO($arrayWord) {
  global $bdd;

  $qMarks = str_repeat('?,', count($arrayWord) - 1) . '?';
  if (!isset($_SESSION['arrayResult'])) {

    $response = $bdd->prepare("   SELECT word, document.name, position
                                  FROM document, position, word
                                  WHERE word
                                  IN ($qMarks)
                                  AND id_document = document.id
                                  AND id_word = word.id
                                  GROUP BY document.name, word, position
                                  ORDER BY word.word ASC
    ");
    $response->execute($arrayWord);

    $idf = array();
    while ($donnees = $response->fetch()) {
      $idf[$donnees[0]][$donnees[1]][] = $donnees[2];
    }
    $response->closeCursor();

    $res = array();
    foreach($idf as $word => &$arrayFileArrayPos) {
      $numberOfFile = count($arrayFileArrayPos);
      $oldArrayPos;
      foreach($arrayFileArrayPos as $file => &$arrayPos) {
        $response = $bdd->prepare(" SELECT count(*)
                                    FROM position
                                    WHERE id_document = 	(   SELECT id
                                                              FROM document
                                                              WHERE name = ?)");
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
    foreach($res as $file => $rtf) {
      $resultat[] = $file;
      // echo "$file.....";
      // echo $rtf;
      // echo "<br/>";
    }
    //$nb_pages = ceil($nb_total/ $pagination);
    $_SESSION['arrayResult'] = serialize($res);
    //return array($nb_total, $nb_pages, $resultat);
  }
  return unserialize($_SESSION['arrayResult']);
}

// function similarity($array1, $array2) {
//   $res=0;
//   foreach($array1 as $pos1) {
//     foreach($array2 as $pos2) {
//       if(abs($pos1 - $pos2) < 3) {
//         $res++;
//       }
//     }
//   }
//   // echo $res;
//   // echo "<BR/>";
//   return $res;
// }
?>
