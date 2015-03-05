
<?php
require_once('../Class/ChargeurClass.php');
require_once('../function.php');
try {
  $bdd = new PDO('mysql:host=localhost;dbname=chochoi;charset=utf8', 'root', 'root');
}
catch (Exception $e) {
  die('Erreur : ' . $e->getMessage());
}

$arrayWordOp = array(array(array("chocap"), array("mari"), array("stephani"), array("bonjour")), array("and", "or", "and"));

getPOPO($arrayWordOp);

function getPOPO($arrayWordOp) {
  global $bdd;
  $qMarks = "";
  $firstOcc = true;
  for($i =0;$i<count($arrayWordOp[1]);$i++) {
    if($firstOcc) {
      $qMarks.="(
      id_word = (
      SELECT word.id
      FROM word
      WHERE word = ?
      )";
      $firstOcc = false;
    } if($arrayWordOp[1][$i] == "and") {
      $qMarks.="AND id_document IN (
      SELECT id_document
      FROM `position`
      WHERE id_word = (
      SELECT id
      FROM word
      WHERE word = ?
      )
      )";
    } else if($arrayWordOp[1][$i] == "or") {
      $firstOccAnd = true;
      $qMarks.=")OR (
      id_word = (
      SELECT word.id
      FROM word
      WHERE word = ?
      )";
    }


  }

  $response = $bdd->prepare("   SELECT (
  SELECT name
  FROM document
  WHERE id_document = document.id
  ) AS name
  FROM (
  SELECT DISTINCT id_document
  FROM position
  WHERE $qMarks
  ) ) AS a
  GROUP BY name ");

  var_dump($response);
  $response->execute(array("chocap","mari","stephani","bonjour"));

  $idf = array();
  while ($donnees = $response->fetch()) {
    echo $donnees[0];
  }
  // $qMarks = str_repeat('?,', count($arrayWord) - 1) . '?';
  //
  //
  // $qMarks = str_repeat("AND id_document IN (	SELECT id_document
  // FROM `position`
  // WHERE id_word = ( 		SELECT id
  // FROM word
  // WHERE word = ? )
  // )", count($arrayWord) - 1);
  // $response = $bdd->prepare("   SELECT word, document.name, count(position) AS counter
  //                               FROM document, position, word
  //                               WHERE word
  //                               IN ($qMarks)
  //                               AND id_document = document.id
  //                               AND id_word = word.id
  //                               GROUP BY document.name, word
  //                               ORDER BY word.word ASC
  // ");
  // $response->execute($arrayWord);
  //
  // $idf = array();
  // while ($donnees = $response->fetch()) {
  //   $idf[$donnees[0]][$donnees[1]] = $donnees[2];
  // }
  // $response->closeCursor();
  //
  //
  // foreach($idf as $word => &$arrayFileCount) {
  //   $numberOfFile = count($arrayFileCount);
  //   foreach($arrayFileCount as $file => &$count) {
  //     $response = $bdd->prepare(" SELECT count(*)
  //                                 FROM position
  //                                 WHERE id_document = 	(   SELECT id
  //                                                             FROM document
  //                                                             WHERE name = ?)");
  //     $response->execute(array($file));
  //     $bf = $response->fetch()[0];
  //     $count = ($count/$bf)*log(3102/$numberOfFile);
  //     $response->closeCursor();
  //   }
  // }
  // $res = array();
  // foreach($idf as $word => &$arrayFileCount) {
  //   foreach($arrayFileCount as $file => &$count) {
  //     if(array_key_exists($file, $res)) {
  //       $res[$file] += $count;
  //     } else {
  //       $res[$file] = $count;
  //     }
  //
  //   }
  // }
  //
  // arsort($res);
  // foreach($res as $file => $rtf) {
  //   $resultat[] = $file;
  //   echo "$file.....";
  //   echo $rtf;
  //   echo "<br/>";
  // }
  // return $resultat;
}
?>
