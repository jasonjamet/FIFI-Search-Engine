<?php 
function pagination($current_page, $nb_pages, $mode, $nom_module, $link='-%d', $around=3, $firstlast=1)
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




function AfficherNouveaux($categorie, $requete, $region, $departement, $page)
{
global $bdd;
global $profil;

$pagination = 12;
// Numéro du 1er enregistrement à lire
$limit_start = ($page - 1) * $pagination;

if ($categorie == 0)
{
	$requete  = "Statue IN ('Couple','Couple_Gay','Couple_Lesbien','Couple_H_bi','Couple_F_bi','Couple_2_bi','Femme','Lesbienne', 'Femme_bi','Homme','Gay', 'Homme_bi','Trav','Trans')";
}

if ($region == 0)
{
	$req = $bdd->prepare('SELECT COUNT(Id) from users WHERE (Valide = 2) and (Id <> ?) AND (Level <> 3) and '.$requete.' LIMIT 120');
				$req->execute(array($profil['Id']));
				$resultat = $req->fetch();
				$req->closeCursor();
				$nb_total = $resultat[0];
				
	$req2 = $bdd->prepare('SELECT Id, Date_Inscription as date FROM users WHERE (Valide = 2) and (Id <> ?) AND (Level <> 3) and '.$requete.' order by date DESC LIMIT '.$limit_start.', '.$pagination.'');
	$req2->execute(array($profil['Id']));
}
else if ($departement == 0)
{
	$req = $bdd->prepare('SELECT COUNT(Id) from users WHERE (Valide = 2) and (Id <> ?) AND (Level <> 3) and '.$requete.' and Region = ? LIMIT 120');
				$req->execute(array($profil['Id'],$region));
				$resultat = $req->fetch();
				$req->closeCursor();
				$nb_total = $resultat[0];
				
	if ($nb_total < 120)
	{
		$req = $bdd->prepare('SELECT COUNT(Id) from users WHERE (Valide = 2) and (Id <> ?) AND (Level <> 3) and '.$requete.' LIMIT 120');
		$req->execute(array($profil['Id']));
		$resultat = $req->fetch();
		$req->closeCursor();
		$nb_total = $resultat[0];
	}
	
		$req2 = $bdd->prepare('(SELECT Id, Date_Inscription as date, Region FROM users WHERE '.$requete.' AND (Valide = 2) AND (Level <> 3) AND (Region = ?) AND (Id <> ?) order By Region, date DESC) UNION (SELECT Id, Date_Inscription as date, Region FROM users WHERE '.$requete.' AND (Region <> ?) AND (Valide = 2) AND (Level <> 3) AND (Id <> ?) order By date DESC) LIMIT '.$limit_start.', '.$pagination.'');
		$req2->execute(array($region,$profil['Id'],$region,$profil['Id']));	
}
else
{
	$req = $bdd->prepare('SELECT COUNT(Id) from users WHERE (Valide = 2) and (Id <> ?) AND (Level <> 3) and '.$requete.' and Region = ? and departement = ? LIMIT 120');
	$req->execute(array($profil['Id'],$region, $departement));
	$resultat = $req->fetch();
	$req->closeCursor();
	$nb_total = $resultat[0];
			
if ($nb_total < 120)
{
	$req = $bdd->prepare('SELECT COUNT(Id) from users WHERE (Valide = 2) and (Id <> ?) AND (Level <> 3) and '.$requete.' and region = ? LIMIT 120');
	$req->execute(array($profil['Id']));
	$resultat = $req->fetch();
	$req->closeCursor();
	$nb_total = $resultat[0];
			
	if ($nb_total < 120)
	{
		$req = $bdd->prepare('SELECT COUNT(Id) from users WHERE (Valide = 2) and (Id <> ?) AND (Level <> 3) LIMIT 120');
		$req->execute(array($profil['Id']));
		$resultat = $req->fetch();
		$req->closeCursor();
		$nb_total = $resultat[0];
	}
}
			
$req2 = $bdd->prepare('(SELECT Id, Date_Inscription as date, Region, Departement FROM users WHERE '.$requete.' AND (Valide = 2) AND (Region = ?) AND (Level <> 3) AND (Departement = ?) AND (Id <> ?) order By Departement, Region, date DESC) UNION (SELECT Id, Date_Inscription as date, Region, Departement FROM users WHERE '.$requete.' AND (Region = ?) AND (Level <> 3) AND (Departement <> ?) AND (Valide = 2) AND (Id <> ?) order By Region, date DESC) UNION (SELECT Id, Date_Inscription as date, Region, Departement FROM users WHERE '.$requete.' AND (Level <> 3) AND (Region <> ?) AND (Departement <> ?) AND (Valide = 2) AND (Id <> ?) order by date DESC) LIMIT '.$limit_start.', '.$pagination.'');
$req2->execute(array($region,$departement,$profil['Id'],$region,$departement,$profil['Id'],$region,$departement,$profil['Id']));
}



echo '<div id="affiche_resultat">';
	while ($resultat2 = $req2->fetch())
	{
	$destinataire = InformationsProfil($resultat2['Id']);
	echo '
		<a href="Profil~'.$destinataire['Pseudo'].'">
	<div class="resultat">
   <div class="resultatphoto '.CouleurVignette($destinataire['Id']).'">'.AfficherLogoVipVignette($destinataire['Id']).''.AdresseProfil($destinataire['Id']).''.AfficherLogoDisponible ($destinataire['Id']).''.AfficherLogoStatut ($destinataire['Id']).'</div>
    <div class="resultatpseudo"><div class="resultattrois2">'.CouleurPseudo ($destinataire['Id'], $destinataire['Pseudo']).'</div><div class="resultattrois3">('.$destinataire['Departement'].')</div></div>
    <div class="resultatrecherches">'.AfficherRechercheStatut($destinataire['Id']).'</div> 
    <div class="resultattrois"><div class="resultattrois2">Inscrit: </div><div class="resultattrois3">'.date("d/m/Y", $resultat2['date']).'</div></div>
	</div></a>';
	}
echo '</div>';

$req2->closeCursor();

// Fonction Afficher le systéme de page
	$nb_pages = ceil($nb_total/ $pagination);	
	echo '<div class="clear"></div><center><br/><p class="pagination centre">' . pagination($page,$nb_pages) . '</p></center>';
	
echo '<script>
$("#suivant").on( "click", function() {
$.post("fonctions/contenu/AfficherNouveau.php",{page: '.($page+1).', id_region: $("#Region").val(), id_departement:$("#Departement").val(), Couple: $("#Couple").attr("checked"), Homme: $("#Homme").attr("checked"), Femme: $("#Femme").attr("checked"), Trans: $("#Trans").attr("checked"), Trav: $("#Trav").attr("checked")},function(texte){
$("#affiche").empty().append(texte);
});
return false;
});

$("#precedent").on( "click", function() {
$.post("fonctions/contenu/AfficherNouveau.php",{page: '.($page-1).', id_region: $("#Region").val(), id_departement:$("#Departement").val(), Couple: $("#Couple").attr("checked"), Homme: $("#Homme").attr("checked"), Femme: $("#Femme").attr("checked"), Trans: $("#Trans").attr("checked"), Trav: $("#Trav").attr("checked")},function(texte){
$("#affiche").empty().append(texte);
});
return false;
});


$(".Numero").on( "click", function() {
$.post("fonctions/contenu/AfficherNouveau.php",{page: $(this).text(), id_region: $("#Region").val(), id_departement:$("#Departement").val(), Couple: $("#Couple").attr("checked"), Homme: $("#Homme").attr("checked"), Femme: $("#Femme").attr("checked"), Trans: $("#Trans").attr("checked"), Trav: $("#Trav").attr("checked")},function(texte){
$("#affiche").empty().append(texte);
});
return false;
});
</script>';
}
?>