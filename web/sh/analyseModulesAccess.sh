#!/bin/sh

# -------------- DEV ----------------------------------
#PARAMETRES boilerbox_prod_2.7
#boilerDir='/var/www/html/BoilerBox.fr/BoilerBox_Prod_2.7'

#PARAMETRES boilerbox-LTS
#flagAnalyseAccess='/tmp/.flagLTSBoilerBoxAnalyseModulesAccess'
#boilerDir='/var/www/html/BoilerBox.fr/BoilerBox-LTS


# -------------- PROD ----------------------------------
#PARAMETRES boilerbox.fr
#boilerDir='/var/www/vhosts/boiler-box.fr/httpdocs/BoilerBox'
#cheminPHP='/opt/plesk/php/7.1/bin/php'

#PARAMETRES boilerbox_LTS
#flagAnalyseAccess='/tmp/.flagLTSBoilerBoxAnalyseModulesAccess'
#boilerDir='/var/www/vhosts/boiler-box.fr/boiler-box-lts.fr'
#cheminPHP='/opt/plesk/php/7.1/bin/php'

# -------------- CLOUD ----------------------------------
#PARAMETRES boilerbox.fr
#boilerDir='/var/www/html/boilerbox.fr'
#cheminPHP='php'

#PARAMETRES boilerbox_LTS
flagAnalyseAccess='/tmp/.flagLTSBoilerBoxAnalyseModulesAccess'
boilerDir='/var/www/html/boilerbox.fr/BoilerBox-LTS'
cheminPHP='php'



#flagAnalyseAccess='/tmp/.flagBoilerBoxAnalyseModulesAccess'
#boilerDir='/var/www/html/boilerbox.fr/BoilerBox'
consoleDir=$boilerDir'/bin/console'
#cacheDir=$boilerDir'/var/cache'
#cheminPHP='/usr/bin/php'




# Vérification qu'un flag d'importation de fichiers binaires n'existe pas
if [ -e "$flagAnalyseAccess" ]; then
        # Si le flag existe mais que le process ne fonctionne pas : suppression du flag
        processActif=`ps -ef | grep 'boilerbox:modulesutils' | grep -v 'grep'`
        if [ -z "$processActif" ]; then
            rm "$flagAnalyseAccess"
        else
            echo "L'analyse de la disponibilité des sites est déjà en cours d'execution"
        fi
        exit 1
else
    # Création du flag
    touch "$flagAnalyseAccess"
    # Appel de la commande qui importe en base la liste des fichiers présents dans le dossier fichiers_binaires
    retour=`nice -0 $cheminPHP $consoleDir boilerbox:modulesutils`
    # Libération du flag
    rm "$flagAnalyseAccess"
fi
#chmod -R 777 $cacheDir
exit 0
