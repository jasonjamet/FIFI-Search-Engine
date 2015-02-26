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

        $Tableau[] = serialize($this->__get('_StopWords'));
        $Tableau[] = $this->__get('_TimeIndexation');

        return serialize($Tableau);
    }
    
    public function unserialize($Deseriabiliser) 
    {
        $Tableau = unserialize($Deseriabiliser);

        $this->__set("_StopWords", unserialize($Tableau[0]));
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
            $this->_StopWords = array();

            while(!feof($fp)) 
            {
                $mot = fgets($fp,255);
                $this->_StopWords[] = trim($mot);
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
                echo "5";
            else if ($Req == '#new_index#')
                echo "2";
            else 
            {
                $Tableau_Request = $this->TrimValue($Req, $KeyWords);
                $Tableau_Operateur_Word = $this->ParserRequestKeyWords($Tableau_Request);
                foreach ($Tableau_Operateur_Word[0] as $Tableau) 
                {
                    $Request_New = "";
                    foreach ($Tableau as $value) 
                    {
                        if (!in_array($value, $this->__get('_StopWords')))
                        {
                            $Request_New .= $this->ParserStemmer($value);
                            // var_dump($this->__get('_StopWords'));
                        }
                    }
                }
                print_r($Tableau_Operateur_Word[0]);
                print_r($Tableau_Operateur_Word[1]);
            }
        }        
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
