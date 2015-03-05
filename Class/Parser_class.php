<?php


class Parser implements Serializable
{
	private $_StopWords;

	public function __construct()
	{
        $this->_StopWords = array();
	}

    public function serialize() {
        return $this->__get('_StopWords')->saveToString();
    }

    public function unserialize($Deseriabiliser)
    {
        $this->__set("_StopWords", QuickHashStringIntHash::loadFromString($Deseriabiliser));
    }

    public function ParserStopWords()
    {
        if (!$fp = fopen("Ressources/stopwords.txt","r"))
        {
            throw new Exception("Attribut Not Found !", 1);
        }
        else
        {
            $this->_StopWords = new QuickHashStringIntHash(64);

            while(!feof($fp))
            {
                $mot = fgets($fp,255);
                $this->_StopWords[trim($mot)] = 0;
            }
            fclose($fp);
        }
    }

    public function TrimValue($tab, $op)
    {
        $tab = explode(" ", $tab);
   
        while (count($tab) > 0 && in_array($tab[0], $op))
        {
            array_shift($tab);
        }
        while (count($tab) > 0 && in_array($tab[count($tab)-1], $op))
        {
            array_pop($tab);
        }
        
        return $tab;
    }


    public function ParserRequest($Request, $page)
    {
        $KeyWords = array("or", "and", "not");
        $Req = trim(strtolower($Request));

        if (strlen($Req) < 2)
        {
            throw new Exception("Invalide Request", 2);
        }
        else
        {
            if ($Req == '#report#')
                echo "8";
            else if ($Req == '#new_index#')
                echo "2";
            else
            {
                $Tableau_Request = $this->TrimValue($Req, $KeyWords);
                $Tableau_Operateur_Word = $this->ParserRequestKeyWords($Tableau_Request);
                // var_dump($Tableau_Operateur_Word);
                
                 // var_dump($Tableau_Operateur_Word[0]);

                $Request_New = array();
                $Tab_StopWord = array();
                foreach ($Tableau_Operateur_Word[0] as $Tableau)
                {
                    foreach ($Tableau as $value)
                    {
                        if (!isset($this->_StopWords[$value]))
                        {
                            $Request_New[] = $this->ParserStemmer($value);
                        }
                        else
                            $Tab_StopWord[] = $this->ParserStemmer($value);
                    }
                }
                
                $Request_New = str_replace(",", '', $Request_New);

               $verifcation_words = $this->verifcation_words($Tableau_Request);
               if (!empty($verifcation_words))
                echo '<div id="New_Spelling"><span class="Try_Spelling"> Try with this spelling: </span><a href="#" class="add_search">'.$verifcation_words.'</a></div>
                    <script>$(".add_search").on(\'click\',function() {$("#recherche-id").val(this.text);});</script><div class="clear"></div>';;

                if(count($Request_New) > 0 )
                {
                    $TableauArrayOfDocByArrayWord = getArrayOfDocByArrayWord($Request_New, $page);
                    $nb_result = $TableauArrayOfDocByArrayWord[0];
                
                
                    echo '<div id="Liste_Doc"><ul>';
                    if ($nb_result == 0)
                        echo '<p class="Result_NoFound">No documents correspond to the specified search words<br /> <span class="No_Found"> ('. $Req .')  </span> </p><br /><br />';

                    else if ($nb_result == 1)
                        echo '<p class="Counter_Doc">'.$nb_result.' result has been found</p><br /><br />';
                    else
                        echo '<p class="Counter_Doc">'.$nb_result.' results have been found</p><br /><br />';

                    
                    foreach ($TableauArrayOfDocByArrayWord[2] as $doc => $num) {
                        if(isset($Tab_StopWord))
                        {
                            echo '<li><a href="document.php?doc='.$doc.'&word='.implode(",",$Request_New).'&stopWord='.implode(",",$Tab_StopWord).'" target=_blank >'.$doc.'</a> ('.$num.')<br/>
                            <span class="green">document.php?doc='.$doc.'&word='.implode(",",$Request_New).'</span><br/>
                            <span class="Contenu_Doc">'.getMinimalDocWithResult($TableauArrayOfDocByArrayWord[2], $doc).'</span>
                            </li><br/>';
                        }
                        else
                        {
                            echo '<li><a href="document.php?doc='.$doc.'&word='.implode(",",$Request_New).'" target=_blank >'.$doc.'</a> ('.$num.')<br/>
                            <span class="green">document.php?doc='.$doc.'&word='.implode(",",$Request_New).'</span><br/>
                            <span class="Contenu_Doc">'.getMinimalDocWithResult($TableauArrayOfDocByArrayWord[2], $doc).'</span>
                            </li><br/>';
                        }
                    }
                    echo '</ul></div>';

                    echo '<div class="clear"></div>';
                    echo '<p class="pagination">' . $this->pagination($page, $TableauArrayOfDocByArrayWord[1]) . '</p>';

                    echo '<script>
                    $("#suivant").on( "click", function() {
                    $("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src=\'Images/ajax-loader.gif\' alt=\'Loading\'/></p><p class=\'orange\'>Searching...<br/>This can take few secondes.</p>");
                    $.post("cible.php",{recherche: "'.$Request.'", page: '.($page + 1).'},function(texte){
                        $("#Informations_Submit").empty().append(texte);
                    });
                    return false;
                    });

                    $("#precedent").on( "click", function() {
                    $("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src=\'Images/ajax-loader.gif\' alt=\'Loading\'/></p><p class=\'orange\'>Searching...<br/>This can take few secondes.</p>");
                    $.post("cible.php",{recherche: "'.$Request.'", page: '.($page - 1).'},function(texte){
                    $("#Informations_Submit").empty().append(texte);
                    });
                    return false;
                    });


                    $(".Numero").on( "click", function() {
                    $("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src=\'Images/ajax-loader.gif\' alt=\'Loading\'/></p><p class=\'orange\'>Searching...<br/>This can take few secondes.</p>");
                    $.post("cible.php",{recherche: "'.$Request.'", page: $(this).text()},function(texte){
                    $("#Informations_Submit").empty().append(texte);
                    });
                    return false;
                    });
                    </script>';
                }
                else {
                    throw new Exception("Error Word in StopWord", 3);
                    
                }
            }
        }
    }

private function pagination($current_page, $nb_pages, $mode="", $nom_module="", $link='-%d', $around=3, $firstlast=1)
{
    $pagination = '';
    $link = preg_replace('`%([^d])`', '%%$1', $link);
    $link = $nom_module.''.$link;
    if ( !preg_match('`(?<!%)%d`', $link) ) $link .= '%d';
    if ( $nb_pages > 1 ) {

        // Lien précédent
        if ( $current_page > 1 )
        {
            if (empty($mode))
            {
            $pagination .= '<a id="precedent" class="prevnext" href="'.sprintf($link, $current_page-1).'" title="Page précédente">&laquo; Précédent</a>';
            }
            else
            {
            $pagination .= '<a id="precedent" class="prevnext" href="'.sprintf($link, $current_page-1).'-'.$mode.'" title="Page précédente">&laquo; Précédent</a>';
            }
        }
        else
            $pagination .= '<span class="prevnext disabled">&laquo; Précédent</span>';

        // Lien(s) début
        for ( $i=1 ; $i<=$firstlast ; $i++ ) {
            $pagination .= ' ';
            if (empty($mode))
            {
            $pagination .= ($current_page==$i) ? '<span class="current">'.$i.'</span>' : '<a class="Numero" href="'.sprintf($link, $i).'">'.$i.'</a>';
            }
            else
            {
            $pagination .= ($current_page==$i) ? '<span class="current">'.$i.'</span>' : '<a class="Numero" href="'.sprintf($link, $i).'-'.$mode.'">'.$i.'</a>';
            }
        }

        // ... après pages début ?
        if ( ($current_page-$around) > $firstlast+1 )
            $pagination .= ' &hellip;';

        // On boucle autour de la page courante
        $start = ($current_page-$around)>$firstlast ? $current_page-$around : $firstlast+1;
        $end = ($current_page+$around)<=($nb_pages-$firstlast) ? $current_page+$around : $nb_pages-$firstlast;
        for ( $i=$start ; $i<=$end ; $i++ ) {
            $pagination .= ' ';
            if ( $i==$current_page )
                $pagination .= '<span class="current">'.$i.'</span>';
            else
            {
                if (empty($mode))
                {
                $pagination .= '<a class="Numero" href="'.sprintf($link, $i).'">'.$i.'</a>';
                }
                else
                {
                $pagination .= '<a class="Numero" href="'.sprintf($link, $i).'-'.$mode.'">'.$i.'</a>';
                }
            }
        }

        // ... avant page nb_pages ?
        if ( ($current_page+$around) < $nb_pages-$firstlast )
            $pagination .= ' &hellip;';

        // Lien(s) fin
        $start = $nb_pages-$firstlast+1;
        if( $start <= $firstlast ) $start = $firstlast+1;
        for ( $i=$start ; $i<=$nb_pages ; $i++ ) {
            $pagination .= ' ';
            if (empty($mode))
            {
            $pagination .= ($current_page==$i) ? '<span class="current">'.$i.'</span>' : '<a class="Numero" href="'.sprintf($link, $i).'">'.$i.'</a>';
            }
            else
            {
            $pagination .= ($current_page==$i) ? '<span class="current">'.$i.'</span>' : '<a class="Numero" href="'.sprintf($link, $i).'-'.$mode.'">'.$i.'</a>';
            }
        }

        // Lien suivant
        if ( $current_page < $nb_pages )
        {
            if (empty($mode))
            {
            $pagination .= ' <a id="suivant" class="prevnext" href="'.sprintf($link, ($current_page+1)).'" title="Page suivante">Suivant &raquo;</a>';
            }
            else
            {
            $pagination .= ' <a id="suivant" class="prevnext" href="'.sprintf($link, ($current_page+1)).'-'.$mode.'" title="Page suivante">Suivant &raquo;</a>';
            }
        }
        else
            $pagination .= ' <span class="prevnext disabled">Suivant &raquo;</span>';
    }
    return $pagination;
}


public function buildIndex()
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

                $Requete_position.= "('$key_word_search', '$id_key_document', '$position'),";
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

  // Insertion Requete
  $Requete_position = substr($Requete_position,0,-1).";";
  $Requete_doc = substr($Requete_doc,0,-1).";";
  $Requete_word = substr($Requete_word,0,-1).";";

  $bdd->exec($Requete_doc);
  $bdd->exec($Requete_word);
  $bdd->exec($Requete_position);

  closedir($dir);
}


