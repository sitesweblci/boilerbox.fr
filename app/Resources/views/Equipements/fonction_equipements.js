<script 'type=text/javascript'>
// Tableau des équipements selectionnés
// il permet de ne pas réafficher les équipements en doublon lors de la modification du select site
var tabDesEquipementsSelectionnes = [];

$(document).ready(function() 
{
        /******************************         READY POUR LES EQUIPEMENTS        ***************************************/
        $('#select_equipement').change(function()
        {
            var html_nouvelles_options = '';
            {% for e_siteBA in es_sitesBA %}
                // On affiche les équipements du site selectionné ou tous les équipements si aucun site n'est sélectionné
                if (("{{ e_siteBA.id }}" == $(this).val()) || ($(this).val() === ''))
                {
                    {% for e_equipement in e_siteBA.equipementBATickets %}
                        // On ajout l'équipement à la liste des équipements selectionnable si celui ci n'est pas déjà sélectionné (cad présent dans le tableau tabDesEquipementsSelectionnes)
                        if (tabDesEquipementsSelectionnes.indexOf("{{ e_equipement.id }}") == -1)
                        {
                            // Création du contenu html des Equipements
                            html_nouvelles_options += " \
                                <div id='parent_div_equipement_{{ e_equipement.id }}'> \
                                    <div id='div_equipement_{{ e_equipement.id }}'> \
                                        {#<span style='display: inline-block; width:15px; line-height:11px; padding-bottom: 3px; text-align:center; cursor:pointer;' onClick=\"supprimeEquipement('{{ e_equipement.id  }}');\">x</span> \#}
                                        <span style='display:inline-block; width:30px;'> \
                                            <input    type='checkbox' \
                                                id='equipement_{{ e_equipement.id }}' \
                                                name='equipement_{{ e_equipement.id }}' \
                                                value='{{ e_equipement.id }}' \
                                                style='display:inline-block; border:2px solid gray; cursor:pointer;' \
                                                onClick=\"deplaceEquipement('{{ e_equipement.id }}', '{{ e_equipement.denomination }}', '{{ e_equipement.numeroDeSerie }}')\" \
                                            />\
                                        </span><label style='display:inline-block; cursor:pointer;' for='equipement_{{ e_equipement.id }}'><span style='display:inline-block; width:60px;'>({{ e_equipement.numeroDeSerie }})</span><span style='display:inline-block; width:60px;'>{{ e_equipement.anneeDeConstruction | date('d/m/y') }}</span><span style='display:inline-block; width:150px;'>{{ e_equipement.denomination }}</span><span style='display:inline-block; width:150px;'>{{ e_equipement.autreDenomination }}</span><span style='display:inline-block; width:200px;'>{{ e_equipement.siteBA.intitule }}</span></label>\
                                    </div>\
                                </div>";
                        } else {
                            // Si l'équipement est déjà sélectionné, on n'affiche que le div du paretn pour pouvoir replacer l'équipement en cas de dé sélection de celui ci
                            html_nouvelles_options += "<div id='parent_div_equipement_{{ e_equipement.id }}'></div>";
                        }
                    {% endfor %}
                }
            {% endfor %}
            $('#insert_equipement').html(html_nouvelles_options);

            // On effectue la recherche sur les équipements nouvellement affichés
            $('#recherche_equipement').trigger('keyup');
        });

        // Fonction de recherche dans la liste des équipements
        $('#recherche_equipement').keyup(function()
        {
            $('#liste_des_equipements span').each(function()
            {
                $('#insert_equipement > div').each(function()
                {
                    if ($('#recherche_equipement').val() === '')
                    {
                        $(this).removeClass('cacher');
                    } else {
                        if (! $(this).is(':contains("' + $('#recherche_equipement').val() + '")'))
                        {
                            $(this).addClass('cacher');
                        }else{
                            $(this).removeClass('cacher');
                        }
                    }
                });
            });
        });
        // On lance le raffraichissement de la popup Equipement
        refreshSelectEquipements();

		// On lance la reSelection des équipements du bon si on est sur la page de visu (modification) de bon
		if ($('#bons_attachement_modification_site').length != 0)
		{
			{% if entity_bon is defined %}
            	{% for e_equipement in entity_bon.equipementBATicket  %}
					var id_checkbox = 'equipement_' + "{{ e_equipement.id }}";
					$('#' + id_checkbox).trigger('click');
            	{% endfor %}
			{% endif %}
		}

        // On change le format de la datepicker des équipements pour envoyer le format yy/mm/dd
        $("#date_annee_construction_equipement").datepicker( "option", "altField", "#equipement_ba_ticket_anneeDeConstruction" );
        $("#date_annee_construction_equipement").datepicker( "option", "altFormat", "yy/mm/dd" );
});


    function gestionDesSelectionEquipements()
    {
		// Si on est sur la page de saisie de bons
		if ($('#bons_attachement_site').length != 0)
		{
        	// Si la selection du select de site n'est pas vide on affiche le formulaire en lui définissant le même site que celui selectionné
        	if ($('#bons_attachement_site').val() != '')
        	{
        	    $("#select_equipement").val($('#bons_attachement_site').val());
        	}
		} else if ($('#bons_attachement_modification_site').length != 0)
        {
			// Si on est sur la page de visualisation des bons (= page de modification)
            if ($('#bons_attachement_modification_site').val() != '')
            {
                $("#select_equipement").val($('#bons_attachement_modification_site').val());
            }
		} else {
			alert('page parent incorrecte');
			return -1;
		}
		// On vide le champs de recherche 
		$('#recherche_equipement').val('');
        $('#select_equipement').trigger('change');
        togglePopUp(popupSelectionEquipement);
    }
    // Gestion du click sur la croix de fermeture de la popup Sélection des équipements :
    // Fermeture de la popup
    function fermeturePopupSelectionEquipement()
    {
        togglePopUp(popupSelectionEquipement);
    }
    function refreshSelectEquipements()
    {
		// Si on est sur la page de saisie de bons
        if ($('#bons_attachement_site').length != 0)
        {
			$('#select_equipement').val($('#bons_attachement_site').val());
		} else if ($('#bons_attachement_modification_site').length != 0)
        {
        	// On défini le même site sur le select des équipements que le site sélectionné
        	$('#select_equipement').val($('#bons_attachement_modification_site').val());
		}
        // On affiche la liste des équipements associés au nouveau site sélectionné
        $('#select_equipement').change();
    }


    function supprimeEquipement($idEquipement)
    {
        attendreRechargement();

        // On s'assure qu'il n'y a pas de check sur la checkbox de l'équipement à supprimer pour ne pas l'envoyer dans le formulaire de création de bon
        $('#equipement_' + $idEquipement).prop('checked', false);
        // Suppression de l'équipement en ajax et
        // Réaffichage de la page pour prendre en compte la suppression
        $.ajax({
            url: "{{ path('lci_ajax_bon_del_equipement') }}",
            data : {'id_equipement':$idEquipement },
            method: "POST",
            success: function(msg)
            {
				if ($('#bons_attachement_site').length != 0)
				{
                	document.forms['myForm'].submit();
				} else if ($('#bons_attachement_modification_site').length != 0)
                {
					document.forms['myFormFichiers'].submit();
				}
            },
            error: function(status, msg, tri) {
                finAttendreRechargement();
                alert('error ' + msg + tri);
            }
        });
    }

    function deplaceEquipement(id_checkbox, denomination, numeroDeSerie)
    {
        var id_div = "div_equipement_" + id_checkbox;
        var div_tmp = $("#" + id_div);

        // Selection d'un équipement
        if ($('#' + id_div + ' input[type="checkbox"]').is(':checked'))
        {
            // On ajoute l'id de l'équipement au tableau des équipements sélectionnés
            tabDesEquipementsSelectionnes.push(id_checkbox);
            // On retire l'element du div principal
            div_tmp.remove();

            // On ajoute l'élément au div de selection
            $('#liste_des_equipements_selectionnes').append(div_tmp);

			// Ajout de l'élément sur la page HTML
			$html = "<tr id='tr_equipement_" + id_checkbox + "'><td>" + denomination + "</td><td>" + numeroDeSerie + "</td></tr>";
			$('#table_des_equipements').append($html);
        } else {
            // On retire l'id de l'équipement du tableau des équipement selectionnés
            var indexARetirer = tabDesEquipementsSelectionnes.indexOf(id_checkbox);
            tabDesEquipementsSelectionnes.splice(indexARetirer, 1);

            // On retire l'element du div de selection
            div_tmp.remove();

            // On ajoute l'élément au div principal dans son conteneur parent
            $('#parent_' + id_div).append(div_tmp);


			$('#tr_equipement_' + id_checkbox).remove();

        }
    }


	/**********************************			Fonction pour la création des équipements ************************************/
	function creerEquipement2()
    {
        $send_form = true;

        // On valide les informations entrées pour l'équipement
        // Présence d'un site
        var $new_site = $('#equipement_ba_ticket_siteBA').val();
        if ($new_site == '')
        {
            $('#equipement_ba_ticket_siteBA').addClass('erreur_formulaire');
            $send_form = false;
        }

        // Présence du numéro de série
        var $new_numero_de_serie = $('#equipement_ba_ticket_numeroDeSerie').val();
        if ($new_numero_de_serie == '')
        {
            $('#equipement_ba_ticket_numeroDeSerie').addClass('erreur_formulaire');
            $send_form = false;
        }

        //Présence d'une dénomination
        var $new_denomination = $('#equipement_ba_ticket_denomination').val();
        if ($new_denomination == '')
        {
            $('#equipement_ba_ticket_denomination').addClass('erreur_formulaire');
            $send_form = false;
        }

        //Présence d'une autre dénomination (non obligatoire)
        var $new_autre_denomination = $('#equipement_ba_ticket_autreDenomination').val();

        // Présence d'une date d'initialisation
        var $new_annee_de_contruction = $('#equipement_ba_ticket_anneeDeConstruction').val();
        if ($new_annee_de_contruction == '')
        {
            $send_form = false;
            $('#date_annee_construction_equipement').addClass('erreur_formulaire');
        }

        if ($send_form == true)
        {
            attendreRechargement();

            // Appel ajax
            $.ajax({
                url: "{{ path('lci_ajax_bon_new_equipement') }}",
                method: "POST",
                data: $('form[name="equipement_ba_ticket"]').serialize(),
                success: function(output, status, xhr)
                {
                    try {
                        // On recoit une réponse ajax : L'équipement a été enregistré
                        data = JSON.parse(output);

                        // Remplissage des champs indiquant la création d'un nouvel objet
                        var $id_nouvel_equipement = data['message'];
                        $('#bons_attachement_idNouveau').val($id_nouvel_equipement);
                        $('#bons_attachement_typeNouveau').val('equipement');
                        $('#bons_attachement_siteNouveau').val($('#equipement_ba_ticket_siteBA').val());

						// Assignation de l'équipement au bon si on est sur la page de visu (modification de bon)
						if ($('#bons_attachement_modification_site').length != 0)
						{
							$.ajax({
								url : "{{ path('lci_ajax_bon_assign_equipement_to_bon') }}",
								method: "POST",
								data: {'id_equipement':$id_nouvel_equipement, 'id_bon': $('#bons_attachement_modification_id').val()},
								success: function(msg){
									// Raffraichissement du formulaire du bon
									document.forms['myFormFichiers'].submit();
									return 0;
								},
								error: function(data) {
									alert("Assignation de l'équipement en echec");
									console.log(data);
									return -1;
								}
							});
						} else if ($('#bons_attachement_site').length != 0)
                        {
							// Si on est su la page de saisie d'un bon on raffraichit la page
                        	// Rechargement de la page de saisie
                        	document.forms['myForm'].submit();
						}
                    } catch(e) {
                        // On ne recoit pas une réponse Ajax : on recoit donc le formulaire HTML avec les erreurs
                        var form_html = output;
                        $('#popupEquipement2').html(form_html);
                        // Activation du datepicker pour le nouveau formulaire
                        setDatePicker();
                        // On défini l'input affiché comme source pour l'input du formulaire : Permet d'afficher un format fr et d'envoyer un format  pour la date
                        // On change le format de la datepicker des équipements pour envoyer le format yy/mm/dd
                        $("#date_annee_construction_equipement").datepicker( "option", "altField", "#equipement_ba_ticket_anneeDeConstruction" );
                        $("#date_annee_construction_equipement").datepicker( "option", "altFormat", "yy/mm/dd" );
                        finAttendreRechargement();
                    }
                },
                error: function (xhr, status, st_text)
                {
                    finAttendreRechargement
                    alert('error');
                }
            });
        }
    }

    // Gestion du clic sur Annuler dans la popup de création d'équipement :
    // Fermeture de la popup Creer un nouvel équipement
    // Ouverture de la popup de Selection des équipements
    function annulerPopupEquipement2()
    {
        $('#equipement_ba_ticket_siteBA').removeClass('erreur_formulaire');
        $('#equipement_ba_ticket_numeroDeSerie').removeClass('erreur_formulaire');
        $('#equipement_ba_ticket_denomination').removeClass('erreur_formulaire');
        $('#date_annee_construction_equipement').removeClass('erreur_formulaire');

        $('#equipement_ba_ticket_reset').trigger('click');

        togglePopUp(popupEquipement2);
        togglePopUp(popupSelectionEquipement);
    }


    // Gestion du click sur la croix de fermeture de la popup de création d'équipement :
    // Fermeture de la popup de création d'équipement
    // Fermeture de la popup de sélection d'équipements
    function fermeturePopupEquipement2()
    {
        $('#equipement_ba_ticket_siteBA').removeClass('erreur_formulaire');
        $('#equipement_ba_ticket_numeroDeSerie').removeClass('erreur_formulaire');
        $('#equipement_ba_ticket_denomination').removeClass('erreur_formulaire');
        $('#date_annee_construction_equipement').removeClass('erreur_formulaire');

		$('#equipement_ba_ticket_reset').trigger('click');

        togglePopUp(popupEquipement2);
    }


    function gestionDesEquipements2()
    {
		// Si on est sur la page de saisie des sites
		if ($('#bons_attachement_site').length != 0)
		{
        	// Si la selection du select de site n'est pas vide on affiche le formulaire en lui définissant le même site que celui selectionné
        	if ($('#bons_attachement_site').val() != '')
        	{
        	    // On définit le site selectionné comme site selectionnée dans le select du site
        	    $('#equipement_ba_ticket_siteBA').val($('#bons_attachement_site').val());
			} else if ($('#select_equipement').val() != '')
        	{
        	    // On définit le site selectionné comme site selectionnée dans le select nouvel Equipement
        	    $('#equipement_ba_ticket_siteBA').val($('#select_equipement').val());
        	}
		} else if ($('#bons_attachement_modification_site').length != 0)
		{
			// Si on est sur la page de visu / modification du site
			// Si la selection du select de site n'est pas vide on affiche le formulaire en lui définissant le même site que celui selectionné
            if ($('#bons_attachement_modification_site').val() != '')
            {
				// On définit le site selectionné comme site selectionnée dans le select du site
                $('#equipement_ba_ticket_siteBA').val($('#bons_attachement_modification_site').val());
            } else if ($('#select_equipement').val() != '')
        	{
            	// On définit le site selectionné comme site selectionnée dans le select nouvel Equipement
            	$('#equipement_ba_ticket_siteBA').val($('#select_equipement').val());
        	}
        } 

        togglePopUp(popupSelectionEquipement);
        togglePopUp(popupEquipement2);
    }

</script>
