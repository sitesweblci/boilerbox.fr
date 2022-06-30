<script 'type=text/javascript'>
    $(document).ready(function()
    {
        attendreRechargement();

        // Récupération du container des fichiers des SitesBA
        $container_fichiers_site = $('#encart_fichiers_siteBA'); //$('#site_ba_fichiersJoint');
        // Récupération du container des contacts des SitesBA
        $container_contacts_site = $('#site_ba_contacts');


        /* Lien permettant l'ajout d'un nouveau contact au site */
        var $addLinkContact = $('<a href="#" id="add_contact" class="small_link">Ajouter un contact</a>');
        /* Lien permettant l'ajout d'un nouveau fichier au bon */
        var $addLinkFichierBon = $('<a href="#" id="add_fichier" class="small_link">Ajouter un fichier</a>');
        /* Lien permettant l'ajout d'un nouveau fichier au site */
        var $addLinkFichierSiteBA = $('<a href="#" id="add_fichier_site" class="small_link">Ajouter un fichier</a>');


        /* Ajout du lien dans la div du container */
        $('#lien_ajout_fichier').html($addLinkFichierBon);
        /* Ajout du lien dans la div du container de fichiers des sites */
        $('#lien_ajout_fichier_siteBA').html($addLinkFichierSiteBA);
        /* Ajout du lien dans la div du container de contact des sites */
        $('#lien_ajout_contact').html($addLinkContact);



        /* Ajout d'un listener pour créer un nouveau champs lors du clic sur le lien */
        $addLinkFichierBon.click(function(e){
            ajoutChampFichier();
            e.preventDefault();
            // On click sur le lien 'Parcourir' pour afficher la fenetre de recherche du fichier
            $('#' +  id_lien_fichiersPdf + '_' + ( $indexFichier - 1 ) + '_file').trigger("click");
            return false;
        });

        /* Ajout d'un listener pour création d'un champs lors du clic sur le lien des fichiers des sitesBA */
        $addLinkFichierSiteBA.click(function(e){
            ajoutChampFichierSiteBA();
            e.preventDefault();
            $('#site_ba_fichiersJoint_' + ( $indexFichierSiteBA - 1 ) + '_file').trigger("click");
            return false;
        });

        /* Ajout d'un listener pour création d'un champs contact lors du clic sur le lien des contacts */
        $addLinkContact.click(function(e){
            ajoutChampContactSiteBA();
            e.preventDefault();
            $('#site_ba_contacts_' + ( $indexContactSiteBA - 1 ) + '_nom').trigger("click");
            return false;
        });


        // Gestion du select intervenant
        $('#' + id_select_intervenant).change(function()
        {
            if ($('#' + id_select_intervenant).val() !=  null)
            {
                $('#' + id_select_intervenant).removeClass('erreur_formulaire');
            }
        });



        // Gestion du select site
        /* Affichage des informations du site selectionné dans l'encart SiteBA */
        $('#' + id_select_site).change(function()
        {
            initAutocomplete();

            // Réinitialisation des informations concernant les sites
            $('#site_ba_reset').trigger('click');
            resetInfosSite();

            // Retire des encart rouge indiquant les champs manquants pour l'envoi du formulaire site
            resetCheck();

            // On réinitialise les valeurs de la fiche client et du contact associé au bon
            resetInfosContact();

            // On réinitalise le select contact
            $('#select_contact').html("<option value=''></option>");

            // On lance le raffraichissement de la popup Equipement
            refreshSelectEquipements();

            // SI UN SITE EST SELECTIONNE
            // Appel ajax pour obtenir les informations liées au site
            // + Affichage du bouton de création/modification de contact
            if ($('#' + id_select_site).val() != '')
            {
                // On retire l'encart rouge indiquant qu'un site est necessaire pour l'envoi du formulaire
                $('#' + id_select_site).removeClass('erreur_formulaire');
                // Affichage du bouton permettant la création/modification de contact
                $('#btn_contact').removeClass('cacher');
				// Modification du titre
                $('#titre_nouveau_siteBA').text('Modification du site');

                var $id_site_ba = this.value;
                var $url = "{{ path('lci_ajax_bons_get_siteBA') }}";
                $.ajax({
                    url: $url,
                    method: "post",
                    data: {'id_site_ba':$id_site_ba},
                    success: function(msg)
                    {
                        var siteBA = $.parseJSON(msg);

                        $('#id_site_ba').val(siteBA.id);
                        $('#site_ba_intitule').val(siteBA.intitule);
                        {# $('#site_ba_intitule').attr('readonly', true); // On autorise le changement du nom du site #}
                        $('#site_ba_adresse').val(siteBA.adresse);
                        $('#site_ba_informationsClient').val(siteBA.informationsClient);


                        $.ajax({
                            url: "{{ path('lci_ajax_bon_get_googlemap') }}",
                            method: "POST",
                            data: {'id_site_ba':$id_site_ba},
                            success: function(urlGoogleMap)
                            {
                                $('#site_ba_lienGoogle').val(urlGoogleMap);
                                // Affichage de la map google
                                $('#site_ba_lienGoogle').trigger('change');
                                // Placement du marqueur sur la page
                                ajoutMarqueur($('#site_ba_lienGoogle').val());
                            },
                            error: function()
                            {
                                alert('error map google');
                            }
                        });

                        // On ré initialise le select contact en indiquant la possiblité de création d'un contact
                        html_option = "<option value=''>Ajouter un nouveau contact</option>";
                        $.each(siteBA.contacts, function(index, contact)
                        {
                            // ICI DEV ficheContactPourImpression(index);
                            $html = '<p>';
                            $html = $html + contact.nom + '</p>';
                            $('#contacts_deja_associes').append($html);
                            html_option += "<option value='" + contact.id + "'>" + contact.nom + "</option>";
                        });
                        $('#select_contact').html(html_option);

                        // On reselectionne le contact précédemment selectionné
                        {% if id_last_site is not null %}
                            if($('#' + id_champs_nom_du_contact).val() != '')
                            {
                                $('#select_contact option:contains("' + $('#' + id_champs_nom_du_contact).val() + '")').prop('selected', true);
                                $('#select_contact').trigger('change');
                            }
                        {% endif %}

						/* 
                        // Ajout des noms des fichiers précédemment joints au site
                        $.each(siteBA.fichiersJoint, function(index, fichier) {
                                $('#fichiers_deja_joints').append(fichier.alt + '<br />');
                                $('#fichiers_deja_joints2').append("<div class='add-list__added " + fichier.id + "'>" + fichier.alt + "<div class='close-cross' onClick='supprimeFile(" + fichier.id + ");'></div></div>");
                        });
						*/

                        // Ajout des équipements précédemment joints au site
                        $.each(siteBA.equipementBATickets, function(index, equipement) {
                                $('#equipements_deja_joints').append(equipement.denomination + '<br />');
                        });

                        // Si on est dans l'action de changement de site suite au chargement de la page
                        if ($chargementEnCours == true)
                        {
                            // On re sélectionne les équipements précédemment selectionné après l'envoi sans sauvegarde du formulaire de bon
                            {% if tab_des_id_equipements_selectionnes | length != 0 %}
                                // On affiche tous les équipements pour re sélectionner les équipements sélectionnés avant l'envoi du formulaire
                                $('#select_equipement').val('');

                                // Remplissage du html de la popup equipement
                                $('#select_equipement').change();

                                {% for id_equipement in tab_des_id_equipements_selectionnes %}
                                    // On check les checkbox des equipements
                                    $('#equipement_' + "{{ id_equipement }}").prop('checked', 'checked');

                                    // Déplacement des équipements selectionnés
                                    setTimeout(function() {
                                        $('#equipement_' + "{{ id_equipement }}").trigger('click');
                                    }, 2);
                                {% endfor %}
                            {% endif %}

                            // On affiche les équipements du site selectionné au chargement de la page
                            $('#select_equipement').change();


                            // Gestion des nouveaux contacts et nouveaux équipements a réafficher
                            if ($('#' + id_champs_nouveau_nom_site).val() != '')
                            {
                                // Selection du contact si un nouveau contact est crée pour le site courant
                                switch($('#' + id_champs_nouveau_type).val())
                                {
                                    case 'equipement':
                                        gestionDesSelectionEquipements();
                                        $('#select_equipement').val($('#' + id_champs_nouveau_nom_site).val());
                                        $('#select_equipement').trigger('change');
                                    break;
                                    case 'contact':
                                        if ($('#' + id_select_site).val() == $('#' + id_champs_nouveau_nom_site).val())
                                        {
                                            // On affiche le nouveau contact ssi il est crée pour le site courant du bon
                                            $('#select_contact').val($('#' + id_champs_nouveau_id_site).val())
                                            $('#select_contact').trigger('change');
                                        }
                                    break;
                                }
                                // On réinitialise les paramètres de Création
                                $('#' + id_champs_nouveau_type).val('');
                                $('#' + id_champs_nouveau_id_site).val('');
                                $('#' + id_champs_nouveau_nom_site).val('');
                            }
                            $chargementEnCours = false;
                        }
                        finAttendreRechargement();
                    },
                    error: function(status, msg, tri) {
                        if ($chargementEnCours == true)
                        {
                            $chargementEnCours = false;
                        }
                        finAttendreRechargement();
                        alert('error ' + msg + tri);
                    }
                });
            } else {
                // Si aucun site n'est selectionné : On réinitialise les informations de la popup site
                // + désactivation du bouton de création / modification de contact
                $('#site_ba_reset').trigger('click');
                $('#btn_contact').addClass('cacher');

                if ($chargementEnCours == true)
                {
                    $chargementEnCours = false;
                }
                finAttendreRechargement();

            }
        });

        /* Lors du clic sur reset on réautorise l'écriture du nom du site et on supprime la valeur de l'identifiant du site  pour éviter une mise à jour */
        $('#site_ba_reset').click(function()
        {
            /* On réinitialise les informations de la popup site */
            resetInfosSite();

            /* On réinitialise les valeurs de la fiche client et du contact associé au bon */
            resetInfosContact();

            /* On réinitalise le select contact */
            $('#select_contact').html("<option value=''></option>");
        });

        /******************************         READY POUR LES CONTACTS         ***************************************/

        // Gestion du select contact
        $('#select_contact').change(function()
        {
            // Réinitialisation des informations liées au contact
            resetInfosContact();

            // Gestion du texte du bouton d'ajout/modification du contact
            // + Gestion du titre de la popup Contact
            if ($('#select_contact').val() === '')
            {
                $('#select_contact + button > span').text('Créer un contact');
                $('#titre_nouveau_contact').text('Nouveau contact');
            } else {
                $('#select_contact + button > span').text('Modifier le contact');
                $('#titre_nouveau_contact').text('Modifier le contact');
            }

            // Affichage des informations concernant le contact sélectionné
            var $index_tab_contact = $('#select_contact').val();
            if ($('#select_contact').val() != '')
            {
                attendreRechargement();
                $.ajax({
                    url: "{{ path('lci_ajax_bon_get_infos_contacts') }}",
                    method: "post",
                    data: {'id_contact':$('#select_contact').val()},
                    success: function(data)
                    {
                        var contact = JSON.parse(data);
                        $('#contact_id').val(contact.id);
                        $('#contact_nom').val(contact.nom);
                        $('#contact_prenom').val(contact.prenom);
                        $('#contact_mail').val(contact.mail);
                        $('#contact_telephone').val(contact.telephone);
                        $('#contact_fonction').val(contact.fonction);
                        $('#' + id_champs_nom_du_contact).val(contact.nom);
                        $('#' + id_champs_mail_du_contact).val(contact.mail);
                        $('#' + id_champs_tel_du_contact).val(contact.telephone);
                        finAttendreRechargement();
                    },
                    error: function()
                    {
                        finAttendreRechargement();
                        alert('error');
                    }
                });
            }

            // On retire l'encart rouge qui indique que les champs sont necessaires pour l'envoi du formulaire
            if ($('#select_contact').val() != '')
            {
                $('#select_contact').removeClass('erreur_formulaire');
            }
            if ($('#' + id_champs_mail_du_contact).val() != '')
            {
                $('#' + id_champs_mail_du_contact).removeClass('erreur_formulaire');
            }
            if ($('#' + id_champs_tel_du_contact).val() != '')
            {
                $('#' + id_champs_tel_du_contact).removeClass('erreur_formulaire');
            }
        });

        /******************************          Actions faites au chargement de la page        ********************************************/

        var $chargementEnCours = true;

        /* Permet de selectionner le dernier site - permet de réafficher le site après l'ajout d'un contact */
        {% if id_last_site is defined %}
            var id_last_site = "{{ id_last_site }}";
            // Sauvegarde des informations du contact
            save_nom_contact  = $('#' + id_champs_nom_du_contact).val();
            save_tel_contact  = $('#' + id_champs_tel_du_contact).val();
            save_mail_contact = $('#' + id_champs_mail_du_contact).val();

            // On rédéfini le select précédemment selectionné dans le select
            $('#' + id_select_site).val(id_last_site);

            // On active le listener de changement de site courant
            $('#' + id_select_site).trigger('change');

            // On redéfini les informations du contact précédemment selectionné
            $('#' + id_champs_nom_du_contact).val(save_nom_contact);
            $('#' + id_champs_tel_du_contact).val(save_tel_contact);
            $('#' + id_champs_mail_du_contact).val(save_mail_contact);
        {% endif %}

        // Si la création d'un équipement est en erreur : affichage du formulaire
       	{% if echec_creation_equipement == true %}
       	    $('#btn_ajout_equipement2').trigger('click');
       	{% endif %}

		// Se trouve dans la fonction setDatePicker
        setDatePicker();
    });

</script>
