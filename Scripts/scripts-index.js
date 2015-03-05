
$(".add_search").on('click',function() 
{
	$("#recherche-id").val(this.text);
});

$("#Envoi_Formulaire").on('reset',function() 
{
	$("#Informations_Submit").fadeOut("Slow");
});

$("#Envoi_Formulaire").on('submit',function() 
{
	var Recherche =  $("#recherche-id").val();
	$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/ajax-loader.gif' alt='Loading'/></p><p class='orange'>Searching...<br/>This can take few secondes.</p>");

	$.post( "cible.php", {recherche: Recherche}, function( data ) {

		if (data == 1)
		{
			$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>Your request should be at least 2 characters !</p>");
		}
		else if(data == 8)
		{
			$("#Informations_Submit").empty().fadeIn("Slow").html("<p><a href='Rapport/rapport.pdf' target='_blank'> <img src='Images/pdf.png' alt='PDF' width='60'/></a> <br/> Please click the icon to view the report</p>");

		}
		else if (data == 5)
		{
			$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>The system encountered an internal error !<br/> <br/> Please inform an administrator.</p>");
		}
		else if (data == 9)
		{
			$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>A request can't be made solely operator !<br/> <br/> Please change your request.</p>");
		}
		else if (data == 2)
		{
			swal({
			title: "New Indexing",
			text: "Are you sure? This action is irreversible !\nThis can take several secondes.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: '#DD6B55',
			confirmButtonText: 'Confirm',
			cancelButtonText: "Cancel",
			closeOnConfirm: true,
			closeOnCancel: true
			},
				function(isConfirm){
			    if (isConfirm){

			    	$("#Envoi_Formulaire").css("display", "none");
					$("#astuce").css("display", "none");
					$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/ajax-loader.gif' alt='Loading'/></p><p class='orange'>Indexing of documents...<br/>This can take several secondes.</p><p class='orange'><u>Thank you not to reload the page</u></p>");
					
					$.post("cible.php",{install: "true"},function(texte)
					{
						$("#Envoi_Formulaire").fadeIn("Slow");
						$("#astuce").fadeIn("Slow");

						if (texte == 3)
						{
							if ($("#recherche-id").val() == '#new_index#')
								$("#recherche-id").val("");

							$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/check.png' alt='Success'/></p><p class='green'>The system has completed indexing<br/><br/>You can now enter a query dice !</p>");
						}
						else
						{
							$("#Informations_Submit").empty().fadeIn("Slow").html("<p><img src='Images/erreur.png' alt='Erreur'/></p><p class='rouge'>The system encountered an internal error !<br/><br/>Please inform an adminstrator.</p>");
						}
					});
			    } 
			});
		}
		else
		{
			$("#Informations_Submit").empty().fadeIn("Slow").html(data);
		}
	});
	return false;
});

