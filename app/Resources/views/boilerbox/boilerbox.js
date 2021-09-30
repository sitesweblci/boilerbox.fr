<script type='text/javascript'>
$(document).ready(function() {
        // Inhibition du raffraichissement par F5
        $(document).keydown(function(e){
            if (e.which == 116) {
                return false;
            } else if (e.which == 27) {
                $('#lienDeconnexion').get(0).click();
            }
        });
    });

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


    function redirection($from) {
        var $url;
        switch($from) {
			case 'menuGestionParcModules' :
				if (document.location.pathname.substr(-9) == 'problemes') {
					$url = $('#liens').attr('data-parcModules');
				} else {
					$url = $('#liens').attr('data-retourMenu');
				}
				break;
            case 'menuGestionParcEquipements' :
                if (document.location.pathname.substr(-9) == 'problemes') {
                    $url = $('#liens').attr('data-parcEquipements');
                } else {
                    $url = $('#liens').attr('data-retourMenu');
                }
                break;
            default:
                var $lien = 'data-' + $from;
                $url = $('#liens').attr($lien);
                break;
        }
        $(location).attr('href', $url);
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
</script>
