function getArrayOfDocByArrayWord($arrayWord, $page) {
  // global $bdd;
  //
  // $pagination = 10;
  //
  // $limit_start = ($page - 1) * $pagination;
  //
  //
  // $qMarks = str_repeat("AND id_document IN (	SELECT id_document
  //                                             FROM `position`
  //                                             WHERE id_word = ( 		SELECT id
  //                                                                   FROM word
  //                                                                   WHERE word = ? )
  //                                           )", count($arrayWord) - 1);
  //   $req = $bdd->prepare("  SELECT COUNT(*)
  //                           FROM (
  //                                   SELECT DISTINCT id_document
  //                                   FROM position
  //                                   WHERE id_word = (
  //                                                     SELECT id
  //                                                     FROM word
  //                                                     WHERE word = ?
  //                                                   )
  //                                   $qMarks
  //                           ) AS counter
  //   ");
  //   $req->execute($arrayWord);
  //   $nb_total=$req->fetch()[0];
  //   $req->closeCursor();
  //
  //
  // $response = $bdd->prepare("  SELECT (
  //                                       SELECT name
  //                                       FROM document
  //                                       WHERE id_document = document.id
  //                                     ) AS name, count(*) AS nombre
  //                              FROM (
  //                                       SELECT id_document
  //                                       FROM position
  //                                       WHERE id_word = (
  //                                                         SELECT word.id
  //                                                         FROM word
  //                                                         WHERE word = ?
  //                                                       )
  //                                                       $qMarks
  //                                   ) AS test
  //                               GROUP BY id_document
  //                               ORDER BY nombre DESC
  //                               LIMIT ".$limit_start.", ".$pagination."
  //
  //                           ");
  //
  // $response->execute($arrayWord);
  // $res = array();
  // while ($donnees = $response->fetch()) {
    //   $res[$donnees[0]] = $donnees[1];
    // }
    //
    // //Tri par count
    // //arsort($res);
    // $response->closeCursor();
    //
    //
    //
    // $nb_pages = ceil($nb_total/ $pagination);
    // return array($nb_total,$nb_pages, $res);
    //
    //
    // /*SELECT (SELECT name from document where id_document = document.id) as name, count(*) as nombre FROM (SELECT id_document FROM position WHERE id_word = (SELECT word.id  FROM word WHERE word = "korea")) as test group by id_document*/
    global $bdd;

    $pagination = 10;
    $limit_start = ($page - 1) * $pagination;
    $qMarks = str_repeat('?,', count($arrayWord) - 1) . '?';

    $response = $bdd->prepare("   SELECT word, document.name, count(position) AS counter
    FROM document, position, word
    WHERE word
    IN ($qMarks)
    AND id_document = document.id
    AND id_word = word.id
    GROUP BY document.name, word
    ORDER BY word.word ASC
    ");
    $response->execute($arrayWord);

    $idf = array();
    while ($donnees = $response->fetch()) {
      $idf[$donnees[0]][$donnees[1]] = $donnees[2];
    }
    $response->closeCursor();


    foreach($idf as $word => &$arrayFileCount) {
      $numberOfFile = count($arrayFileCount);
      foreach($arrayFileCount as $file => &$count) {
        $count = (1+log10($count))*log10(3102/$numberOfFile);
      }
    }
    $res = array();
    foreach($idf as $word => &$arrayFileCount) {
      foreach($arrayFileCount as $file => &$count) {
        if(array_key_exists($file, $res)) {
          $res[$file] += $count;
        } else {
          $res[$file] = $count;
        }

      }
    }

    arsort($res);
    $nb_total= count($res);
    $nb_pages = ceil($nb_total/ $pagination);

    $_SESSION["TabRes"] = serialize($res);
    var_dump($res);
    return array($nb_total, $nb_pages, $res);
  }


  SELECT *, SUM( log ) AS pagerank, name FROM
    (SELECT (1 + LOG10((SELECT count(*) from position where id_word = word.id and id_document = document.id)) * LOG10((SELECT count(*) from document) / (SELECT count(*) FROM position WHERE id_word = id_word))) AS log, document.name FROM document, position, word
		WHERE word IN ("north", "Korea")
		AND id_document = document.id
		AND id_word = word.id
		GROUP BY id_document, id_word
		ORDER BY word.word DESC) AS test
GROUP BY test.name
ORDER BY pagerank ASC



SELECT id_word, word,(frequence.nb_frequence * log_table.log) as pagerank
FROM (
    	(SELECT id_word, word, id_document,count(*) as nb_frequence
         FROM position,document,word
         WHERE id_document = document.id and id_word = word.id) as frequence,

    	(SELECT LOG(nb_document / nb_occurence) as log
         FROM ((select *, count(*) as nb_document
                from document) as document,

               (select count(id_document) as nb_occurence
                from position,word
                where position.id_word = word.id) as occurence)) as log_table

	)

WHERE word IN ("north", "korean")
AND id_document = document.id
AND id_word = word.id
GROUP BY id_document, id_word








SELECT *, (SELECT count(*) from position where id_word = word.id and id_document = document.id) as occurence, (select count(*) as nb_document from document) as document,(select count(id_document) nb_occurence from position,word where word.id = id_word) FROM document, position, word
WHERE word IN ("north", "Korea")
AND id_document = document.id
AND id_word = word.id
GROUP BY id_document, id_word





select count(*) as nb_frequence from position where id_document = 1 and id_word = 1
SELECT LOG(nb_document / nb_occurence) FROM ((select count(*) as nb_document from document) as document,(select count(id_document) nb_occurence from position,word where position.id_word = word.id and word="north") as occurence)


SELECT (frequence.nb_frequence * log_table.log) as pagerank FROM (
    (SELECT count(*) as nb_frequence FROM position,document,word WHERE id_document = document.id and id_word = word.id) as frequence,
	(SELECT LOG(nb_document / nb_occurence) as log FROM ((select count(*) as nb_document from document) as document,(select count(id_document) nb_occurence from position,word where position.id_word = word.id and word="north") as occurence)) as log_table)

























  SELECT PD,*
  FROM((SELECT word, document.name, count( position ) AS counter
  FROM document, position, word
  WHERE id_document IN (	SELECT id_document
  FROM `position`
  WHERE id_word = ( 		SELECT id
  FROM word
  WHERE word = "mari"
  )
  )
  AND id_document IN (	SELECT id_document
  FROM `position`
  WHERE id_word = ( 		SELECT id
  FROM word
  WHERE word = "Pierr"
  )
  )
  AND id_document = document.id
  AND id_word = word.id
  GROUP BY document.name, word
  ORDER BY word.word ASC) as popo
  INNER JOIN
  (SELECT word, document.name, count( position ) AS counter
  FROM document, position, word
  WHERE id_document IN (	SELECT id_document
  FROM `position`
  WHERE id_word = ( 		SELECT id
  FROM word
  WHERE word = "huitr"
  )
  )
  AND id_document IN (	SELECT id_document
  FROM `position`
  WHERE id_word = ( 		SELECT id
  FROM word
  WHERE word = "chocap"
  )
  )
  AND id_document = document.id
  AND id_word = word.id
  GROUP BY document.name, word
  ORDER BY word.word ASC)AS pipi)
  GROUP BY popo.name
  
