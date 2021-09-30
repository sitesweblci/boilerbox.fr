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

    // Fonction qui définie la taille de l'écran afin que la div d'identifiant siteIpc couvre la totalité de l'écran
    function setSiteIpcDimension() {
        var hauteur = window.innerHeight + 'px';
        var largeur = window.innerWidth + 'px';
        $('#siteIpc').width(largeur);
        $('#siteIpc').height(hauteur);
    }

    // Affichage de l'image du loader pour indiquer que la page est en cours de chargement
    function attente() {
        $(':visible').addClass('cursor_wait');
    }

    function fin_attente() {
        $(':visible').removeClass('cursor_wait');
    }
</script>
