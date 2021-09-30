Ce site permet de configurer le sites boilerbox.fr LCI et LTS



Lors de la synchronisation avec le boilerbox.fr de LCI (boilerbox.fr/BoilerBox/) : 

Après avoir effectué le pull il convient de 

Modifier la base de donnée dans le fichier app/config/parameters.yml -> mettre boilerbox
Modifier le parametre url_cloud dane le fichier de configuration app/config/config.yml
-> url_cloud: 'http://54.36.104.188/'




Lors de la synchronisation avec le boilerbox.fr de LTS (boilerbox.fr/BoilerBox-LTS/) :

Après avoir effectué le pull il convient de 

Modifier la base de donnée dans le fichier app/config/parameters.yml -> mettre lts-boilerbox
Modifier le parametre url_cloud dane le fichier de configuration app/config/config.yml
-> url_cloud: 'http://cloud.boiler-box-lts.fr/'
