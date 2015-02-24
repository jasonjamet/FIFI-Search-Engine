<?php

class Dictionary_class {
  private $_dictionary;

  public function __construct() {
    $this->ParserBuildIndex();
  }

  private function ParserBuildIndex() {

    $this->_dictionary = array();
    $currentDoc;
    $currentWordInDoc=0;


    // $dirname = '../Ressources/AP/';
    // $dir = opendir($dirname);
    // while($file = readdir($dir)) {
    //   if($file != '.' && $file != '..' && !is_dir($dirname.$file)) {
    //     if (!$fp = fopen($dirname.$file,"r")) {
    //       throw new Exception("Attribut Introuvable !", 1);
    //     }
    $fp = fopen("../AP890101","r");
        //else {
          while(!feof($fp)) {
            $line = fgets($fp);
            $arrayWord = explode(" ", $line);
            if($arrayWord[0]=="<DOCNO>") {
              $currentDoc = array_splice($arrayWord, 1, -1)[0];
            }

            if(trim($line)=="<TEXT>") {

              while(trim($line)!="</TEXT>") {
                foreach ($arrayWord as $word) {
                  if($word!="" || $word!=" ") {
                    $this->addWordToDictionary(stem_english($word), $currentDoc, $currentWordInDoc);
                    $currentWordInDoc++;
                  }
                }

                $line = fgets($fp);
                $arrayWord = explode(" ", $line);

              }
            }



            // foreach ($arrayWord as &$word) {
            //
            //   if($word=="<DOC>") {
            //     $currentDoc=
            //   }
            // }
            // //echo var_dump($word);
            // if($line=="<DOC>") {
            //   _ArrayWords[]
            // }
            // if($word[0]=="<DOCNO>") {
            //   currentDoc=$word[1];
            // } else if {
            //   preg_match('</^>', $word[], $matches, PREG_OFFSET_CAPTURE);
            //   print_r($matches);
            // }
          }
          fclose($fp);

        //}
        var_dump($this->_dictionary);
        return 0;
      //}
    //}
    //closedir($dir);


  }

  private function addWordToDictionary($word, $fileName, $position) {
    foreach ($this->_dictionary as $objWordFromDictionary) {
      echo "ok";
      foreach ($objWordFromDictionary as $keyWordFromDictionary => $fileNameAndPosFromDictionary) {
        if($keyWordFromDictionary == $word) {
          foreach ($fileNameAndPosFromDictionary as $keyFileName => $PositionFromDictionary) {
            if($keyFileName == $fileName) {
              $PositionFromDictionary[] = $position;

              return true;
            }
          }
          $fileNameAndPosFromDictionary[]=array($fileName, array($position));
          return true;
        }
      }
    }
    $this->_dictionary[]=array($word => array($fileName => array($position)));
    //var_dump($this->_dictionary);
  }

  private function Attribut_Existe($name) {
    if (isset($this->$name)) {
      return true;
    }
    else {
      throw new Exception("Attribut Introuvable !", 1);
    }
  }

  public function __get($name) {
    try {
      if ($this->Attribut_Existe($name)) {
        return $this->$name;
      }
    }
    catch(Exception $e) {
      return $e->getMessage();
    }
  }
}
new Dictionary_class;
?>
