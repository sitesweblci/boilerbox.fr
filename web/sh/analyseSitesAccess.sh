#!/bin/sh

# -------------- DEV ----------------------------------
#PARAMETRES boilerbox_prod_2.7
#boilerDir='/var/www/html/BoilerBox.fr/BoilerBox_Prod_2.7'

#PARAMETRES boilerbox-LTS
#flagAnalyseAccess='/tmp/.flagLTSBoilerBoxAnalyseSitesAccess'
#boilerDir='/var/www/html/BoilerBox.fr/BoilerBox-LTS


# -------------- PROD ----------------------------------
#PARAMETRES boilerbox.fr
#boilerDir='/var/www/vhosts/boiler-box.fr/httpdocs/BoilerBox'
#cheminPHP='/opt/plesk/php/7.1/bin/php'

#PARAMETRES boilerbox_LTS
#flagAnalyseAccess='/tmp/.flagLTSBoilerBoxAnalyseSitesAccess'
#boilerDir='/var/www/vhosts/boiler-box.fr/boiler-box-lts.fr'
#cheminPHP='/opt/plesk/php/7.1/bin/php'

# -------------- CLOUD ----------------------------------
#PARAMETRES boilerbox.fr
flagAnalyseAccess='/tmp/.flagBoilerBoxAnalyseSitesAccess'
boilerDir='/var/www/html/boilerbox.fr/BoilerBox'
cheminPHP='php'

#PARAMETRES boilerbox_LTS
#flagAnalyseAccess='/tmp/.flagLTSBoilerBoxAnalyseSitesAccess'
#boilerDir='/var/www/html/boilerbox.fr/BoilerBox-LTS'
#cheminPHP='php'


consoleDir=$boilerDir'/bin/console'




# Vérification qu'un flag d'importation de fichiers binaires n'existe pas
if [ -e "$flagAnalyseAccess" ]; then
		# Si le flag existe mais que le process ne fonctionne pas : suppression du flag
		processActif=`ps -ef | grep 'boilerbox:utils' | grep -v 'grep'`
		if [ -z "$processActif" ]; then
			rm "$flagAnalyseAccess"
		else
			echo "L'analyse de la disponibilité des sites est déjà en cours d'execution"
		fi
        exit 1
else
    # Création du flag
    touch "$flagAnalyseAccess"
    retour=`nice -0 $cheminPHP $consoleDir boilerbox:utils`
    # Libération du flag
    rm "$flagAnalyseAccess"
fi
#chmod -R 777 $cacheDir
exit 0
