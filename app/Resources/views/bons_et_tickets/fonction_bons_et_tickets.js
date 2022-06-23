<script 'type=text/javascript'>
    // Fonction style barre de navigation page active
    $(window).on('load', function pageActive(){
        $('.side-nav .bons-interv').addClass('active');
    });

    var maCarte, maCarteVisualisation;
	var monMarqueur, monMarqueurVisualisation;

    function attendreRechargement()
    {
        $('*').addClass('cursor--wait');
    }

    function finAttendreRechargement()
    {
        $('*').removeClass('cursor--wait');
    }


    function initAutocomplete()
    {

		/* Création des cartes google */
        maCarte = new google.maps.Map(document.getElementById('map'), {
            center: {lat: 50.474, lng: 2.97118},
            zoom: 4,
            mapTypeId: 'satellite'
        });

		maCarteVisualisation = new google.maps.Map(document.getElementById('mapVisualisation'), {
            center: {lat: 50.474, lng: 2.97118},
            zoom: 4,
            mapTypeId: 'satellite'
        });


        /* Mise en place du textbox dans la carte */
        var input = document.getElementById('site_ba_adresse');

        var autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.setFields(['place_id', 'geometry']);

        /* Placement du marqueur sur l'emplacement selectionné */
        maCarte.addListener('click', function(e)
        {
            if (monMarqueur != null) {
                monMarqueur.setMap(null);
            }
            monMarqueur = new google.maps.Marker({
                position: e.latLng,
                map: maCarte,
                icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-circle.png',
				title: 'Entrée'
            });
            maCarte.setZoom(19);
            maCarte.setCenter(monMarqueur.getPosition());
            maCarte.setMapTypeId('satellite');


			if (monMarqueurVisualisation != null) {
                monMarqueurVisualisation.setMap(null);
            }
            monMarqueurVisualisation = new google.maps.Marker({
                position: e.latLng,
                map: maCarteVisualisation,
                icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-circle.png',
                title: 'Entrée'
            });
            maCarteVisualisation.setZoom(19);
            maCarteVisualisation.setCenter(monMarqueurVisualisation.getPosition());
            maCarteVisualisation.setMapTypeId('satellite');



            /* Service de geocoding pour récupérer l'adresse du lieu selectionné par la souris */
            /* On ne modifie que l'url en fonction du marqueur */
            geocoder = new google.maps.Geocoder();
            geocoder.geocode({'location':monMarqueur.getPosition()}, function(results, status)
            {
                if (status == 'OK')
                {
                    $('#site_ba_lienGoogle').val('latLng' + monMarqueur.getPosition());
                    $('#site_ba_lienGoogle').attr('readonly', true);
                } else {
                    alert('Aucune adresse trouvée pour le lieu selectionné');
                }
            });
        });
        autocomplete.addListener('place_changed', function()
        {
            var place = autocomplete.getPlace();
            if (place.geometry) {
                maCarte.panTo(place.geometry.location);
                maCarte.setZoom(19);
                maCarte.setMapTypeId('satellite');
                monMarqueur = new google.maps.Marker({
                    position: place.geometry.location,
                    map: maCarte
                });

				maCarteVisualisation.panTo(place.geometry.location);
                maCarteVisualisation.setZoom(19);
                maCarteVisualisation.setMapTypeId('satellite');
                monMarqueurVisualisation = new google.maps.Marker({
                    position: place.geometry.location,
                    map: maCarteVisualisation
                });

            }
            $('#site_ba_lienGoogle').val('latLng' + place.geometry.location);
            $('#site_ba_lienGoogle').attr('readonly', true)
        });
    }

    $('#site_ba_lienGoogle').on('change', function()
    {
        var $objPos = {};

        var $position = $('#site_ba_lienGoogle').val();
        if ($position == '' )
        {
            return 0;
        }


        // Si la coordonnée est retournée par l'outil de google map On zoom sur le lieu de l'adresse selectionnée
        // Sinon si l'adresse provient du site web google map, on extrait les coordonnées
        var $coordonnees = $position.match(/latLng\((.+?),(.+?)\)/);
        if ($coordonnees == null)
        {
            geocoder = new google.maps.Geocoder();
            var $adresse = 'https://maps.googleapis.com/maps/api/geocode/json?address=' + $('#site_ba_lienGoogle').val() + '&key=' + "{{ apiKey }}";
            $.ajax({
                url: $adresse,
                method: "get",
                success: function(msg) {
                    $objPos.lat = msg['results'][0]['geometry']['location']['lat'];
                    $objPos.lng = msg['results'][0]['geometry']['location']['lng'];

                    maCarte.setCenter($objPos);
                    maCarte.setZoom(19);
                    maCarte.setMapTypeId('satellite');
                    monMarqueur = new google.maps.Marker({
                        position: $objPos,
                        map: maCarte
                    });

                    maCarteVisualisation.setCenter($objPos);
                    maCarteVisualisation.setZoom(19);
                    maCarteVisualisation.setMapTypeId('satellite');
                    monMarqueurVisualisation = new google.maps.Marker({
                        position: $objPos,
                        map: maCarteVisualisation
                    });

                    $latLng = 'latLng(' + $objPos.lat + ',' + $objPos.lng + ')';
                    $('#site_ba_lienGoogle').val($latLng);
                }
            });
        } else {
            $objPos.lat = parseFloat($coordonnees[1]);
            $objPos.lng = parseFloat($coordonnees[2]);

            maCarte.setCenter($objPos);
            maCarte.setZoom(19);
            maCarte.setMapTypeId('satellite');


            maCarteVisualisation.setCenter($objPos);
            maCarteVisualisation.setZoom(19);
            maCarteVisualisation.setMapTypeId('satellite');
        }
    });



    // Fonction qui ajoute un champs 'Nouveau fichier' en remplacant le label et le nom par l'index du fichier
    function ajoutChampFichier() {
        var $nouveauPrototype = $($('#' + id_lien_fichiersPdf).data('prototype').replace(/__name__label__/g, '').replace(/__name__/g, $indexFichier));

        // Création et ajout d'un lien de suppression en fin de container
        $deleteLink = $('<a href="#" class="mini_link">Supprimer</a>');
        $nouveauPrototype.append($deleteLink);

        $deleteLink.click(function(e){
            $nouveauPrototype.remove();
            e.preventDefault();
            return false;
        });

        // Ajout du prototype a la fin du container
        $container_fichiers_bon.prepend($nouveauPrototype);
        // Incrémentation de l'index des fichiers
        $indexFichier ++;
    }



    // Fonction qui ajoute un champs 'Nouveau fichier de site BA' en remplacant le label et le nom par l'index du fichier
    function ajoutChampFichierSiteBA() {
        var $nouveauPrototype = $($('#site_ba_fichiersJoint').data('prototype').replace(/__name__label__/g, '').replace(/__name__/g, $indexFichierSiteBA));

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
        $container_fichiers_site.prepend($nouveauPrototype);
        // Incrémentation de l'index des fichiers
        $indexFichierSiteBA ++;
    }



    function ajoutMarqueur($infosCoordonnees) {
        var $objPos = {};
        var $coordonnees = $infosCoordonnees.match(/latLng\((.+?),(.+?)\)/);
        if ($coordonnees != null)
        {
            $objPos.lat = parseFloat($coordonnees[1]);
            $objPos.lng = parseFloat($coordonnees[2]);

            /* Création du marqueur */
            monMarqueur = new google.maps.Marker({
                position: $objPos,
				map: maCarte,
                icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-circle.png',
                title: 'Entrée'
            });
            /* Placement du marqueur sur la carte */
            maCarte.setZoom(19);
            maCarte.setCenter(monMarqueur.getPosition());
            maCarte.setMapTypeId('satellite');


			monMarqueurVisualisation = new google.maps.Marker({
                position: $objPos,
                map: maCarteVisualisation,
                icon: 'http://maps.google.com/mapfiles/kml/paddle/blu-circle.png',
                title: 'Entrée'
            });
            maCarteVisualisation.setZoom(19);
            maCarteVisualisation.setCenter(monMarqueurVisualisation.getPosition());
            maCarteVisualisation.setMapTypeId('satellite');
        }
    }



    // Active le datepicker sur tous les input ayant le placeholder à 'dd/mm/YYYY'
    function setDatePicker()
    {
        $("input[placeholder='dd/mm/YYYY']").datepicker();
    }


    function resetInfosSite()
    {
        $('#site_ba_intitule').attr('readonly', false);
        $('#site_ba_lienGoogle').attr('readonly', false);
        $('#id_site_ba').val('');
        $('#fichiers_deja_joints').html('');
        $('#contacts_deja_associes').html('');
        $('#titre_nouveau_siteBA').text('Nouveau site');

        $('#equipements_deja_joints').html('');
    }
    // Fonction d'ajout fichier
    var imageId = 0;
    var baseFiles = [];

    $("#add-fichierBon").on("click", () => {
        $("#fichierBon").click();
    });
    $("#add-equipBon").on("click", () => {
        $("#equipBon").click();
    });

    $('.file-input').change(function (e) {
        var input = e.target;
        var files = input.files;
        var images = $('.images');

        for (var i = 0; i < files.length; i++) {
            imageId++;

            ((file) => {
                var reader = new FileReader();
                var fileName = file.name.replace(/\.[^.]*$/, ''); // without file extension
                var fileId = file.size + imageId;
                var baseFile = '';

                reader.onload = (e) => {
                    var preview = '<div class="image image_' + i + '" data-id="' + fileId + '">' +
                        '<div class="img"><img src="' + e.target.result + '"></div>' +
                        '<button class="btn remove">Remove</button>' +
                        '</div>';
                    images.append(preview);

                    baseFile = reader.result;

                    baseFiles.push({
                        id: fileId,
                        name: file.name,
                        file: baseFile,
                        uploaded: 0
                    });
                }

                reader.readAsDataURL(file);
            })(files[i]);
        }

        console.log('after upload :>> ', baseFiles);
    });
    $(document).on('click', '.remove', function (e) {
        e.preventDefault();

        var parent = $(this).parent();
        var id = parent.data('id');
        var removeIndex = baseFiles.map(function (item) {
            return item.id;
        }).indexOf(id);

        baseFiles.splice(removeIndex, 1);
        parent.remove();
        console.log('after remove :>> ', baseFiles);
        // var myJSON = JSON.stringify(baseFiles);
        // $('.files-text').val(myJSON);
    });


    function gestionDesFichiers()
    {
        togglePopUp(popupGestionFichiersBon);
        $('#add_fichier').trigger('click');
    }

    function fermeturePopupGestionFichiersBon()
    {
        // On vide l'ancienne liste des fichiers pour éviter de les afficher en double
        $('#fichiers_deja_joints2').html('');

        togglePopUp(popupGestionFichiersBon);

        // On affiche tous les fichiers selectionné pour le bon
        $('#popupGestionFichiersBon input[type="file"]').each(function()
        {
            var tab_index_fichier = this.id.split('_');
            var index_fichier = tab_index_fichier[tab_index_fichier.length - 2];
            var tab_nom_fichier = $('#' + this.id)[0]['value'].split('\\');
            var nom_fichier = tab_nom_fichier[tab_nom_fichier.length - 1];

            if (nom_fichier != '')
            {
                $('#fichiers_deja_joints2').append("<div class='add-list__added' id='encartFichierBA_" + index_fichier + "'>" + nom_fichier + "<div class='close-cross' onClick='retireBAFile(" + index_fichier + ");'></div></div>");
            } else {
                // Si il n'y a pas de nom de fichier on supprime le champs (simulation de click sur lien suppression
                var id_encart_a_supp = '#' + id_lien_fichiersPdf + '_' + index_fichier;
                $(id_encart_a_supp + ' + a').trigger('click');
            }
        });
    }

    // Click sur le bouton RETOUR de la popup nouveau site
    function retourPopupSiteBA()
    {
        // On retire les encart rouge
        $('#site_ba_intitule').removeClass('erreur_formulaire');
        $('#site_ba_adresse').removeClass('erreur_formulaire');

        togglePopUp(bonsSite);
        // Simulation click sur le premier lien supprimer pour enlever l'encart nouveau contact
        $('#container_new_contact a.mini_link').trigger('click');

        $('#site_ba > fieldset').removeClass('cacher');
        $('#ajout_fichier_siteBA_mobile_portrait').removeClass('cacher');
        $('#contacts_deja_associes').removeClass('cacher');

        //$('#titre_nouveau_siteBA').text('Nouveau Site');
        if ($('#' + id_select_site).val() === '')
        {
            $('#titre_nouveau_siteBA').text('Nouveau site');
        } else {
            $('#titre_nouveau_siteBA').text('Modification du site');
        }
    }

    var sauvegarde_select_user = $('#' + id_select_intervenant).html();
    // Envoi du service selectionné
    // Récupération des ids des membres du service
    // Cache les non membre du service du select html
    $('#' + id_select_service).change(function()
    {
        // On sauvegarde le nom de l'intervenant selectionné
        var intervenant = $('#' + id_select_intervenant).val();

        // Lors d'un changement de choix de service : On redéfini le select d'origin pour supprimer toutes les classes cacher
        $('#' + id_select_intervenant).html(sauvegarde_select_user);

        var intervenant_identique = false;
        $.ajax("{{ path('lci_ajax_bons_select_service') }}",
        {
            async: false,
            method: "POST",
            data: {service:$('#' + id_select_service + ' option:selected').text() },
            success: function(data, textStatus, jqXHR) {
                // Lors du retour de la demande Ajax, soit on récupère un tableau avec les id des membres du service soit on recoit une chaine de caractère vide
                // Si on recoit la chaine de c. on ne fait pas de changement au tableau des intervenants
                if (data != '') {
                    var t_data = JSON.parse(data);
                    $('#' + id_select_intervenant + ' option').each(function()
                    {
                        if (($.inArray(parseInt($(this).prop('value')), t_data) == -1) && ($(this).prop('value') != ''))
                        {
                            $(this).prop('disabled', 'disabled');
							$(this).addClass('disabled');
                        } else {
                            // On regarde si l'intervenant précédemment selectionné se trouve dans la liste des intervants du service selectionné
                            // Si oui on le reselectionnera
                            if ($(this).val() == intervenant)
                            {
                                intervenant_identique = true;
                            }
                        }
                    });
                    if (intervenant_identique == true)
                    {
                        $('#' + id_select_intervenant).val(intervenant);
                    } else {
                        // Selection de l'option par défaut pour l'intervenant (Choix de l'intervenant)
                        $('#' + id_select_intervenant + ' option[value=""]').prop('selected', true);
                    }
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('error');
            }
        });
    });


    function checkValidationBeforeSend()
    {
		resetCheck();

        var $send_form = true;

        // On vérifie la selection d'un site
        if ($('#' + id_select_site).val() === '')
        {
            $('#' + id_select_site).addClass('erreur_formulaire');
            $send_form = false;
        } else {
			
		}

        // On vérifie l'ajout du numero d'affaire
        if ($('#' + id_text_numeroAffaire).val() === '')
        {
            $('#' + id_text_numeroAffaire).addClass('erreur_formulaire');
            $send_form = false;
        } else {
            /*
                Valeures acceptées
            	CXXX
            	DXXX
            	VXXX
            	PLXXX
            	PCXXX
            	SALXXX
            	SACXXX
            	COLXXX
            	COCXXX
            	GXXX
            */
			// Mise en majuscule de l'affaire 
			var $tmp_numero_affaire = $('#' + id_text_numeroAffaire).val().toUpperCase();
			$('#' + id_text_numeroAffaire).val($tmp_numero_affaire);

			// Vérification des caractères autorisés
			var $is_ok =  $tmp_numero_affaire.match(/^([CDGV]|PL|PC|SAL|SAC|COL|COC)\d\d\d$/);
       	 	if ($is_ok == null)
        	{
				$('#' + id_text_numeroAffaire).addClass('erreur_formulaire');
			//	$('#' + id_text_numeroAffaire).val('Formats acceptés : CXXX, DXXX, GXXX, VXXX, PLXXX, PCXXX, SALXXX, SACXXX, COLXXX, COCXXX').toLowerCase();
            	$send_form = false;
			} 
				
		}

        // On vérifie la selection d'un contact
        if ($('#select_contact').val() === '')
        {
            $('#select_contact').addClass('erreur_formulaire');
            $send_form = false;
        }

        // On vérifie la présence du motif de création du ticket
        if ($('#ticket_incident_motif').val() === '')
        {
            $('#ticket_incident_motif').addClass('erreur_formulaire');
            $send_form = false;
        }


        // On vérifie que pour le contact selectionné, il y a soit un email soit un téléphone
        if (($('#' + id_champs_mail_du_contact).val() === '') && ($('#' + id_champs_tel_du_contact).val() === ''))
        {
            $('#' + id_champs_mail_du_contact).addClass('erreur_formulaire');
            $('#' + id_champs_tel_du_contact).addClass('erreur_formulaire');
            $send_form = false;
        }

        if ($send_form === true)
        {
            attendreRechargement();

            // On indique que le formulaire du bon sera envoyé pour être sauvegardé en base
            $('#enregistrement').val("oui");

            document.forms['myForm'].submit();
        }
    }

    function resetCheck()
    {
        $('#' + id_select_site).removeClass('erreur_formulaire');
        $('#' + id_text_numeroAffaire).removeClass('erreur_formulaire');
        $('#select_contact').removeClass('erreur_formulaire');
        $('#' + id_champs_mail_du_contact).removeClass('erreur_formulaire');
        $('#' + id_champs_tel_du_contact).removeClass('erreur_formulaire');
        $('#ticket_incident_motif').removeClass('erreur_formulaire');
    }



    function checkValidationContactBeforeSend()
    {
        var $send = true;

        if ($('#nomContact').val() === '')
        {
            $('#nomContact').addClass('erreur_formulaire');
            $send = false;
        }
        if ($('#prenomContact').val() === '')
        {
            $('#prenomContact').addClass('erreur_formulaire');
            $send = false;
        }
        if (($('#emailContact').val() === '') && ($('#telephoneContact').val() === ''))
        {
            $('#emailContact').addClass('erreur_formulaire');
            $('#telephoneContact').addClass('erreur_formulaire');
            $send = false;
        }
        if ($('#fonctionContact').val() === '')
        {
            $('#fonctionContact').addClass('erreur_formulaire');
            $send = false;
        }

        return $send;
    }


/******************************         FONCTIONS POUR LES FICHIERS         ***************************************/


    // Fonction de suppression d'un fichier d'un siteBA
    function supprimeFile($idFile)
    {
        $.ajax({
            url: "{{ path('lci_ajax_siteBA_del_file') }}",
            data: {'id_file':$idFile },
            success: function(msg)
            {
                console.log('fichier du site supprimé');
            },
            error: function(status, msg, tri) {
                alert('error ' + msg + tri);
            }
        });
    }



    function retireBAFile($ind)
    {
        var id_lien_suppression = '#' + id_lien_fichiersPdf + '_' + $ind;
        // On retire les fichiers du bon
        $(id_lien_suppression + ' + a').trigger('click');
        // On retire le fichier de l'affichage HTML
        $('#encartFichierBA_' + $ind).remove();
    }



/******************************         FONCTIONS POUR LES CONTACTS         ***************************************/
    function creerContact2()
    {
        var $send_form = true;

		gestionCaracteresContact();

        // Vérification des champs du contact
        if($('#contact_nom').val() === '')
        {
            $('#contact_nom').addClass('erreur_formulaire');
            $send_form = false;
        }
        if( ($('#contact_telephone').val() === '') && ($('#contact_mail').val() === '') )
        {
            $('#contact_telephone').addClass('erreur_formulaire');
            $('#contact_mail').addClass('erreur_formulaire');
            $send_form = false;
        }
        if($('#contact_fonction').val() === '')
        {
            $('#contact_fonction').addClass('erreur_formulaire');
            $send_form = false;
        }
		

        // Si la vérification est ok (pas de champs obligatoire vides) : Envoi du formulaire
        if ($send_form === true)
        {
            attendreRechargement();

            $.ajax({
                url: "{{ path('lci_ajax_siteba_new_contact') }}",
                method: "POST",
                data: $('form[name="contact"]').serialize(),
                success: function(output, status, xhr)
                {
                    try {
                        // On recoit une réponse ajax : Le contact a été enregistré
                        data = JSON.parse(output);


                        // Remplissage des champs indiquant la création d'un nouvel objet
                        var $id_nouveau_contact = data['message'];
                        $('#' + id_champs_nouveau_id_site).val($id_nouveau_contact);
                        $('#' + id_champs_nouveau_type).val('contact');
                        // Recherche de l'id du site du nouveau contact
                        $('#' + id_select_site + ' option').each(function()
                        {
                            if($(this).text() == $('#contact_siteBA option:selected').text())
                            {
                                $('#' + id_champs_nouveau_nom_site).val($(this).val());
                                return false;
                            }
                        });


                        // On reset le formulaire contact
                        $('#contact_reset').trigger('click');

                        // On ferme la popup contact
                        retourPopupContact2();

                        // Rechargement de la page
                        document.forms['myForm'].submit();
                    } catch(e) {
                        finAttendreRechargement
                        // On ne recoit pas une réponse Ajax : on recoit donc le formulaire HTML avec les erreurs
                        var form_html = output;
                        $('#popupContact2').html(form_html);
                    }
                },
                error: function (xhr, status, st_text)
                {
                    finAttendreRechargement();
                    alert('error');
                }
            });
        }
    }


    function gestionDesContacts2()
    {
        // Si la selection du select de site n'est pas vide on affiche le formulaire en lui définissant le même site que celui selectionné
        if ($('#' + id_select_site).val() != '')
        {
            $("#contact_siteBA option[value='" + $('#' + id_select_site).val() + "']").prop('selected', true);
        }

        // Si le select de contact n'est pas vide on recherche les informations liés au contact pour pré remplir le formulaire de contact
        if ($('#select_contact').val() != '')
        {
            $('#select_contact').trigger('change');
        }
        togglePopUp(popupContact2);
    }


    function resetInfosContact()
    {
        /* On réinitialise les valeurs de la fiche client et du contact associé au bon */
        $('#contact_id').val('');
        $('#contact_nom').val('');
        $('#contact_prenom').val('');
        $('#contact_mail').val('');
        $('#contact_telephone').val('');
        $('#contact_fonction').val('');
        $('#' + id_champs_nom_du_contact).val('');
        $('#' + id_champs_mail_du_contact).val('');
        $('#' + id_champs_tel_du_contact).val('');

        $($("#bloc_contact_pour_impression")).html('');
    }
    function retourPopupContact2()
    {
        $('#contact_nom').removeClass('erreur_formulaire');
        $('#contact_telephone').removeClass('erreur_formulaire');
        $('#contact_mail').removeClass('erreur_formulaire');
        $('#contact_fonction').removeClass('erreur_formulaire');

        $('#contact_reset').trigger('click');

        togglePopUp(popupContact2);
    }


    function modifierContact()
    {
        if (checkValidationContactBeforeSend() === true)
        {
            // Fermeture de la popup contact
            togglePopUp(bonsNouveauContact);

            var $refreshContact;
            $url_modif          = "{{ path('lci_ajax_bon_contact_modif') }}";
            $id_contact_modif   = $('#idContact').val();
            $nom                = $('#nomContact').val();
            $prenom             = $('#prenomContact').val();
            $email              = $('#emailContact').val();
            $tel                = $('#telephoneContact').val();
            $fonction           = $('#fonctionContact').val();

            // On sauvegarde la valeur de l'option du contact selectionné pour le reselectionner après modification
            $opt_select_contact_val = $('#select_contact').val();
            $.ajax({
                url: $url_modif,
                method: "post",
                data: {'id_contact_modif':$id_contact_modif, 'nomContact':$nom, 'prenomContact':$prenom, 'telephoneContact':$tel, 'emailContact':$email, 'fonctionContact':$fonction},
                success: function(msg)
                {
                    // Après la modification d'un contact on réaffiche le site
                    $('#' + id_select_site).trigger('change');
                }
            });
        }
    }
    function supprimerContact()
    {
        $url_supp = "{{ path('lci_ajax_bon_contact_supp') }}";
        $id_contact_supp = $('#idContact').val();
        $.ajax({
            url: $url_supp,
            method: "post",
            data: {'id_contact_supp':$id_contact_supp},
            success: function(msg)
            {
                $('#' + id_select_site).trigger('change');
            }
        });
    }

    function ficheContactPourImpression($index_tab_contact)
    {
        var $container = $($("#bloc_contact_pour_impression"));
        var $html = '';
        if (($tab_contact[$index_tab_contact]['prenom'] != '') && ($tab_contact[$index_tab_contact]['prenom'] !=  null) && ($tab_contact[$index_tab_contact]['prenom'] != '-'))
        {
            $html += capitalize($tab_contact[$index_tab_contact]['prenom']) + ' ';
        }
        $html += capitalize($tab_contact[$index_tab_contact]['nom']);
        if (($tab_contact[$index_tab_contact]['email'] != '') && ($tab_contact[$index_tab_contact]['email'] != null))
        {
            $html += ' - ' + $tab_contact[$index_tab_contact]['email'];
        }
        if (($tab_contact[$index_tab_contact]['telephone'] != '') && ($tab_contact[$index_tab_contact]['telephone'] != null))
        {
            $html += ' : ' + $tab_contact[$index_tab_contact]['telephone'];
        }
        $html += " ( " + $tab_contact[$index_tab_contact]['fonction'] + " )<br />";
        $container.append($html);
    }

    // Fonction qui ajoute un champs 'Contact au site BA'
    function ajoutChampContactSiteBA()
    {
        // L'identifiant du nouveau champs sera : site_ba_contacts_$indexContactSiteBA_date_maj
        var $nouveauPrototype = $($('#site_ba_contacts').data('prototype').replace(/__name__label__/g, '').replace(/__name__/g, $indexContactSiteBA));

        // Création et ajout d'un lien de suppression en fin de container
        $deleteLink = $('<a href="#" class="mini_link">Supprimer</a>');
        $nouveauPrototype.append($deleteLink);

        // Ajout du listener permettant la suppression du champ fichier lors du clic sur le lien
        $deleteLink.click(function(e)
        {
            $nouveauPrototype.remove();
            e.preventDefault();
            return false;
        });

        // On place le nouveau champs input
        $container_new_contact = $('#container_new_contact');
        $container_new_contact.prepend($nouveauPrototype);

        // On indique les valeur par défaut des champs obligatoire
        var $today = new Date();
        var $val_today = $today.getDate() + '/' + ($today.getMonth() + 1) + '/' +  $today.getFullYear();
        $('#site_ba_contacts_' + $indexContactSiteBA + '_date_maj').val($val_today);
        $('#site_ba_contacts_' + $indexContactSiteBA + '_fonction').val('Contact');
        $('#site_ba_contacts_' + $indexContactSiteBA + '_prenom').val('-');

        // On met en place l'activation js pour l'affichage du calendrier
        setDatePicker();

        // Ajout du prototype a la fin du container
        //$container_contact.prepend($nouveauPrototype);
        // Incrémentation de l'index des contacts
        $indexContactSiteBA ++;
    }

    $("#add-fichierBon").on("click", () => {
        $("#fichierBon").click();
    });
    $("#add-equipBon").on("click", () => {
        $("#equipBon").click();
    });

    // On change l'icône du bouton et le texte de l'infobulle du bouton lié au select
    $('#' + id_select_site).on('change', function editButton(){
        // Si la valeur est différente de celle par défaut
        if ($('#' + id_select_site).val() != ''){
            // On change l'image par un crayon
            $('#' + id_select_site).next(".btn--main").addClass('edit');
            // On modifie l'infobulle par "Modifier"
            $('#' + id_select_site).next(".btn--main").find('.tooltip').html('Modifier Site');
        } else {
            $('#' + id_select_site).next(".btn--main").removeClass('edit');
            $('#' + id_select_site).next(".btn--main").find('.tooltip').html('Créer Site')
        }
    });
    // Même chose pour les contacts
    $('#select_contact').on('change', function editButton(){
        // Si la valeur est différente de celle par défaut
        if ($('#select_contact').val() != ''){
            // On change l'image par un crayon
            $('#select_contact').next(".btn--main").addClass('edit');
            // On modifie l'infobulle par "Modifier"
            $('#select_contact').next(".btn--main").find('.tooltip').html('Modifier contact');
        } else {
            $('#select_contact').next(".btn--main").removeClass('edit');
            $('#select_contact').next(".btn--main").find('.tooltip').html('Créer contact')
        }
    });
    // Fonction pour changer la bordure de la barre de recherche en focus
    $(".search-bar__input").on('focus', function searchFocus(){
        if(!$(this).parents(".search-bar").hasClass('search-bar--focus')){
            $(this).parents(".search-bar").addClass("search-bar--focus");
        }
    });
    $(".search-bar__input").on('blur', function searchBlur(){
        if($(this).parents(".search-bar").hasClass("search-bar--focus")){
            $(this).parents(".search-bar").removeClass("search-bar--focus");
        }
    });




    function gestionCaracteresContact()
    {
		$('#contact_nom').val($('#contact_nom').val().toUpperCase());
        $('#contact_prenom').val(capitalize($('#contact_prenom').val().toLowerCase()));
        $('#contact_fonction').val(capitalize($('#contact_fonction').val().toLowerCase()));
    }



    $('#bons_attachement_modification_user').change(function(e) {
        togglePopUp(commentaireChangeIntervenant);
    });

</script>
