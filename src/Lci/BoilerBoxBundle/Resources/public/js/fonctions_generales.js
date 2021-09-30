// Raccourci pour écriture dans la console
function c(val) {
    console.log(val);
}


// Affichage de l'image du loader pour indiquer que la page est en cours de chargement
// Le curseur se positionne sur toutes les éléments DOM visibles
function attente() {
    $(':visible').addClass('cursor_wait');
}

function fin_attente() {
    $(':visible').removeClass('cursor_wait');
}


// Fonction de redirection 
// Elles utilisent le bundle FOSJsRoutingBundle
// Pour pouvoir appeler une route en Javascript, il faut l'indiquer dans le paramètre de configuration du bundle : 
/*		app/config/config.yml
			fos_js_routing:
     			routes_to_expose: [
*/
// Exemple de route avec passage de paramètre : Routing.generate('lci_site_update', {idSite: $id_site});
function redirection($destination, $tab_parametre = null) {
	var $url_destination;
	switch($destination) {
 		case 'retourAccueil' :
			// Le paramètre qui peut être passé correspond à l'identifiant du site à afficher dans la popup au lancement de la page d'accueil (page d'accueil = Liste des sites)
			// Retour sur la page d'accueil : Liste des sites
			$url_destination = Routing.generate('lci_boilerbox_accesSite');
			if ($tab_parametre) {
				$url_destination += '/' + $tab_parametre[0];
			}
			break;
		case 'modificationSite' :
			$url_destination = Routing.generate('lci_site_update', {idSite: $tab_parametre['id_site']});
			break;
		case 'creationSite' :
			$url_destination = Routing.generate('lci_register_site');
			break;
		case 'accueilConf' :
			// Retour sur la page des Paramètres BoilerBox
			$url_destination = Routing.generate('lci_gestion_admin');
			break;
		case 'accueilConfUtilisateur' :
			// Retour sur la page de Gestion des utilisateurs
			$url_destination = Routing.generate('lci_accueil_register_user');
			break;
	}
	window.location = $url_destination;
}


// Fonction qui retourne un texte avec un nombre de caractères max
// ex : IN 	-> ce_nom_de_fichier_est_trop_long   
//		OUT -> ce_nom_de_fichier...
function decoupeTexte($texte, $nb_caracateres) {
    if($texte.length > $nb_caracateres) {
    	$texte = $texte.substr(0, $nb_caracateres - 3);
        $texte = $texte + '...';
    }
	return $texte;
}



function getXHR() {
    var xhr;
    try {
        xhr = new ActiveXObject('Msxml2.XMLHTTP');
    }catch (e)
    {
        try {
            xhr = new ActiveXObject('Microsoft.XMLHTTP');
        }catch (e2)
        {
            try {   xhr = new XMLHttpRequest();
            }catch (e3)
            {
				// Le navigateur ne supporte pas l'objet XmlHttpRequest
                xhr = false; 
            }
        }
    }
    return xhr;
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


