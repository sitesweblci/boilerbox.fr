<script type='text/javascript'>
    $(document).ready(function()
    {
        // On définit l'id de l'intervenant pour le retour arrière lorsqu'aucun message de motif de changement d'intervenant n'est indiqué
        $('#last_id_intervenant').val($('#' + id_select_intervenant).val());

		/* Désactivation de la checkbox Archive */
        $('#chk_archive').attr('checked', false);

        $('#ticket_incident_validation_validationIntervention_valide').click(function(e){
            e.preventDefault;
            $sens = $('#ticket_incident_validation_validationIntervention_valide').is(":checked");
            $type = 'intervention';
            changeValidation($type, $sens);
        });
        $('#ticket_incident_validation_validationCloture_valide').click(function(e){
            e.preventDefault;
            $sens = $('#ticket_incident_validation_validationCloture_valide').is(":checked");
            $type = 'cloture';
            togglePopUp(commentaireCloture);
        });

        $('#bons_attachement_validation_validationTechnique_valide').click(function(e){
            e.preventDefault;
            $sens = $('#bons_attachement_validation_validationTechnique_valide').is(":checked");
            $type = 'technique';
            changeValidation($type, $sens);
        });
        $('#bons_attachement_validation_validationPiece_valide').click(function(e){
            e.preventDefault;
            $sens = $('#bons_attachement_validation_validationPiece_valide').is(":checked");
            $type = 'pieces';
            changeValidation($type, $sens);
        });
        $('#bons_attachement_validation_validationPieceFaite_valide').click(function(e){
            e.preventDefault;
            $sens = $('#bons_attachement_validation_validationPieceFaite_valide').is(":checked");
            $type = 'pieces_faite';
            changeValidation($type, $sens);
        });
        $('#bons_attachement_validation_validationSAV_valide').click(function(e){
            //e.preventDefault;
            $sens = $('#bons_attachement_validation_validationSAV_valide').is(":checked");
            $type = 'sav';
            togglePopUp(commentaireSAV);
        });
        $('#bons_attachement_validation_validationFacturation_valide').click(function(e){
            e.preventDefault;
            $sens = $('#bons_attachement_validation_validationFacturation_valide').is(":checked");
            $type = 'facturation';
            changeValidation($type, $sens);
        });

        $("#bons_attachement_validation_dateSignature" ).datepicker();




        /* Récupération du container contenant l'attribut data-prototype */
        $container = $('#' + id_lien_fichiersPdf);

        /* Calcul du nombre de fichiers déjà présent */
        // On définit un compteur unique pour nommer les champs qu'on va ajouter dynamiquement
        $indexFichier = $container.find(':input').length;

        // Si le nombre de fichier est >= 1 on enlève les champs Parcourir inutiles

        /* Lien permettant l'ajout d'un nouveau fichier */
        var $addLink = $('<a href="#" id="add_fichier" class="small_link">Ajouter un fichier ({{ max_upload_size }} max)</a>');

        /* Ajout du lien dans la div du container */
        $('#lien_ajout_fichier').html($addLink);

        /* Ajout d'un listener pour créer un nouveau champs lors du clic sur le lien */
        $addLink.click(function(e){
            ajoutChampFichier();
            $('#' + id_lien_fichiersPdf + '_' + ( $indexFichier - 1 ) + '_file').trigger("click");
            $('#js-btn-file > div').removeClass('main-box__btn--disabled');
            $('#js-btn-file .btn--main').removeClass('btn--disabled');
            e.preventDefault();
            return false;
        });

        // Si le nombre de fichier est >= 1 on enlève les champs Parcourir inutils
        $('#formulaire_bons_fichiers div.form-group').addClass('cacher');


        /* Modification des infos bulles indiquant l'affichage des encarts fichiers et commentaires */
        $('#label_fichier').mouseover(function(e) {
            $('#info_bulle_fichier').removeClass('cacher');

        });
        $('#label_fichier').mouseout(function(e) {
            $('#info_bulle_fichier').addClass('cacher');

        });


        // ************************************************************************************ LISTENERS
        // Lors du click sur Chemin des photos / Récupération d'un fichier bat avec l'acces vers le dossier des photos du bon
        $('#cheminDossierPhotos').dblclick(function()
        {
            var $id_bon = $('#' + id_champs_last_id_site).val();

            var $url_controller_ajax_has_url    = "{{ path('lci_bons_has_url', {'id_bon': 'identifiantDuBon'}) }}";
            $url_controller_ajax_has_url        = $url_controller_ajax_has_url.replace('identifiantDuBon', $id_bon);

            $.ajax({
                url: $url_controller_ajax_has_url,
                method: "GET",
                success: function(msg){
                    var $retour = $.parseJSON(msg);
                    if ($retour['hasUrl'] == true)
                    {
                        // Appel de la fonction pour télécharment du .bat
                        var $url_controller_fonction_bat    = "{{ path('lci_get_fichier_bat', {'id_bon': 'identifiantDuBon'}) }}";
                        $url_controller_fonction_bat        = $url_controller_fonction_bat.replace('identifiantDuBon', $id_bon);
                        document.location = $url_controller_fonction_bat;
                    } else {
                        alert("Aucun dossier n'a été enregistré pour le bon");
                    }
                },
                error: function(){
                    alert('erreur serveur survenue lors de la recherche du dossier des photos');
                }
            });
        });

        refreshSelectEquipements();

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
                                $(this).addClass('cacher');
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

        setDatePicker();
	});

</script>