    private function is_correct_word($word,$pspell_link)
    {
        if (pspell_check($pspell_link, $word))
            return true;
        else
            return false;
    }

    private function verifcation_words($words)
    {
        $pspell_link = pspell_new("en");
        $NewRequest = "";
        $compteur = 0;
        foreach ($words as $word) {
            if (!$this->is_correct_word($word,$pspell_link))
            {
                @$sugg = pspell_suggest($pspell_link, $word)[0];
                if (strtolower($sugg) != $word)
                    $compteur++;

                $NewRequest .= $sugg;
            }
            else
                $NewRequest .= $word;

            $NewRequest .= " ";
        }

        if ($compteur == 0)
            return "";
        else
            return substr($NewRequest,0,-1);
    }

    private function ParserRequestKeyWords($Tableau_Request)
    {
        $KeyWords = array("or", "and", "not");

        $SousPhrase = array();
        $Operateur = array();

        $tmp = array();

        foreach ($Tableau_Request as $value)
        {
            if (in_array($value, $KeyWords))
            {
                $SousPhrase[] = $tmp;
                $Operateur[] = $value;
                $tmp = array();
            }
            else
            {
                $tmp[] = $value;
            }
        }
        $SousPhrase[] = $tmp;

        return array($SousPhrase, $Operateur);
    }

    private function ParserStemmer($word)
    {
       return stem_english($word);
    }

    public function isIndex()
    {
        if (!file_exists("Ressources/index.txt"))
            return true;
        else
            return false;
    }

	private function Attribut_Existe($name)
	{
        if (isset($this->$name))
        {
            return true;
        }
        else
        {
            throw new Exception("Attribut Not Found !", 1);
        }
    }

	public function __get($name)
    {
        try
        {
            if ($this->Attribut_Existe($name))
            {
				return $this->$name;
            }
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }

    public function __set($name, $value)
    {
        try
        {
            if ($this->Attribut_Existe($name))
            {
                $this->$name = $value;
            }
        }
        catch(Exception $e)
        {
            return $e->getMessage();
        }
    }
}
?>
