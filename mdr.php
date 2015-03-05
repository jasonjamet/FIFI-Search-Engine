<?php
require_once("Class/Parser_class.php");


  /*$Tableau = array(1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23);

  $Response_Request = new QuickHashIntStringHash(256);

  $String_Request = "";
  foreach ($Tableau as $value) {
    if ($compte++ < 10)
       $String_Request.= "";
  }
  //$Response_Request */

  $Parser = new Parser();
  $Parser->ParserStopWords();
  $Parser->buildIndex();


jean pierre paul and marie or john and patrick and claude

	$TableauWord[] = array("Jean", "Pierre", "Paul");
	$TableauWord[] = array("Marie");
	$TableauWord[] = array("John");
	$TableauWord[] = array("Patrick");
	$TableauWord[] = array("Claude");
	$TableauWordOperator[0] = $TableauWord;
	$TableauWordOperator[1] = array("and", "or", "and", "and");

?>