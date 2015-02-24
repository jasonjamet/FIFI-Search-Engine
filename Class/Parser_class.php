<?php

class Parser
{
	private $_StopWords;

	public function __construct() 
	{
        $this->ParserStopWords();
	}

    private function ParserStopWords()
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

    public function ParserRequest($Request)
    {
        if (strlen(trim($Request)) < 3) 
        {
            throw new Exception("Invalide Request", 1);
        }
        else
        {
            $Tableau_Request = explode(" ", strtolower($Request));
            $Tableau_Operateur_Word = $this->ParserRequestKeyWords($Tableau_Request);

            foreach ($Tableau_Operateur_Word[0] as $Tableau) {
                $Request_New = "";
                foreach ($Tableau as $value) {
                    if (!in_array($value, $this->__get('_StopWords'))) {
                            $Request_New .= $this->ParserStemmer($value);
                        }
                }
            }
        }
        print_r($Tableau_Operateur_Word[0]);
        print_r($Tableau_Operateur_Word[1]);
        echo "YES";
    }


    private function ParserRequestKeyWords($Tableau_Request)
    {
        $KeyWords = array("or", "and", "&&", "||", "*");

        $SousPhrase = array();
        $Operateur = array();

        $tmp = array();
        foreach ($Tableau_Request as $value) {
            if (in_array($value, $KeyWords)) {
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
}
?>	
