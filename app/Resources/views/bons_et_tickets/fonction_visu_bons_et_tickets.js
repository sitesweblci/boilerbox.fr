<script 'type=text/javascript'>

	// information : La variable $idBon est définie dans les ficchier form_visu_un_bon et form_visu_un_ticket. 
	//					Elle doit être instanciés avant l'include de ce fichier

    // Fonction style barre de navigation page active
    function pageActive() {
        $('.side-nav .bons-interv').addClass('active');
    }
    // On force le scroll tout en bas des commentaires pour afficher le plus récent
    function scrollCommentaire() {
        scrollComm = document.getElementById('commentaires_bon');
        scrollComm.scrollTop = scrollComm.scrollHeight;
        console.log(scrollComm.scrollHeight);
        console.log(scrollComm.scrollTop);
    }
    $(window).on('load', function(){
        pageActive();
        scrollCommentaire();
    })

    /* Débug largeur parent flex col sur un resize */
    $(window).on('resize', function(){
        $('.main-content').css({'width':'initial'});
        setTimeout(function(){
            $('.main-content').css({'width':'fit-content'});
        }, 30);

    });


    // Fonction affichage selon l'onglet sélectionné
    const tabs = document.querySelectorAll('[data-tab-target]')
    const tabContents = document.querySelectorAll('[data-tab-content]')

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const target = document.querySelector(tab.dataset.tabTarget)
            tabContents.forEach(tabContent => {
                tabContent.classList.remove('active')
            })
            tabs.forEach(tab => {
                tab.classList.remove('active')
            })
            tab.classList.add('active')
            target.classList.add('active')
        })
    });
    // Fonction d'activation du bouton 'publier commentaire'
    $('#commentaires').on('keyup', function enableBtnComm(){
        if($('#commentaires').val().length){
            $('#js-btn-comm > div').removeClass('main-box__btn--disabled');
            $('#js-btn-comm .btn--main').removeClass('btn--disabled');
        } else if (!$('#commentaires').val().length){
            $('#js-btn-comm > div').addClass('main-box__btn--disabled');
            $('#js-btn-comm .btn--main').addClass('btn--disabled');
        }
    });


    function attendreRechargement()
    {
        $('*').addClass('cursor--wait');
    }

    function finAttendreRechargement()
    {
        $('*').removeClass('cursor--wait');
    }


    // Affichage de la map du site avec le marqueur
    function initAutocomplete()
    {
        if (("{{ latitude }}" != "") && ("{{ longitude }}" != ""))
        {
            $('#map').removeClass('cacher');
            var $objPos = {};
            $objPos.lat = parseFloat("{{ latitude }}");
            $objPos.lng = parseFloat("{{ longitude }}");
            var maCarte = new google.maps.Map(document.getElementById('map'), {
                center: $objPos,
                zoom: parseInt("{{ zoomApi }}"),
                mapTypeId: 'satellite'
            });
            var monMarqueur = new google.maps.Marker({
                position: $objPos,
                map: maCarte
            });
        }
    }


    /******************* Fonctions pour les consignes de facturation */
    function validationCommentaire(type)
    {
		switch(type)
		{
			case 'sav':
        		var texte = $('#consignesFacturation').val();
        		if (texte != '')
        		{
        		    texte = 'Consigne SAV : ' + texte;
        		    ajoutCommentaire('sav', texte);
        		} else {
        		    $('#avertissementConsignesFacturation').text("Merci d'indiquer les consignes de facturations");
        		    return 1;
        		}	
			break;
			case 'cloture':
		        var texte 				= $('#consignesClientCloture').val();
        		if (texte != '')
        		{
            		// Envoi du mail de cloture au client
            		$.ajax({
            		    url: "{{ path('envoi_mail_cloture') }}",
            		    data:  {'id_entity':$idBon, 'commentaire':texte},
            		    method: "POST",
            		    success: function(msg)
            		    {
            		        ajoutCommentaire('cloture',"<span class='info_system'>Informations de clôture client</span> : " + texte);

							var texte_technicien    = $('#consignesTechnicienCloture').val();
							ajoutCommentaire('cloture_technicien', "<span class='info_system'>Informations de clôture technicien</span> : " + texte_technicien);
            		    },
            		    error: function(){
            		        alert("Echec de l'envoi du mail client de cloture incident");
                		}
            		});
        		} else {
            		$('#avertissementConsignesCloture').text("Merci d'indiquer les informations client de cloture");
            		return 1;
       	 		}
			break;
			case 'changeIntervenant':
        		var texte = $('#motifChangeIntervenant').val();
        		if (texte != '')
        		{
					var texte_auto;
					if ($("#last_id_intervenant").val() != '')
					{
        		    	texte_auto = "Changement d'intervenant effectué par {{ app.user.label }} - Ancien intervenant : ";
        		    	texte_auto += $('#' + id_select_intervenant + ' option[value=' + $("#last_id_intervenant").val() + ']').text();
        		    	texte_auto += " - Nouvel intervenant : " + $('#' + id_select_intervenant + ' option:selected').text();
					} else {
						texte_auto = "Changement d'intervenant effectué par {{ app.user.label }}";
						texte_auto += " - Nouvel intervenant : " + $('#' + id_select_intervenant + ' option:selected').text();
					}

        		    // On indique le changement effectué
        		    ajoutCommentaire('autoNoSave', texte_auto);

        		    texte = "Motif de changement d'intervenant : " + texte;
        		    ajoutCommentaire('motif', texte);
        		    // On remplace l'indication du dernier intervenant
        		    $("#last_id_intervenant").val($('#' + id_select_intervenant).val());
        		} else {
        		    $('#avertissementChangeIntervenant').text("Merci d'indiquer le motif de changement d'intervenant");
        		    return 1;
        		}
			break;
		}
    }

    // Permet la fermeture de la popup sans consigne mais désactive alors ne modifie pas l'intervenant (garder en memoire id last intervenant)
    function fermeturePopupCommentaire(type)
    {
		switch(type)
		{
			case 'sav':
				$('#bons_attachement_validation_validationSAV_valide').prop('checked', false);
    		    $('#avertissementConsignesFacturation').text('');
    		    togglePopUp(commentaireSAV);
			break;
			case 'cloture':	
		        $('#ticket_incident_validation_validationCloture_valide').prop('checked', false);
        		$('#avertissementConsignesCloture').text('');
        		togglePopUp(commentaireCloture);
			break;
            case 'changeIntervenant':
                $('#bons_attachement_modification_user').val($("#last_id_intervenant").val());
                $('#avertissementChangeIntervenant').text('');
                togglePopUp(commentaireChangeIntervenant);
            break;
		}
    }



    // type = autoNoSave, sav ou motif -> ce sont les cas ou le paramètre passé : commentaire n'est pas null
    function ajoutCommentaire(type=null, commentaire=null)
    {
        if (commentaire == null)
        {
            commentaire  = $('#commentaires').val();
            $.ajax({
                url: "{{ path('ajout_commentaire_bons_et_tickets') }}",
                data: {'id_bon':$idBon, 'commentaire':commentaire},
                method: "POST",
                success: function(msg){
                    document.forms['myFormFichiers'].submit();
                },
                error: function(){
                    alert("Erreur d'envoi du message");
                    return 1;
                }
            });
        } else {
            // Ajout du commentaire en ajax
            $.ajax({
                url: "{{ path('lci_bon_commentaire_consigne_facturation') }}",
                data: {'id_bon':$idBon, 'commentaire':commentaire},
                method: "POST",
                success: function(msg){
                    if ((type == 'sav') || (type == 'cloture'))
                    {
                        // Modification de la checkbox
                        sendValidationAjaxRequest(type, true);
						return 0;
                    }
                    if (type != 'autoNoSave')
                    {
                        document.forms['myFormFichiers'].submit();
						return 0;
                    }
                },
                error: function(){
                    if (type == 'sav')
                    {
                        alert("Erreur d'envoi de la consigne de facturation");
                    }  else if (type == 'cloture')
                    {
                        alert("Erreur d'envoi du mail de cloture au client");
                    } else if (type == 'motif')
                    {
                        alert("Erreur d'envoi du motif de modification d'intervenant");
                    }
                    return 1;
                }
            });
        }
    }


    function sendValidationAjaxRequest($type, $sens)
    {
        attente();
        setTimeout(function()
        {
            $.ajax({
                url: "{{ path('lci_ajax_bons_setValidation') }}",
                method: "post",
                data: {'identifiant':$idBon, 'type':$type, 'sens':$sens},
                success: function(msg)
                {
                    window.location.assign(location.href);
                    return true;
                },
                error: function(request, $error, $msg)
                {
                    alert('error ' + $error + $msg);
                    fin_attente();
                    switch($type) {
                        case 'technique':
                            $('#bons_attachement_validation_validationTechnique_valide').prop("checked", true);
                            break;
                        case 'sav':
                            $('#bons_attachement_validation_validationSAV_valide').prop("checked", true);
                            break;
                        case 'pieces':
                            $('#bons_attachement_validation_validationPiece_valide').prop("checked", true);
                            break;
                        case 'pieces_faite':
                            $('#bons_attachement_validation_validationPieceFaite_valide').prop("checked", true);
                            break;
                        case 'facturation':
                            $('#bons_attachement_validation_validationFacturation_valide').prop("checked", true);
                            break;
                        case 'intervention':
                            $('#ticket_incident_validation_validationIntervention_valide').prop("checked", true);
                            break;
                        case 'cloture':
                            $('#ticket_incident_validation_validationCloture_valide').prop("checked", true);
                            break;
                    }
                    return false;
                }
            });
        }, 500);
    }


    function afficheFichier($url) {
        $('#' + $url)[0].click();
    }


    function archiveUnFichierDuBon($idFichier, $archive) {
        $.ajax({
            url: "{{ path('lci_ajax_bons_archive_fichier') }}",
            method: "post",
            data: {'identifiant_fichier':$idFichier},
            success: function($msg) {
                if ($archive == false) {
                    $('#fichier_' +  $idFichier).addClass('archive');
                    $('#image_' + $idFichier).attr('src', "{{ asset('bundles/lciboilerbox/images/bons/boutonsMenu/croix-suppression.png') }}");
                    $('#image_' + $idFichier).attr('onClick',"archiveUnFichierDuBon('" + $idFichier + "', '1')");
                } else {
                    $('#fichier_' +  $idFichier).removeClass('archive');
                    $('#image_' + $idFichier).attr('src', "{{ asset('bundles/lciboilerbox/images/bons/boutonsMenu/croix-ajout.png') }}");
                    $('#image_' + $idFichier).attr('onClick',"archiveUnFichierDuBon('" + $idFichier + "', '0')");
                }
                afficheArchives();
            },
            error: function($requete, $error, $msg) {
                alert('Erreur : Archivage non effectué');
            }
        });
    }




    // Fonction qui ajout un champs 'Nouveau fichier' en remplacant le label et le nom par l'index du fichier
    function ajoutChampFichier() {
        var $nouveauPrototype = $($('#' + id_lien_fichiersPdf).data('prototype').replace(/__name__label__/g, '').replace(/__name__/g, $indexFichier));

        // Création et ajout d'un lien de suppression en fin de container
        $deleteLink = $('<a href="#" class="mini_link">Supprimer</a>');
        $nouveauPrototype.append($deleteLink);

        // Ajout du listener permettant la suppression du champ fichier lors du clic sur le lien
        $deleteLink.click(function(e){
            $nouveauPrototype.remove();
            e.preventDefault();
            return false;
        });

        // Ajout du prototype a la fin du container
        $container.prepend($nouveauPrototype);

        // Incrémentation de l'index des fichiers
        $indexFichier ++;
    }

    // Fonction qui renvoie vers la page de modification de bon
    function updateBon($idBon) {
        $('#id_bon').val($idBon);
        document.forms['form_modification_bon'].submit();
    }


    function toggleFiles() {
        $('#info_bulle_fichier').toggleClass("fichier");
        $('#formulaire_bons_fichiers').toggleClass("cacher");
        if ($('#formulaire_bons_fichiers').is(':visible')) {
            $('#img_validation').attr('onClick', "attente(); document.forms['myFormFichiers'].submit(); return false;");
        } else {
            $('#img_validation').attr('onClick', "attente(); document.forms['myFormCommentaires'].submit(); return false;");
        }
    }


    function afficheArchives() {
        if ($('#chk_archive').is(":checked")) {
            $('#table_des_fichiers tr').each(function() {
                $(this).removeClass('cacher');
            });
        } else if (!$('#chk_archive').is(':checked')){
            $('#table_des_fichiers tr').each(function() {
                if ($(this).hasClass('archive')) {
                    $(this).addClass('cacher');
                }
            });
        }
    }


    // Fonction qui supprime les informations contenues entre les parenthèses des noms de fichiers
    function definirNomFichier($idLien, $nomDuFichier)
    {
        var $regex = /\([a-zA-Z0-9àéèêâ:\/\s]+\)$/;
        $('#' + $idLien).attr('download', $.trim($nomDuFichier.replace($regex, '')));
    }




    $('#' + id_select_intervenant).change(function(e) {
        togglePopUp(commentaireChangeIntervenant);
    });

    // Fonction d'activation du bouton 'publier commentaire'
    $('#commentaires').on('keyup', function enableBtnComm(){
        if($('#commentaires').val().length){
            $('#js-btn-comm > div').removeClass('main-box__btn--disabled');
            $('#js-btn-comm .btn--main').removeClass('btn--disabled');
        } else if (!$('#commentaires').val().length){
            $('#js-btn-comm > div').addClass('main-box__btn--disabled');
            $('#js-btn-comm .btn--main').addClass('btn--disabled');
        }
    });



</script>
