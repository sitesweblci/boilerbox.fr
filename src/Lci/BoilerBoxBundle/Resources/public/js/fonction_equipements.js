var tabDesEquipementsSelectionnes = [];

$(document).ready(function() {
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
                                                onClick=\"deplaceEquipement('{{ e_equipement.id }}')\" \
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
});


    function gestionDesSelectionEquipements()
    {
        // Si la selection du select de site n'est pas vide on affiche le formulaire en lui définissant le même site que celui selectionné
        if ($('#bons_attachement_site').val() != '')
        {
            $("#select_equipement").val($('#bons_attachement_site').val());
        }
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
            console.log('refresh');
        console.log($('#bons_attachement_modification_site').val());
        // On défini le même site sur le select des équipements que le site sélectionné
        $('#select_equipement').val($('#bons_attachement_modification_site').val());
        console.log($('#select_equipement').val());

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
                document.forms['myForm'].submit();
            },
            error: function(status, msg, tri) {
                finAttendreRechargement();
                alert('error ' + msg + tri);
            }
        });
    }

    function deplaceEquipement(id_checkbox)
    {
        var id_div = "div_equipement_" + id_checkbox;
        var div_tmp = $("#" + id_div);

        // Selection d'un équipement
        if ($('#' + id_div + ' input[type="checkbox"]').is(':checked'))
        {
            // On ajout d'id de l'equipement au tableau des équipements selectionnés
            tabDesEquipementsSelectionnes.push(id_checkbox);
            // On retire l'element du div principal
            div_tmp.remove();

            // On ajoute l'élément au div de selection
            $('#liste_des_equipements_selectionnes').append(div_tmp);
        // Dé selection d'un équipement
        } else {
            // On retire l'id de l'équipement du tableau des équipement selectionnés
            var indexARetirer = tabDesEquipementsSelectionnes.indexOf(id_checkbox);
            tabDesEquipementsSelectionnes.splice(indexARetirer, 1);

            // On retire l'element du div de selection
            div_tmp.remove();

            // On ajoute l'élément au div principal dans son conteneur parent
            $('#parent_' + id_div).append(div_tmp);
        }
    }
