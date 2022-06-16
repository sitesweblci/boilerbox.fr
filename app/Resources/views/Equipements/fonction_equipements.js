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
							var tmp_autreDenomination = "{{ e_equipement.autreDenomination }}";
							tmp_autreDenomination = tmp_autreDenomination.replace("'", "\\\'");

							var tmp_denomination = "{{ e_equipement.denomination }}";
							tmp_denomination = tmp_denomination.replace("'", "\\\'");

                            // Création du contenu html des Equipements
                            html_nouvelles_options += " \
                                <div id='parent_div_equipement_{{ e_equipement.id }}' onMouseEnter='sourisOver(\"entre\",this.id);' onMouseLeave='sourisOver(\"sort\",this.id);'> \
                                    <div id='div_equipement_{{ e_equipement.id }}'> \
                                        {#<span style='display: inline-block; width:15px; line-height:11px; padding-bottom: 3px; text-align:center; cursor:pointer;' onClick=\"supprimeEquipement('{{ e_equipement.id  }}');\">x</span> \#}
                                        <span style='display:inline-block; width:30px;'> \
                                            <input    type='checkbox' \
                                                id='equipement_{{ e_equipement.id }}' \
                                                name='equipement_{{ e_equipement.id }}' \
                                                value='{{ e_equipement.id }}' \
                                                style='display:inline-block; border:2px solid gray; cursor:pointer;' \
                                                onClick=\"deplaceEquipement('{{ e_equipement.id }}', '{{ e_equipement.numeroDeSerie }}', '" + tmp_denomination + "', '" + tmp_autreDenomination + "')\" \
                                            />\
                                        </span><label style='display:inline-block; cursor:pointer;' for='equipement_{{ e_equipement.id }}'><span style='display:inline-block; width:80px;margin-right:10px'>({{ e_equipement.numeroDeSerie }})</span><span style='display:inline-block; width:80px;margin-right:10px'>{{ e_equipement.anneeDeConstruction | date('d/m/y') }}</span><span style='display:inline-block; width:120px;margin-right:10px'>{{ e_equipement.denomination }}</span><span style='display:inline-block; width:120px;margin-right:10px'>{{ e_equipement.autreDenomination }}</span><span style='display:inline-block; width:200px;'>{{ e_equipement.siteBA.intitule }}</span></label>\
                                    </div>\
                                </div>";
                        } else {
                            // Si l'équipement est déjà sélectionné, on n'affiche que le div du parent pour pouvoir replacer l'équipement en cas de dé sélection de celui ci
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
            $('#liste_des_equipements label').each(function()
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
		if ($('#bons_attachement_site').length != 0)
		{
			// Si on est sur la page de saisie de bons
        	// et Si la selection du select de site n'est pas vide on affiche le formulaire en lui définissant le même site que celui selectionné
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
		}  else if ($('#ticket_incident_site').length != 0)
        {
            // Si on est sur la page de saisie d'un ticket
            if ($('#ticket_incident_site').val() != '')
            {
                $("#select_equipement").val($('#ticket_incident_site').val());
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
		} else if ($('#ticket_incident_site').length != 0)
        {
            // On défini le même site sur le select des équipements que le site sélectionné
            $('#select_equipement').val($('#ticket_incident_site').val());
        }
        // On affiche la liste des équipements associés au nouveau site sélectionné
        $('#select_equipement').change();
    }


	function modifierEquipement($id_equipement)
    {
		$('#titre_nouvel_equipement2').text("Modification de l'équipement");
		creerModifierEquipement('recherche', $id_equipement);
	}


	//  Accepte pour $type_action : recherche et modification
    function creerModifierEquipement($type_action, $id_equipement=null)
    {
		var $data_a_envoyer = $id_equipement;
		if ($id_equipement == null)
		{
			$data_a_envoyer = $('form[name="equipement_ba_ticket"]').serialize()
		} else {
			$data_a_envoyer = {'id_equipement':$id_equipement}
		}
		attendreRechargement();

        // Modification de l'équipement en ajax et
        // Réaffichage de la page pour prendre en compte la suppression
        $.ajax({
            url: "{{ path('lci_ajax_bon_new_equipement') }}",
            data : $data_a_envoyer,
            method: "POST",
            success: function(output)
            {
				try {
                   	// On recoit une réponse ajax : 
					//					L'équipement a été enregistré : On recoit l'entité de l'equipement
					// 					Une demande de modification de l'équipement a été envoyé et nous recevons les informations courantes de l'équipement

                	var $data_e_equipement = JSON.parse(output);

                    $('#equipement_ba_ticket_id').val($data_e_equipement.id);
                    $('#equipement_ba_ticket_siteBA').val($data_e_equipement.siteBA.id);
                    $('#equipement_ba_ticket_numeroDeSerie').val($data_e_equipement.numeroDeSerie);
                    $('#equipement_ba_ticket_denomination').val($data_e_equipement.denomination);
                    $('#equipement_ba_ticket_autreDenomination').val($data_e_equipement.autreDenomination);
                    var date_reverse_creation_equipement = dateTransformeFromEntiteSerializedForPicker($data_e_equipement.anneeDeConstruction);
                    var date_creation_equipement = dateTransformeFromEntiteSerialized($data_e_equipement.anneeDeConstruction);
                    $('#date_annee_construction_equipement').val(date_reverse_creation_equipement);
                    $('#equipement_ba_ticket_anneeDeConstruction').val(date_creation_equipement);

					if ($type_action == 'recherche')
					{
						// On affiche les informations de l'équipement recherché
						togglePopUp(popupEquipement2);
						finAttendreRechargement();
					} else if ($type_action == 'modification')
					{
						// On retire le champs des équipements pour ne pas enregistrer les anciennes valeurs
						$('#bons_attachement_modification_equipementBATicket').remove();
					}
				} catch(e)
				{
					// Si reception d'un page HTML c'est qu'on recoit le formulaire du bon
					// On modifie le contenu de la popup de création d'équipement pour lui mettre le contenu de l'équipement à modifier
					$('#popupEquipement2').html(output);
					togglePopUp(popupEquipement2);

					// Sauvegarde de la date de création car après setDatePicket, la valeur  du champs est remise à 0
					var date_creation_equipement = $('#equipement_ba_ticket_anneeDeConstruction').val();

					// Activation du datepicker pour le nouveau formulaire
					setDatePicker();


					// On définit le champs qui recoit la date
                    // On change le format de la datepicker des équipements pour envoyer le format yy/mm/dd
        			$("#date_annee_construction_equipement").datepicker( "option", "altField", "#equipement_ba_ticket_anneeDeConstruction" );
        			$("#date_annee_construction_equipement").datepicker( "option", "altFormat", "yy/mm/dd" );

					// On réindique la date de création de l'équipement
					$("#date_annee_construction_equipement").val(dateReverseForPicker(date_creation_equipement));

					return 0;
				}

				if ($type_action == 'modification')
                {
					// Submit du formulaire de bon de la page de saisie de bon
                	if ( ($('#bons_attachement_site').length != 0) || ($('#ticket_incident_site').length != 0) )
                	{
                	    document.forms['myForm'].submit();
                	} else if ($('#bons_attachement_modification_site').length != 0)
                	{
						// Submit du formulaire de bon de la page de visu / modification de bon
                	    document.forms['myFormFichiers'].submit();
                	}
				}

            },
            error: function(status, msg, tri) {
                finAttendreRechargement();
                alert('error ' + msg + tri);
            }
        });
    }

    function supprimeEquipement($id_equipement)
    {
        attendreRechargement();

        // On s'assure qu'il n'y a pas de check sur la checkbox de l'équipement à supprimer pour ne pas l'envoyer dans le formulaire de création de bon
        $('#equipement_' + $id_equipement).prop('checked', false);
        // Suppression de l'équipement en ajax et
        // Réaffichage de la page pour prendre en compte la suppression
        $.ajax({
            url: "{{ path('lci_ajax_bon_del_equipement') }}",
            data : {'id_equipement':$id_equipement },
            method: "POST",
            success: function(msg)
            {
				if ( ($('#bons_attachement_site').length != 0) || ($('#ticket_incident_site').length != 0) )
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

    function deplaceEquipement(id_checkbox, numeroDeSerie, denomination, autreDenomination)
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
			$html = "<tr id='tr_equipement_" + id_checkbox + "' class='flex-table__row' onClick=\"modifierEquipement('" + id_checkbox + "');\">" + "<td>" + numeroDeSerie + "</td>" + "<td>" + denomination + "</td>" + "<td>" + autreDenomination+ "</td></tr>";
			$('#table_des_equipements tbody').append($html);
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
	function verificationCreationEquipement()
    {
		if ($('#titre_nouvel_equipement2').text() ==  "Modification de l'équipement")
		{
			togglePopUp(popupEquipement2);
			return creerModifierEquipement('modification');
		}
	
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
                        // On recoit une réponse ajax : L'équipement a été enregistré (on recoit l'entité équipement)
                        $data_e_equipement = JSON.parse(output);
                        // Remplissage des champs indiquant la création d'un nouvel objet
                        var $id_nouvel_equipement = $data_e_equipement['id'];
                        $('#bons_attachement_idNouveau').val($id_nouvel_equipement);
                        $('#bons_attachement_typeNouveau').val('equipement');
                        $('#bons_attachement_siteNouveau').val($('#equipement_ba_ticket_siteBA').val());

						// Assignation de l'équipement au bon si on est sur la page de visu (modification de bon)
						if ( ($('#bons_attachement_site').length != 0) || ($('#ticket_incident_site').length != 0) )
                        {
                            // Si on est su la page de saisie d'un bon on raffraichit la page
                            // Rechargement de la page de saisie
                            document.forms['myForm'].submit();
                        } else if ($('#bons_attachement_modification_site').length != 0)
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
									alert("Echec d'assignation de l'équipement");
									console.log(data);
									return -1;
								}
							});
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
		// Si on est sur la page de [ saisie des bons ]
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
        } else if ($('#ticket_incident_site').length != 0)
        {
            // Si la selection du select de site n'est pas vide on affiche le formulaire en lui définissant le même site que celui selectionné
            if ($('#ticket_incident_site').val() != '')
            {
                // On définit le site selectionné comme site selectionnée dans le select du site
                $('#equipement_ba_ticket_siteBA').val($('#ticket_incident_site').val());
            } else if ($('#select_equipement').val() != '')
            {
                // On définit le site selectionné comme site selectionnée dans le select nouvel Equipement
                $('#equipement_ba_ticket_siteBA').val($('#select_equipement').val());
            }
        }
		// Fermeture de la popup de selection des équipements
        togglePopUp(popupSelectionEquipement);

		// Ouverture de la popup de création d'équipement
		$("#titre_nouvel_equipement2").text('Nouvel équipement');
        togglePopUp(popupEquipement2);
    }

	function sourisOver(direction, id_element) 
	{
		if (direction == 'entre')
		{
			$('#' + id_element).css('background-color', 'lightgray');
		} else {
			$('#' + id_element).css('background-color', 'white');
		}
	}



	function dateTransformeFromEntiteSerializedForPicker(str_date)
    {
        var new_ch = str_date.substr(0,10);
        var new_ch2 = new_ch.replace(/(....)-(..)-(..)/,"$3/$2/$1");
        return new_ch2;
    }
	function dateTransformeFromEntiteSerialized(str_date)
    {
        var new_ch = str_date.substr(0,10);
        var new_ch2 = new_ch.replace(/(....)-(..)-(..)/,"$1/$2/$3");
        return new_ch2;
    }



	function dateTransformeEntiteSerialise(str_date)
	{	
		var new_ch = str_date.substr(0,10);
		var new_ch2 = new_ch.replace(/..(..)-(..)-(..)/,"$3/$2/$1");
		return new_ch2;
	}


	function dateReverseForPicker(date_enter)
	{
		var new_ch2 = date_enter.replace(/(....)\/(..)\/(..)/,"$3/$2/$1");
        return new_ch2;
	}
</script>
