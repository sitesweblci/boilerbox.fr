<script type='text/javascript'>
// Javascript qui est pris en compte sans devoir repasser la commande asset:dump

	$(document).ready(function() 
	{
        // Inhibition du raffraichissement par F5
        $(document).keydown(function(e){
            if (e.which == 116) {
                return false;
            } else if (e.which == 27) {
                $('#lienDeconnexion').get(0).click();
            }
        });
    });

	// Raccourci pour écriture dans la console
	function c(val) {
	    console.log(val);
	}


    function getXHR() {
        var xhr;
        try {
            xhr = new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
            try {
                xhr = new ActiveXObject('Microsoft.XMLHTTP');
            } catch (e2) {
                try {   xhr = new XMLHttpRequest();
                } catch (e3) {
                    xhr = false;
                }
            }
        }
        return xhr;
    }


    function redirection(from, tab_parametre = null) 
	{
        var url;
        switch(from) {
			case 'menuGestionParcModules' :
				if (document.location.pathname.substr(-9) == 'problemes') {
					url = $('#liens').attr('data-parcModules');
				} else {
					url = $('#liens').attr('data-retourMenu');
				}
				break;
            case 'menuGestionParcEquipements' :
                if (document.location.pathname.substr(-9) == 'problemes') {
                    url = $('#liens').attr('data-parcEquipements');
                } else {
                    url = $('#liens').attr('data-retourMenu');
                }
                break;
			case 'saisie_ticket':
				url = "{{ path('saisie_ticket') }}";
				break;
	        case 'retourAccueil' :
	            // Le paramètre qui peut être passé correspond à l'identifiant du site à afficher dans la popup au lancement de la page d'accueil (page d'accueil = Liste des sites)
	            // Retour sur la page d'accueil : Liste des sites
	            url = Routing.generate('lci_boilerbox_accesSite');
	            if (tab_parametre) {
	                url_destination += '/' + tab_parametre[0];
	            }
	            break;
	        case 'modificationSite' :
	            url = Routing.generate('lci_site_update', {idSite: tab_parametre['id_site']});
	            break;
	        case 'creationSite' :
	            url = Routing.generate('lci_register_site');
	            break;
	        case 'accueilConf' :
	            // Retour sur la page des Paramètres BoilerBox
	            url = Routing.generate('lci_gestion_admin');
	            break;
	        case 'accueilConfUtilisateur' :
	            // Retour sur la page de Gestion des utilisateurs
	            url = Routing.generate('lci_accueil_register_user');
	            break;
            default:
                var lien = 'data-' + from;
                url = $('#liens').attr(lien);
                break;
        }
        $(location).attr('href', url);
		//window.location = url;
    }


	// Fonction qui retourne un texte avec un nombre de caractères max
	// ex : IN  -> ce_nom_de_fichier_est_trop_long
	//      OUT -> ce_nom_de_fichier...
	function decoupeTexte($texte, $nb_caracateres) {
	    if($texte.length > $nb_caracateres) {
	        $texte = $texte.substr(0, $nb_caracateres - 3);
	        $texte = $texte + '...';
	    }
	    return $texte;
	}


    // Affichage de l'image du loader pour indiquer que la page est en cours de chargement
    function attente() {
        $(':visible').addClass('cursor_wait');
    }

    function fin_attente() {
        $(':visible').removeClass('cursor_wait');
    }

    // Fonction executée après la validation d'un popup messagebox
    function validation_messagebox() {
		$('#messageboxTexte').text('');
        $('#messagebox').addClass('cacher');
        removeShadow('message');
        activateLinks();
    }

    function closeMessageBox() {
        if ($('#messagebox').hasClass('cacher') == false) {
            $('#messagebox').addClass('cacher');
            activateLinks();
            removeShadow('message');
        }
    }

    function activateLinks() {
        $('#pagePrincipaleBody a').each(function(){
            $(this).removeClass('btn-disable');
        });
    }

    function desactivateLinks() {
        // Lors de l'ouverture de la box, désactivation de la validation du login/mot de passe
        $('#pagePrincipaleBody a').each(function(){
            $(this).addClass('btn-disable');
        });
    }

    function removeShadow() {
		$('#siteIpc').off("click",closeMessageBox);
        $('#pagePrincipaleBody').removeClass('shadowing');
    }

	function afficheMenu(obj) {
		var idMenu     = obj.id;
		var idSousMenu = 'sous' + idMenu;
		var sousMenu   = document.getElementById(idSousMenu);
	
		/*****************************************************/
		/**	si le sous-menu correspondant au menu cliqué    **/
		/** est caché alors on l'affiche, sinon on le cache **/
		/*****************************************************/
		sousMenu.style.display = "block";
	}

	function cacheMenu(obj) {
		var idMenu = obj.id;
		var idSousMenu = 'sous' + idMenu;
		var objSousMenu = document.getElementById(idSousMenu);
		objSousMenu.style.display = "none";
	}


    function changeAjaxFromVar($fromVar){
        $.ajax({
            url: "{{ path('lci_ajax_change_fromVar') }}",
            method: "get",
            data: {fromVar:$fromVar}
        }).done(function(msg){
            /*alert('done ' + msg);*/
        });
    }


    function definirVariableProvenance($provenance) {
        $.ajax({
            url: "{{ path('lci_ajax_change_var_provenance') }}",
            method: "get",
            data: {provenance:$provenance},
            async: false,
            error : function(resultat, statut, erreur){
                alert('erreur ' + statut + erreur);
            },
            success : function(code_html, statut){
                /*alert('success ' + code_html);*/
            }
        });
    }


	// fonction qui soumet un formulaire symfony d'après l'identifiant du bouton submit du formulaire
	function soumettreLeFormulaire($id_bouton) {
	    $('button#' + $id_bouton).trigger('click');
	    return 0;
	}


	// Permet de faire les retours à la ligne des textes enregistrés dans des textarea vers des paragraphes
	function nl2br (str, isXhtml) {
	  // Some latest browsers when str is null return and unexpected null value
	  if (typeof str === 'undefined' || str === null) {
	    return ''
	  }
	  // Adjust comment to avoid issue on locutus.io display
	  var breakTag = (isXhtml || typeof isXhtml === 'undefined') ? '<br ' + '/>' : '<br>'
	
	  return (str + '')
	    .replace(/(\r\n|\n\r|\r|\n)/g, breakTag + '$1')
	}


	function capitalize($mot)
	{
	    if (typeof $mot !== 'string') return $mot;
	    return $mot.charAt(0).toUpperCase() + $mot.slice(1).toLowerCase();
	}


	function getTodayDate()
	{
	    var tdate = new Date();
	    var dd = tdate.getDate(); //yields day
	    var MM = tdate.getMonth(); //yields month
	    var yyyy = tdate.getFullYear(); //yields year
	    var currentDate= dd + "/" +( MM+1) + "/" + yyyy;
	    return currentDate;
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

    function capitalizeFirstLetter(string) {
        var string_sortie = '';
        var tab_string = string.split(/(\s+)/);
        $.each(tab_string, function(e){
            string_sortie += tab_string[e].charAt(0).toUpperCase() + tab_string[e].slice(1).toLowerCase();
        });
        return string_sortie;
    }

    // Active le datepicker sur tous les input ayant le placeholder à 'dd/mm/YYYY'
    function setDatePicker()
    {
        alert('set datepicker');
        $("input[placeholder='dd/mm/YYYY']").datepicker();
    }

</script>
