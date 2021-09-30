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

    function redirection($page) {
        var url;
        switch($page) {
			case 'menuGestionParc' :
				if (document.location.pathname.substr(-9) == 'problemes') {
					url = $('#liens').attr('data-parcModules');
				} else {
					url = $('#liens').attr('data-retourMenu');
				}
				break;
            default:
                var $lien = 'data-' + $page;
                url = $('#liens').attr($lien);
                break;
        }
        $(location).attr('href', url);
    }

    // Affichage de l'image du loader pour indiquer que la page est en cours de chargement
    function attente() {
        $(':visible').addClass('cursor_wait');
    }

    function fin_attente() {
        $(':visible').removeClass('cursor_wait');
    }

</script>