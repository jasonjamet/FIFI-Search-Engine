
<?php
$pspell_link = pspell_new("en");
    $spellchecked = "";
$words = $_GET['word'];
        if (pspell_check($pspell_link, $words))
        {
            $spellchecked .= $words." ";
        }
        else
        {
            $erroneous = "yes";
            $suggestions = pspell_suggest($pspell_link, $words);
            $spellchecked .= $suggestions[0]." ";
        }
   
    if($erroneous == "yes")
    {
        echo "Did you mean: <i>".$spellchecked."?";
    }
    else
    {
        echo $spellchecked . " is a valid word/phrase";
    }