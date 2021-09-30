#!/bin/sh
#OBSOLETE

# Importation des fichiers Binaires
flagRapport='/tmp/.flagBoilerBoxRapportIndisponibilite.sh'

# Vérification qu'un flag n'existe pas
if [ -e "$flagRapport" ]; then
        echo "Le rapport d'indisponibilité est déjà en cours d'execution"
        exit 1
else
    # Création du flag
    touch "$flagRapport"
    # Appel de la commande qui importe en base la liste des fichiers présents dans le dossier fichiers_binaires
    retour=`nice -0 php /var/www/html/boilerbox.fr/BoilerBox/bin/console boilerbox:rapportIndisponibilite`
    # Libération du flag
    rm "$flagRapport"
fi
exit 0
