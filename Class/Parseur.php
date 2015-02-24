<?php

class Parseur
{
	private $_StopWords;

	public function __construct() 
	{
        
	}

    private function ParserStopWords()
    {
        if (!$fp = fopen("Ressources/stopwords.txt","r")) 
        {
            throw new Exception("Attribut Introuvable !", 1);
        }
        else 
        {
            while(!feof($fp)) 
            {
                $Ligne = fgets($fp,255);

            // On affiche la ligne
            echo $Ligne;

            // On stocke l'ensemble des lignes dans une variable
            $Fichier .= $Ligne;

            }
            fclose($fp);
        }
    }

	private function Attribut_Existe($name)
	{
        if (isset($this->$name))
        {
            return true;
        }
        else
        {
            throw new Exception("Attribut Introuvable !", 1);
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
