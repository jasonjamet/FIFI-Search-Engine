<?php

class Dictionary {
  private $_dictionary;

  public function __construct() {
    $this->ParserBuildIndex();
  }

  private function ParserBuildIndex() {
    set_time_limit (0);
    $_SESSION["docCount"]=0;
    $this->_dictionary = array();
    $currentDoc;
    $currentWordInDoc=0;

    // $dirname = 'Ressources/AP/';
    // $dir = opendir($dirname);
    // while($file = readdir($dir)) {
    //   if($file != '.' && $file != '..' && !is_dir($dirname.$file)) {
    //     if (!$fp = fopen($dirname.$file,"r")) {
    //       throw new Exception("Attribut Introuvable !", 1);
    //     }
    $fp = fopen("Ressources/AP/AP891217","r");
        //else {
          while(!feof($fp)) {
            $line = fgets($fp);
            $arrayWord = explode(" ", $line);
            if($arrayWord[0]=="<DOCNO>") {
              $_SESSION["docCount"]++;
              $currentDoc = array_splice($arrayWord, 1, -1)[0];
              $currentWordInDoc=0;
            }

            if(trim($line)=="<TEXT>") {

              $line = fgets($fp);
              $arrayWord = explode(" ", $line);
              while(trim($arrayWord[0])!="</TEXT>") {
                foreach ($arrayWord as $word) {
                  $currentWordInDoc++;
                  $word = preg_replace('#[^A-Za-z0-9]+#', '', $word);
                  if(!empty(trim($word))) {
                    $this->addWordToDictionary($word, $currentDoc, $currentWordInDoc);
                  }
                }
                $arrayWord = explode(" ", $line);
              }
            }
          }
          fclose($fp);

        //}


      //}
    //}
    //closedir($dir);
    @$shm_dico = shmop_open(ftok("Class/Dictionary_class.php",'c'), "w", 0666, 0);
    if (!empty($shm_dico)) {
      $shm_bytes_written = shmop_write($shm_dico, base64_encode(serialize($this->_dictionary)), 0);
    } else {
      $shm_dico = shmop_open(ftok("Class/Dictionary_class.php",'c'), "c", 0644, 300000);
      $shm_bytes_written = shmop_write($shm_dico, serialize($this->_dictionary), 0);
    }
    shmop_close($shm_dico);
    return 0;
  }


  private function readDictionaryFromMemory() {
    $dico = new Dictionary;
    @$shm_dico = shmop_open(ftok("Class/Dictionary_class.php",'c'), "a", 0666, 0);
    //shmop_delete($shm_dico);
    if (!empty($shm_dico)) {
      $shm_size = shmop_size($shm_dico);
      $dico->_dictionary=base64_decode(unserialize(shmop_read($shm_dico, 0, $shm_size)));
    } else {
      $shm_dico = shmop_open(ftok("Class/Dictionary_class.php",'c'), "c", 0666, 300000);
      $this->ParserBuildIndex();
    }
    shmop_close($shm_dico);
  }




  private function addWordToDictionary($word, $fileName, $position) {
    foreach ($this->_dictionary as &$objWordFromDictionary) {
      foreach ($objWordFromDictionary as $keyWordFromDictionary => &$fileNameAndPosFromDictionary) {
        if($keyWordFromDictionary == $word) {
          foreach ($fileNameAndPosFromDictionary as $keyFileName => &$PositionFromDictionary) {

            if($keyFileName == $fileName) {
              $PositionFromDictionary[] = $position;
              return true;
            }
          }
          $fileNameAndPosFromDictionary[$fileName]=array($position);
          return true;
        }
      }
    }
    $this->_dictionary[]=array($word => array($fileName => array($position)));
  }

  public function findWord($word){
    //echo "Mot: $word <br/>";
    foreach ($this->_dictionary as $objWordFromDictionary) {
      foreach ($objWordFromDictionary as $keyWordFromDictionary => $fileNameAndPosFromDictionary) {
          if($keyWordFromDictionary == $word) {
            foreach ($fileNameAndPosFromDictionary as $keyFileName => $PositionFromDictionary) {
              //echo ".....Fichier: $keyFileName <br/>";
              foreach ($PositionFromDictionary as $Position) {
                //echo "............Position: $Position";
                //echo "<br/>";
              }
            }

        }
      }
    }

  }
  public function getArrayPosByWordAndDoc($word, $doc){
    $arrayPos = array();
    foreach ($this->_dictionary as $objWordFromDictionary) {
      foreach ($objWordFromDictionary as $keyWordFromDictionary => $fileNameAndPosFromDictionary) {
        if($keyWordFromDictionary == $word) {
          foreach ($fileNameAndPosFromDictionary as $keyFileName => $PositionFromDictionary) {
            if($keyFileName == $doc) {
              foreach ($PositionFromDictionary as $Position) {
                $arrayPos[]=$Position;
              }
            }
          }
        }
      }
    }
    return $arrayPos;

  }

  public function getArrayOfDocByWord($word){
    $this->readDictionaryFromMemory();
    $arrayDoc = array();
    foreach ($this->_dictionary as $objWordFromDictionary) {
      foreach ($objWordFromDictionary as $keyWordFromDictionary => $fileNameAndPosFromDictionary) {
        if($keyWordFromDictionary == $word) {
          foreach ($fileNameAndPosFromDictionary as $keyFileName => $PositionFromDictionary) {
            $arrayDoc[]=$keyFileName;
          }
        }
      }
    }
    return $arrayDoc;
  }
  public function getResultDocByWordAndDoc($word, $doc){
    $this->readDictionaryFromMemory();
    $file = explode("-", $doc)[0];
    $counterCurrentWordInDoc=0;
    $arrayPos=$this->getArrayPosByWordAndDoc($word, $doc);

    $fp = fopen("$file","r");
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
                $counterCurrentWordInDoc++;
              }
              $line = fgets($fp);
              $arrayWord = explode(" ", $line);
            }

        }
      }

    }
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

  public function __set($name, $value) {
    try {
      if ($this->Attribut_Existe($name)) {
        $this->$name = $value;
      }
    }
    catch(Exception $e) {
      return $e->getMessage();
    }
  }
}


// $dico = new Dictionary;
//$dico->getArrayOfDocByWord("the");
// $dico->getResultDocByWordAndDoc("the", "AP890101-0002");
?>
