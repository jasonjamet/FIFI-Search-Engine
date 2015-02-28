<?php

class Parser implements Serializable
{
	private $_StopWords;
    private $_TimeIndexation;

	public function __construct()
	{
        $this->_StopWords = array();
        $this->_TimeIndexation = 0;
	}

    public function serialize() {

        $Tableau[] = $this->__get('_StopWords')->saveToString();
        $Tableau[] = $this->__get('_TimeIndexation');

        return serialize($Tableau);
    }

    public function unserialize($Deseriabiliser)
    {
        $Tableau = unserialize($Deseriabiliser);

        $this->__set("_StopWords", QuickHashStringIntHash::loadFromString($Tableau[0]));
        $this->__set("_TimeIndexation", $Tableau[1]);
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

        while (in_array($tab[0], $op))
        {
            array_shift($tab);
        }
        while (in_array($tab[count($tab)-1], $op))
        {
            array_pop($tab);
        }

        return $tab;
    }


    public function ParserRequest($Request)
    {
        $KeyWords = array("or", "and", "&&", "||", "not");
        $Req = trim(strtolower($Request));

        if (strlen($Req) < 3)
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
                $Request_New = array();
                foreach ($Tableau_Operateur_Word[0] as $Tableau)
                {
                    foreach ($Tableau as $value)
                    {
                        if (!isset($_StopWords[$value]))
                        {
                            $Request_New[] = $this->ParserStemmer($value);
                        }
                    }
                }

               $verifcation_words = $this->verifcation_words($Tableau_Request);
               if (!empty($verifcation_words))
                echo 'Can you try with: <a href="#" class="add_search">'.$verifcation_words.'</a> ?
                    <script>$(".add_search").on(\'click\',function() {$("#recherche-id").val(this.text);});</script>';
           
                $resultat = count(getArrayOfDocByArrayWord($Request_New));
                if ($resultat == 0)
                    echo '<p class="Counter_Doc">No results found for: "'.$Request.'"</p><br /><br />';

                else if ($resultat == 1)
                    echo '<p class="Counter_Doc">'.$resultat.' result has been found for: "'.$Request.'"</p><br /><br />';
                else
                    echo '<p class="Counter_Doc">'.$resultat.' results have been found for: "'.$Request.'"</p><br /><br />';
                echo '<div id="Liste_Doc"><ul>';
                foreach (getArrayOfDocByArrayWord($Request_New) as $doc => $num) {
                    echo '<li><a href="testjj2.php?doc='.$doc.'&word='.implode(",",$Request_New).'" target=_blank >'.$doc.'</a> '.$num.' fois trouvée <br/>
                    <span class="Contenu_Doc">  </span>
                    </li><br/>';
                }
                echo '</ul></div>';
            }
        }
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
                $NewRequest .= pspell_suggest($pspell_link, $word)[0];
                $compteur++;
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
        $KeyWords = array("or", "and", "&&", "||", "not");

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
