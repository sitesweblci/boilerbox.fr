<script type='text/javascript'>
var months = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aou', 'Sep', 'Oct', 'Nov', 'Déc'];
var timestampDateDebut = {{ timestamp|json_encode|raw }} * 1000;
var timeDateDebut = new Date(timestampDateDebut);
var tmpDebutScript = new Date().getTime();
var valeurRadioSite = 'affaire';
var valeurCheckSite = 'site';


$(document).ready(function() {
    setSiteIpcDimension();
    setInterval("majDeDate()", 100);
});


// Fonction qui définie la taille de l'écran afin que la div d'identifiant siteIpc couvre la totalité de l'écran
function setSiteIpcDimension() {
    var hauteur = window.innerHeight + 'px';
    var largeur = window.innerWidth + 'px';
    $('#siteIpc').width(largeur);
    $('#siteIpc').height(hauteur);
}


function majDeDate() {
    var heureActuelle = {{ lHeure|json_encode|raw }};
    var jourActuel = {{ leJour|json_encode|raw }};
    $('#heure').text(heureActuelle);
    $('#date').text(jourActuel);
    // Temps d'execution du script 
    var tmpFinScript = new Date().getTime();
    var tmpTimestampDiffScript = tmpFinScript - tmpDebutScript;
    //	Nouvelle date 
    var newTimestamp = timestampDateDebut + tmpTimestampDiffScript;
    var newDate = new Date(newTimestamp);
    $('#heure').text(fillNumber(newDate.getHours()) + ':' + fillNumber(newDate.getMinutes()) + ':' + fillNumber(newDate.getSeconds()));
    $('#date').text(fillNumber(newDate.getDate()) + ' ' + months[newDate.getMonth()] + ' ' + newDate.getFullYear());
}

function fillNumber(num) {
    if (num.toString().length == 1) {
		return("0"+num);
    }
    return(num);
}
</script>