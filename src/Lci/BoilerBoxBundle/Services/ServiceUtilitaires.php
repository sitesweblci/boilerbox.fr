<?php
//src/Lci/BoilerBoxBundle/Services/ServiceUtilitaires
//Service effectuant le transfert des fichiers ftp des localisations du site courant
namespace Lci\BoilerBoxBundle\Services;

use Lci\BoilerBoxBundle\Entity\configuration;

class ServiceUtilitaires
{

    private $delais_netcat;
    private $label;
    private $em;
    private $dnsServer;
    private $service_configuration;
    private $service_log;
    private $service_mailing;
    private $debug;
    private $listes_users_suivi_sites;
    private $delais_echec_connexion_defaut;
    private $mail_debuggeur;
    private $variable_en_erreur;
    private $date_access;
    private $ewons;


    public function __construct($doctrine, $service_configuration, $service_log, $service_mailing, $mail_debuggeur)
    {
        $this->date_access = new \Datetime();
        $this->mail_debuggeur = $mail_debuggeur;
        ## Variable indiquant le delais d'execution (en seconde) de chaque test. Diminuer ce chiffre accelère le traitement des tests multiples
        $this->delais_netcat = 5;
        $this->em = $doctrine->getManager();
        $this->dnsServer = '8.8.8.8';
        $this->service_configuration = $service_configuration;
        $this->service_log = $service_log;
        $this->service_mailing = $service_mailing;
        $this->debug = false;
        // Délais par défaut après lequel une connexion erronée doit être indiquée dans la rapport de suivi des sites : 1 heure	-> Pour modifier ensuite il faut modifier directement la valeur en base
        $this->delais_echec_connexion_defaut = '3600';
        // Listes des utilisateurs qui receptionnent les mails d'erreur de connexion
        $ents_users = $this->em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('Role_suivi_des_sites');
        $listes_users_suivi_sites = array();
        foreach ($ents_users as $ent_user) {
            $listes_users_suivi_sites[] = $ent_user->getEmail();
        }
        $this->listes_users_suivi_sites = $listes_users_suivi_sites;
        $this->ewons = array();
    }

    // Suivi des sites : Fonction créée également dans un Controller - UtilsController.php : function validChoiceAction() - A modifier aussi en cas de modification de cette fonction
    // Utilitaire NETCAT : Analyse la liste des sites avec un indicateur indiquant la réussite ou l'échec du test.
    // N'analyse que les sites accessibles ( paramètre configBoilerBox = true)
    public function analyseAccess($numero_affaire = null)
    {
        set_time_limit(0);
        if ($this->debug) $this->service_log->setLog("\nNouveau test de suivi des sites");
        // Mise à jour de la valeur du paramètre : date du dernier test de disponibilité des sites.
        $date_du_jour = new \Datetime();

        // Envoi du rapport si c'est le premier check journalier
        $this->gestionRapportConnexion($this->service_configuration->getEntiteDeConfiguration('date_test_de_disponibilite')->getValeur());

        // DEBUG ! RETIRER LES COMMENTAIRES POUR FAIRE DES TESTS
        //return 0;

        if (null === $numero_affaire) {
            // Recherche de la liste des sites enregistrés en base de donnée
            $tab_entities_site = $this->em->getRepository('LciBoilerBoxBundle:Site')->findAll();
        } else {
            $tab_entities_site = array();
            // Si on vérifie l'accès à une seule affaire on ne met que celle ci dans le tableau
            $tab_entities_site[] = $this->em->getRepository('LciBoilerBoxBundle:Site')->findOneBy(['affaire' => strtoupper($numero_affaire)]);
        }

        // Pour chaque site : Récupération de l'adresse ip et test de l'accessibilité sur cette adresse
        $json_response = @file_get_contents('https://m2web.talk2m.com/t2mapi/getewons?t2mdeveloperid=a233beb0-a971-48f5-a8e8-f3030723a4ea&t2maccount=boiler-box&t2musername=script&t2mpassword=W4Mz7mM8p');

        $response = json_decode($json_response, true);
        $this->ewons = array();
        if (array_key_exists('ewons', $response)) {
            $ewons = $response['ewons'];
            foreach ($ewons as $ewon) {
                if ('-' === $ewon['customAttributes'][1]) {
                    continue;
                }
                $numAffaire = strtok($ewon['name'], ' ');
                $this->ewons[$numAffaire] = array('name' => $ewon['name'], 'status' => $ewon['status']);
                if ($this->debug) echo "eWon recupere : $numAffaire : " . $ewon['status'] . "\n";
                if ('' !== $ewon['customAttributes'][1]) {
                    // pour les comptes ecatcher demandant de se connecter sur un autre
                    $this->ewons[$numAffaire]['affaire_principale'] = $ewon['customAttributes'][1];
                }
            }
        }
        foreach ($tab_entities_site as $entity_site) {

            switch (substr(strtoupper($entity_site->getAffaire()), 0, 1)) {
                // verification pour ne pas traiter de module sur les sites de boilerbox.fr
                case 'C':
                case 'D':
                    $this->checkAccesSiteBoilerBox($entity_site);
                    break;
                default:
                    $this->checkAccesSite($entity_site);
                    break;
            }
        }

        // Retour de la liste des entités [ site ]

        // Définition de la date de test à la fin du test pour éviter les Erreur de type (1) et (2)
        // Créer la variable de conf si elle n'existe pas. Puis la définie à la valeur de la date du jour
        $entity_configuration = $this->service_configuration->getEntiteDeConfiguration('date_test_de_disponibilite', $date_du_jour->format('d-m-Y H:i:s'));
        $entity_configuration->setValeur($date_du_jour->format('d-m-Y H:i:s'));
        $this->em->persist($entity_configuration);
        $this->em->flush();


        return (0);
    }

    // Fonction qui fait un appel API pour récupérer le status de l'eWON
    public function getEwonStatus($entity_site)
    {
        if ($this->debug) echo "\nTest connexion Ewon " . $entity_site->getAffaire() . "\n";
        if ($entity_site->getSiteConnexion()) {
            $pattern = '/(.+)-.+/';
            $replacement = '$1';
            $affaire_url = preg_replace($pattern, $replacement, $entity_site->getAffaire());
            if ($this->debug) echo "\nConnexion possible sur " . $affaire_url . "\n";
            $json_response = @file_get_contents('https://m2web.talk2m.com/t2mapi/getewons?t2mdeveloperid=a233beb0-a971-48f5-a8e8-f3030723a4ea&t2maccount=' . $affaire_url . '&t2musername=technicien&t2mpassword=5667tech');

            if ($json_response === false) {
                if ($this->debug) echo "\nSite non géré par Talk2M";
                if ($this->debug) $this->service_log->setLog("Le site " . $entity_site->getAffaire() . " n'est pas géré par le service Talk2M");
                $this->gestion_mail_connexion_erreur("talk2m", $entity_site);
                return ("offline");
            } else {
                if ($this->debug) {
                    echo "\n";
                    print_r($json_response);
                    echo "\n";
                    echo "\n";
                }
                $response = json_decode($json_response, true);
                if ($response["ewons"]) {
                    foreach ($response["ewons"] as $ewon_site) {
                        if ($this->debug) echo "\n" . $ewon_site["name"] . ' - > ' . $ewon_site["status"] . "\n";
                        $nom_du_site = $ewon_site["name"];
                        $status_du_site = $ewon_site["status"];
                        // Dès qu'on trouve un site online on termine le traitement pour gérer le cas ou plusieurs sites sont sur le compte de l'eWON
                        if ($status_du_site == "online") break;
                    }
                    if ($this->debug) $this->service_log->setLog($json_response);
                    if ($this->debug) $this->service_log->setLog("Le site " . $nom_du_site . " est " . $status_du_site);
                } else {
                    $status_du_site = "offline";
                }
                if ($this->debug) echo "\nStatus renvoyé : " . $status_du_site . "\n";
                // Gestion de l'envoi de mail
                if ($status_du_site == "offline") {
                    $this->gestion_mail_connexion_erreur("offline", $entity_site);
                } else {
                    $this->gestion_mail_connexion_erreur("online", $entity_site);
                }
                return ($status_du_site);
            }
        }
    }

    // Fonction qui fait un appel API pour récupérer le status de l'eWON pour le compte pro boiler-box
    public function getEwonStatusBoilerBox($entity_site)
    {
        if ($entity_site->getSiteConnexion()) {
            $affaire_url = $entity_site->getAffaire();

            if ($this->debug) echo "\nConnexion possible sur " . $affaire_url . "\n";

            if (!array_key_exists($affaire_url, $this->ewons)) {
                if ($this->debug) echo "\nSite non géré par Talk2M";
                if ($this->debug) $this->service_log->setLog("Le site " . $entity_site->getAffaire() . " n'est pas géré par le service Talk2M");
                $this->gestion_mail_connexion_erreur("talk2m", $entity_site);
                return ("offline");
            } else {
                if ($this->debug) {
                    echo "\n";
                    print_r($this->ewons[$affaire_url]);
                    echo "\n";
                    echo "\n";
                }

                $ewon = $this->ewons[$affaire_url];
                $affaireToCheck = $affaire_url;
                if (array_key_exists('affaire_principale', $ewon)) {
                    $affaireToCheck = strtok($this->ewons[$ewon['affaire_principale']]['name'], ' ');
                }
                if (array_key_exists($affaireToCheck, $this->ewons)) {
                    $ewon = $this->ewons[$affaireToCheck];
                    if ($this->debug) echo "\n" . $ewon["name"] . ' - > ' . $ewon["status"] . "\n";
                    $nom_du_site = $ewon["name"];
                    $status_du_site = $ewon["status"];
                    if ($this->debug) $this->service_log->setLog("Le site " . $nom_du_site . " est " . $status_du_site);
                } else {
                    $status_du_site = "offline";
                }
                if ($this->debug) echo "\nStatus renvoyé : " . $status_du_site . "\n";
                // Gestion de l'envoi de mail
                if ($status_du_site == "offline") {
                    $this->gestion_mail_connexion_erreur("offline", $entity_site);
                } else {
                    $this->gestion_mail_connexion_erreur("online", $entity_site);
                }
                return ($status_du_site);
            }
        }
    }

    public function checkAccesSite($entity_site)
    {
        if ($this->debug) $tab_details = [];
        // Inscription de la date du test dans l'entité site
        $entity_site->setDateAccess($this->date_access);
        // Le test du ping (netcat) n'est effectué que pour les sites accessibles (paramètres accesDistant = true && configBoilerbox = true)
        if ($entity_site->getSiteConnexion()) {
            // Acces à distance possible 	&& 		Acces par BoilerBox.fr possible ( et donc Acces par eCatcher possible aussi )
            // 		On test la connexion depuis ecatcher. Si la connexion est ko alors la connexion netcat sera également ko
            // 		TEST ECATCHER
            //      On test le status (Online / offline) de l'eWON
            //          Si le retour est Online : DISPO (3) BLEU + procedure qui continue pour tester l'acces netcat
            //          Sinon DISPO (1) ROUGE + FIN
            //		TEST NETCAT
            // 		On test le retour de la commande netcat
            // 		Si le retour est en succes :
            //			On test l'appel de la page de connexion IPC pour vérifier que le port 80 nous redirige bien sur l'IPC
            // 				Si le retour de la page de connexion IPC est ok -> DISPO (0) : VERT + FIN
            if ((!in_array('non', $entity_site->getSiteConnexion()->getAccesDistant())) && (in_array('boilerbox', $entity_site->getSiteConnexion()->getAccesDistant()))) {
                // TEST ECATCHER
                $retour_ewon_status = $this->getEwonStatus($entity_site);
                if ($retour_ewon_status == 'online') {
                    $entity_site->getSiteConnexion()->setDisponibilite(3);
                    $entity_site->setDateAccessSucceded($this->date_access);
                    $entity_site->setTypeAccessSucceeded('ewon');
                    // On vérifie si une date d'echec de connexion existe, si oui on calcul le temps d'indisponibilité et on réinitialise la date d'echec
                    $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'ecatcher';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Joignable depuis Ecatcher : </p><span class='ecatcher'>" . $entity_site->getIntitule() . " : Est joignable seulement depuis Ecatcher</span>";
                } else {
                    $entity_site->getSiteConnexion()->setDisponibilite(2);
                    $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'nok';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Pas de réponse du port 80 et site offline sur eCatcher</p>";
                    $this->em->flush();
                    return (1);
                }

                // TEST NETCAT
                if ($this->debug) echo $entity_site->getAffaire() . " test netcat";
                $retour_test_netcat = $this->test_netcat($entity_site);
                if ($retour_test_netcat === true) {
                    $entity_site->getSiteConnexion()->setDisponibilite(0);
                    // On indique la date d'acces en succes depuis boiler-box.fr
                    $entity_site->setDateAccessSucceded($this->date_access);
                    $entity_site->setTypeAccessSucceeded('netcat');
                    $this->checkIndisponibilite('boilerbox', $retour_test_netcat, $entity_site->getSiteConnexion());
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'ok';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Success : " . $entity_site->getIntitule() . " : Joignable sur le port 80";
                    $this->em->flush();
                    return (0);
                } else {
                    $this->checkIndisponibilite('boilerbox', $retour_test_netcat, $entity_site->getSiteConnexion());
                }
                $this->em->flush();
                return (1);
            } else {
                // Inaccessible depuis BoilerBox.fr parceque la configuration d'accès à distance n'est autorisé par le client ADSL OU l'accès distant est définit à FALSE (pas de conf eWON / NoIPi ou Pas de contrat )
                // Si l'accès à distance est défini à NON -> DISPO (4) GRIS + FIN
                // Sinon si seul l'accès eCatcher est possible (configuration ADSL client bloquante) ->
                // 		On test le status (Online / offline) de l'eWON
                //          Si le test est en succes : DISPO (3) BLEU + FIN
                //          Sinon DISPO (1) ROUGE + FIN
                if (in_array('non', $entity_site->getSiteConnexion()->getAccesDistant())) {
                    $entity_site->getSiteConnexion()->setDisponibilite(4);
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'inaccessible';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Inaccessible : </p><span class='inaccessible'>" . $entity_site->getIntitule() . " : N'est pas accessible à distance</span>";
                    $this->em->flush();
                    return (1);
                } else {
                    $retour_ewon_status = $this->getEwonStatus($entity_site);
                    if ($retour_ewon_status == 'online') {
                        $entity_site->getSiteConnexion()->setDisponibilite(3);
                        $entity_site->setDateAccessSucceded($this->date_access);
                        $entity_site->setTypeAccessSucceeded('ewon');
                        $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                        if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'ecatcher';
                        if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Joignable depuis Ecatcher : </p><span class='ecatcher'>" . $entity_site->getIntitule() . " : Est joignable seulement depuis Ecatcher</span>";
                        $this->em->flush();
                        return (0);
                    } else {
                        $entity_site->getSiteConnexion()->setDisponibilite(2);
                        $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                        if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'nok';
                        if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Site offline sur eCatcher</p>";
                        $this->em->flush();
                        return (1);
                    }
                }
            }
        }
        if ($this->debug) $tab_details[$entity_site->getId()]['date_test'] = date('H\hi');
        $this->em->flush();
        if ($this->debug) return ($tab_details);
        return (0);
    }

    public function checkAccesSiteBoilerBox($entity_site)
    {
        if ($this->debug) $tab_details = [];
        // Inscription de la date du test dans l'entité site
        $entity_site->setDateAccess($this->date_access);
        // Le test du ping (netcat) n'est effectué que pour les sites accessibles (paramètres accesDistant = true && configBoilerbox = true)
        if ($entity_site->getSiteConnexion()) {
            // Acces à distance possible 	&& 		Acces par BoilerBox.fr possible ( et donc Acces par eCatcher possible aussi )
            // 		On test la connexion depuis ecatcher. Si la connexion esrt ko alors la connexion netcat sera également ko
            // 		TEST ECATCHER
            //      On test le status (Online / offline) de l'eWON
            //          Si le retour est Online : DISPO (3) BLEU + procedure qui continue pour tester l'acces netcat
            //          Sinon DISPO (1) ROUGE + FIN
            //		TEST NETCAT
            // 		On test le retour de la commande netcat
            // 		Si le retour est en succes :
            //			On test l'appel de la page de connexion IPC pour vérifier que le port 80 nous redirige bien sur l'IPC
            // 				Si le retour de la page de connexion IPC est ok -> DISPO (0) : VERT + FIN
            if ((!in_array('non', $entity_site->getSiteConnexion()->getAccesDistant())) && (in_array('boilerbox', $entity_site->getSiteConnexion()->getAccesDistant()))) {
                // TEST ECATCHER
                $retour_ewon_status = $this->getEwonStatusBoilerBox($entity_site);
                if ($this->debug) echo $entity_site->getAffaire() . " test de connexion\n";

                if ($retour_ewon_status == 'online') {
                    $entity_site->getSiteConnexion()->setDisponibilite(3);
                    $entity_site->setDateAccessSucceded($this->date_access);
                    $entity_site->setTypeAccessSucceeded('ewon');
                    // On vérifie si une date d'echec de connexion existe, si oui on calcul le temps d'indisponibilité et on réinitialise la date d'echec
                    $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'ecatcher';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Joignable depuis Ecatcher : </p><span class='ecatcher'>" . $entity_site->getIntitule() . " : Est joignable seulement depuis Ecatcher</span>";
                } else {
                    $entity_site->getSiteConnexion()->setDisponibilite(2);
                    $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'nok';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Pas de réponse du port 80 et site offline sur eCatcher</p>";
                    $this->em->flush();
                    return (1);
                }

                // TEST NETCAT
                if ($this->debug) echo $entity_site->getAffaire() . " test netcat";
                $retour_test_netcat = $this->test_netcat($entity_site);
                if ($retour_test_netcat === true) {
                    $entity_site->getSiteConnexion()->setDisponibilite(0);
                    // On indique la date d'acces en succes depuis boiler-box.fr
                    $entity_site->setDateAccessSucceded($this->date_access);
                    $entity_site->setTypeAccessSucceeded('netcat');
                    $this->checkIndisponibilite('boilerbox', $retour_test_netcat, $entity_site->getSiteConnexion());
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'ok';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Success : " . $entity_site->getIntitule() . " : Joignable sur le port 80";
                    $this->em->flush();
                    return (0);
                } else {
                    $this->checkIndisponibilite('boilerbox', $retour_test_netcat, $entity_site->getSiteConnexion());
                }
                $this->em->flush();
                return (1);
            } else {
                // Inaccessible depuis BoilerBox.fr parceque la configuration d'accès à distance n'est autorisé par le client ADSL OU l'accès distant est définit à FALSE (pas de conf eWON / NoIPi ou Pas de contrat )
                // Si l'accès à distance est défini à NON -> DISPO (4) GRIS + FIN
                // Sinon si seul l'accès eCatcher est possible (configuration ADSL client bloquante) ->
                // 		On test le status (Online / offline) de l'eWON
                //          Si le test est en succes : DISPO (3) BLEU + FIN
                //          Sinon DISPO (1) ROUGE + FIN
                if (in_array('non', $entity_site->getSiteConnexion()->getAccesDistant())) {
                    $entity_site->getSiteConnexion()->setDisponibilite(4);
                    if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'inaccessible';
                    if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Inaccessible : </p><span class='inaccessible'>" . $entity_site->getIntitule() . " : N'est pas accessible à distance</span>";
                    $this->em->flush();
                    return (1);
                } else {
                    $retour_ewon_status = $this->getEwonStatusBoilerBox($entity_site);
                    if ($retour_ewon_status == 'online') {
                        $entity_site->getSiteConnexion()->setDisponibilite(3);
                        $entity_site->setDateAccessSucceded($this->date_access);
                        $entity_site->setTypeAccessSucceeded('ewon');
                        $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                        if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'ecatcher';
                        if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Joignable depuis Ecatcher : </p><span class='ecatcher'>" . $entity_site->getIntitule() . " : Est joignable seulement depuis Ecatcher</span>";
                        $this->em->flush();
                        return (0);
                    } else {
                        $entity_site->getSiteConnexion()->setDisponibilite(2);
                        $this->checkIndisponibilite('ecatcher', $retour_ewon_status, $entity_site->getSiteConnexion());
                        if ($this->debug) $tab_details[$entity_site->getId()]['statut'] = 'nok';
                        if ($this->debug) $tab_details[$entity_site->getId()]['detail'] = "<p>Site offline sur eCatcher</p>";
                        $this->em->flush();
                        return (1);
                    }
                }
            }
        }
        if ($this->debug) $tab_details[$entity_site->getId()]['date_test'] = date('H\hi');
        $this->em->flush();
        if ($this->debug) return ($tab_details);
        return (0);
    }

    private function test_netcat($entity_site)
    {

        // Retourne l'url du site
        $tab_param_url = $this->recuperationSiteUrl($entity_site->getSiteConnexion()->getUrl());
        if ($this->debug) echo "Test du port 80\n";
        // **** Récupération de l'adresse ip si l'url est de type DNS (http://***) sinon on enregistre l'adresse ip défini par l'url dans la variable
        $pattern_dns = '/^[a-zA-Z]/';
        if (!preg_match($pattern_dns, $tab_param_url['url'])) {
            $adresse_ip_site = $tab_param_url['url'];
        } else {
            $commande_adresse_ip_site = "host -t A " . $tab_param_url['url'] . " " . $this->dnsServer . " | grep 'has address' | awk -F' ' '{print $4}'";
            $adresse_ip_site = exec($commande_adresse_ip_site, $tab_adresse_ip, $retour_commande);
            if ($this->debug) {
                echo "Commande Hosts\n";
                echo "Commande hosts : host -t A " . $tab_param_url['url'] . " " . $this->dnsServer . " | grep 'has address' | awk -F' ' '{print $4}'\n";
                echo "Adresse : $adresse_ip_site\n";
            }
        }
        if ($adresse_ip_site != '') {
            $commande_netcat = "nc -v -n -z -w $this->delais_netcat $adresse_ip_site " . $tab_param_url['port'];
            $last_command_lign = exec($commande_netcat, $tab_netcat, $retour_commande_netcat);
            // Interprétation de la réponse et modification de l'entité site passée en paramètre
            if ($retour_commande_netcat == 0) {
                // Si le ping est ok  : On test l'appel de la page d'accueil html de l'ipc -> On y recherche le terme LCI-GROUP pour confirmer que nous somme sur le site Web de LCI
                $url_page_acceuil = $entity_site->getSiteConnexion()->getUrl();
                $html_accueil_ipc = @file_get_contents($url_page_acceuil);
                $pattern_lci = '/LCI-GROUP/';
                if ($this->debug) $this->service_log->setLog($html_accueil_ipc);
                if (preg_match($pattern_lci, $html_accueil_ipc)) {
                    if ($this->debug) $this->service_log->setLog('Site ' . $entity_site->getIntitule() . ' (' . $entity_site->getAffaire() . ") accessible depuis BoilerBox.fr");
                    // On envoi le mail lors du retour d'une connexion sur le port 80
                    $this->gestion_mail_connexion_erreur("accessible", $entity_site);
                    return (true);
                } else {
                    if ($this->debug) $this->service_log->setLog('Le Site ' . $entity_site->getIntitule() . ' (' . $entity_site->getAffaire() . ") n'est pas à l'adresse ip indiqué ou est mal redirigé");
                    $this->gestion_mail_connexion_erreur("pageHtml", $entity_site);
                    return (false);
                }
            } else {
                // Si le ping n'est pas ok
                if ($this->debug) $this->service_log->setLog('Le Site ' . $entity_site->getIntitule() . ' (' . $entity_site->getAffaire() . ") : ne réponds pas sur le port 80");
                $this->gestion_mail_connexion_erreur("ping", $entity_site);
                return (false);
            }
        } else {
            // Inaccessible car l'adresse ip n'est pas trouvée
            $this->gestion_mail_connexion_erreur("ip", $entity_site);
            return (false);
        }
    }


    // Fonction qui prend en argument une url de type http://c671.boiler-box.fr/ (ou http://c714.boiler-box.fr:81/) et retourne l'url c671.boiler-box.fr (ou c714.boiler-box.fr)
    private function recuperationSiteUrl($url)
    {
        if ($this->debug) echo "\nRecuperation URL :" . $url . "\n";
        $tab_param_url = array();
        $pattern_url = '/^http:\/\/(.+?):?(\d*?)\/?$/';
        if (preg_match($pattern_url, $url, $tab_url)) {
            if (!isset($tab_url[2])) {
                $tab_url[2] = 80;
            } else {
                if ($tab_url[2] == null) {
                    $tab_url[2] = 80;
                }
            }
            $tab_param_url['url'] = $tab_url[1];
            $tab_param_url['port'] = $tab_url[2];
        } else {
            $tab_param_url['url'] = $url;
            $tab_param_url['port'] = 80;
        }
        if ($this->debug) echo "Retourn url : " . $tab_param_url['url'] . "\n";
        return ($tab_param_url);
    }



    /* La variable tab_message doit être instanciée comme suit :
        tab_message['titre']
        tab_message['site']
        tab_message['contact']
        tab_message['fichiers']

		Si erreur netcat
			envoi du mail
				si pas de DateSucces / Netcat
				ou
					DateSuccess / Netcat de plus de 5 minutes && si pas envoi mail netcat (mailSended = "non" ou mailSended = "ewon")
					-> mailSended = netcat

		Si erreur ewon
			envoi du mail
				si pas de DateSuccess
				ou
					DateSuccess de plus de 5 minutes && si pas envoi mail ewon (mailSended = "non")
					-> mailSended = ewon
    */
    /* ERREUR SI ECHEC DE PLUS DE 1 Journée -> A	MODIFIER SI BESOIN PT6H */
    public function gestion_mail_connexion_erreur($erreur, $ent_site)
    {
        $intervalle = 'P1D';
        // On n'envoi pas de mail sur les sites qui ne sont pas sous surveillance.
        if ($ent_site->getSiteConnexion()->getSurveillance() === false) return 0;

        // On envoi les mails d'erreur si la date d'erreur en cours est supérieur à la limite définie ou si aucune date en succés n'a eu lieu
        $date_erreur = new \Datetime();
        if ($ent_site->getDateAccessSucceded()) {
            $date_out = \DateTime::createFromFormat('Y-m-d H:i:s', $ent_site->getDateAccessSucceded()->format('Y-m-d H:i:s'));
        } else {
            $date_out = $date_erreur->sub(new \DateInterval($intervalle));
        }
        $date_last_success = $date_out->add(new \DateInterval($intervalle));
        $type_date_last_success = $ent_site->getTypeAccessSucceeded();

        if (($erreur == "online") || ($erreur == "accessible")) {
            // Gestion des mails de fin d'erreur
            if (in_array('boilerbox', $ent_site->getSiteConnexion()->getAccesDistant())) {
                // La configuration du site indique : Accès possible depuis boiler-box.fr
                // Si un mail d'alerte de perte de connexion vers l'ewon a été envoyé (alors mail port80 erreur a aussi été envoyé )
                // La connexion netcat indique la fin de l'incident
                // La connexion ewon indique la fin de perte de connexion ewon mais avec toujours un pb de connexion depuis boiler-box.fr
                if ($ent_site->getMailSended() == "ewon") {    // Si mail ewon envoyé (plus aucune connexion possible)
                    if ($erreur == 'accessible') {
                        // Récupération de la connexion avec le port 80 et la page d'accueil html
                        // ------------------->  ENVOI MAIL FIN ALERTE DE CONNEXION PORT 80 -> Avec indication fin d'alerte EWON
                        $this->sendMail('fin_alerte_netcat_plus', $ent_site);
                        // ------------------->  FIN ERREUR
                        $ent_site->setMailSended("non");
                        $ent_site->setDateMailSended(new \Datetime());
                    } else if ($erreur == 'online') {
                        // Récupération de la connexion avec l'ewon depuis les serveurs talk2m
                        // ------------------->  ENVOI MAIL FIN ALERTE DE CONNEXION EWON
                        $this->sendMail('fin_alerte_ewon', $ent_site);
                        // ------------------->  MODIFICATION DU TYPE D ERREUR
                        $ent_site->setMailSended("netcat");
                        $ent_site->setDateMailSended(new \Datetime());
                        $ent_site->setTypeAccessSucceeded(NULL);
                    }
                } else if ($ent_site->getMailSended() == "netcat") {
                    // Si mail netcat envoyé (la connexion ecatcher est elle ok)
                    if ($erreur == 'accessible') {
                        // Récupération de la connexion avec le port 80 et la page d'accueil html
                        // ------------------->  ENVOI MAIL FIN ALERTE DE CONNEXION PORT 80
                        $this->sendMail('fin_alerte_netcat', $ent_site);
                        // ------------------->  FIN ERREUR
                        $ent_site->setMailSended("non");
                        $ent_site->setDateMailSended(new \Datetime());
                    }
                }
            } else {
                // La configuration du site indique : Pas d'accès boiler-box.fr
                if ($ent_site->getMailSended() == "ewon") {
                    // Si mail ewon envoyé (plus aucune connexion possible)
                    if ($erreur == 'online') {
                        // Récupération de la connexion avec l'ewon depuis les serveurs talk2m
                        // ------------------->  ENVOI MAIL FIN ALERTE DE CONNEXION EWON
                        $this->sendMail('fin_alerte_ewon', $ent_site);
                        // ------------------->  FIN ERREUR
                        $ent_site->setMailSended("non");
                        $ent_site->setDateMailSended(new \Datetime());
                    }
                }
            }
        } else {    // Gestion des mails d'erreur
            if ((in_array($erreur, ['ip', 'ping', 'pageHtml'])) && ($ent_site->getMailSended() == 'non')) {    // Si perte de connexion depuis boiler-box.fr et qu'aucun autre mails d'erreur de connexion n'a été envoyé : On envoi le mail de perte de connexion boiler-box.fr
                if (($ent_site->getTypeAccessSucceeded() == NULL) || (($date_erreur > $date_last_success) && ($ent_site->getMailSended() == 'non'))) {
                    // ------------------->  ENVOI MAIL ALERTE CONNEXION PORT 80
                    $this->sendMail('alerte_netcat', $ent_site);
                    $ent_site->setMailSended('netcat');
                    $ent_site->setDateMailSended(new \Datetime());
                    $ent_site->setTypeAccessSucceeded(NULL);
                }
            } else if (in_array($erreur, ['talk2m', 'offline'])) {    // Si perte de connexion de l'ewon avec les serveurs de talk2m et qu'aucun autre mail d'erreur de connexion talk2m n'a été envoyé : On envoi le mail de perte de connexion vers les serveurs talk2m
                if ((($ent_site->getTypeAccessSucceeded() == NULL) && ($ent_site->getMailSended() != 'ewon')) || (($date_erreur > $date_last_success) && (($ent_site->getMailSended() == 'non') || ($ent_site->getMailSended() == 'netcat')))) {
                    // ------------------->  ENVOI MAIL ALERTE CONNEXION EWON
                    $this->sendMail('alerte_ewon', $ent_site);
                    $ent_site->setMailSended('ewon');
                    $ent_site->setDateMailSended(new \Datetime());
                    $ent_site->setTypeAccessSucceeded(NULL);
                }
            }
        }
        $this->em->flush();
        return (0);
    }


    private function sendMail($type, $ent_site)
    {
        // Les mails de pertes de connexion ne sont plus envoyés. Seul el email de rapport journalier est envoyé.
        // Pour réenvoyer les mails de pertes de connexion : Enlever les return(0) dans les case du switch($type)
        if ($this->debug) echo "<br />Mail $type to be sended";
        switch ($type) {
            case 'erreur_script_access_succeded' :
                $tab_message = array();
                $tab_message['erreur'] = true;
                $tab_message['titre'] = "Erreur (1) du script d'analyse de disponibilité des sites pour le site " . $ent_site->getIntitule();
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>La date d\'accès à distance en succés : ' . $this->variable_en_erreur . ' est définie à aujourd\'hui - Elle devrait être définie à j-1.</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->mail_debuggeur, $ent_site->getAffaire() . ' : Erreur du scripts Analyse de disponibilité des sites pour le site ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'erreur_script_today' :
                $tab_message = array();
                $tab_message['erreur'] = true;
                $tab_message['titre'] = "Erreur (2) du script d'analyse de disponibilité des sites pour le site " . $ent_site->getIntitule();
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>La date d\'echec de connexion à distance est définie à aujourd\'hui - Elle devrait être définie à j-1.</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->mail_debuggeur, $ent_site->getAffaire() . ' : Erreur du scripts Analyse de disponibilité des sites pour le site ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'erreur_script_diff' :
                $tab_message = array();
                $tab_message['erreur'] = true;
                $tab_message['titre'] = "Erreur (3) du script d'analyse de disponibilité des sites pour le site " . $ent_site->getIntitule();
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>Une durée d\'indisponibilité est rencontrée mais le site indique 0 erreur de connexion VPN.</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->mail_debuggeur, $ent_site->getAffaire() . ' : Erreur du scripts Analyse de disponibilité des sites pour le site ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'erreur_script_diffBB' :
                $tab_message = array();
                $tab_message['erreur'] = true;
                $tab_message['titre'] = "Erreur (4) du script d'analyse de disponibilité des sites pour le site " . $ent_site->getIntitule();
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>Une durée d\'indisponibilité est rencontrée mais le site indique 0 erreur de connexion BoilerBox.</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->mail_debuggeur, $ent_site->getAffaire() . ' : Erreur du scripts Analyse de disponibilité des sites pour le site ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'fin_alerte_netcat_plus' :
                //return (0);
                // Envoi du mail si pas
                $tab_message = array();
                $tab_message['erreur'] = false;
                $tab_message['titre'] = 'Rétablissement des connexions vers le site \'' . $ent_site->getIntitule() . '\'';
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>Les connexions avec le site ' . $ent_site->getIntitule() . ' (' . $ent_site->getAffaire() . ') ont été rétablies</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->listes_users_suivi_sites, $ent_site->getAffaire() . ' : Rétablissement des connexions vers ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'fin_alerte_netcat' :
                //return (0);
                $tab_message = array();
                $tab_message['erreur'] = false;
                $tab_message['titre'] = 'Rétablissement de la connexion boiler-box.fr vers le site \'' . $ent_site->getIntitule() . '\'';
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>La connexion depuis boiler-box.fr avec le site ' . $ent_site->getIntitule() . ' (' . $ent_site->getAffaire() . ') a été rétablie</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->listes_users_suivi_sites, $ent_site->getAffaire() . ' : Rétablissement de la connexion boiler-box.fr vers ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'fin_alerte_ewon' :
                //return (0);
                $tab_message = array();
                $tab_message['erreur'] = false;
                $tab_message['titre'] = 'Rétablissement de la connexion depuis eCatcher vers l\'ewon du site \'' . $ent_site->getIntitule() . '\'';
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>La connexion depuis eCatcher vers l\'ewon du site ' . $ent_site->getIntitule() . ' (' . $ent_site->getAffaire() . ') a été rétablie</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->listes_users_suivi_sites, $ent_site->getAffaire() . ' : Rétablissement de la connexion eCatcher vers l\'ewon du site ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'alerte_netcat' :
                //return (0);
                $tab_message = array();
                $tab_message['erreur'] = true;
                $tab_message['titre'] = 'Perte de connexion depuis http://boiler-box.fr vers \'' . $ent_site->getIntitule() . '\'';
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>La connexion depuis boiler-box.fr vers le site ' . $ent_site->getIntitule() . ' (' . $ent_site->getAffaire() . ') a été perdue</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->listes_users_suivi_sites, $ent_site->getAffaire() . ' : Perte de connexion depuis http://boiler-box.fr vers ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'alerte_ewon' :
                //return (0);
                $tab_message = array();
                $tab_message['erreur'] = true;
                //$tab_message['titre'] = 'Perte de connexion eCatcher vers l\'ewon du site \''.$ent_site->getIntitule().'\'';
                $tab_message['titre'] = 'Perte des connexions à distance vers \'' . $ent_site->getIntitule() . '\'';
                $tab_message['site'] = $ent_site->getIntitule();
                $tab_message['affaire'] = $ent_site->getAffaire();
                $tab_message['message'] = array();
                $tab_message['message'][] = '<b style=\'font-size:20px;\'>La connexion depuis eCatcher vers l\'ewon du site ' . $ent_site->getIntitule() . ' (' . $ent_site->getAffaire() . ') a été perdue</b>';
                $tab_message['message'][] = '';
                $tab_message['message'][] = '';
                $tab_message['message'][] = 'Cordialement,';
                $tab_message['message'][] = 'Assistance IBC';
                // Appel de la fonction :  public function sendMail($emetteur, $destinataire, $sujet, $tab_message)
                $this->service_mailing->sendMailSiteEnErreur('Assistance_IBC@lci-group.fr', $this->listes_users_suivi_sites, '! ' . $ent_site->getAffaire() . ' : Perte des connexions à distance vers ' . $ent_site->getIntitule(), $tab_message);
                break;
            case 'rapport':
                $tab_message = array();
                $tab_message['titre'] = "Rapport journalier d'analyse des sites";
                $tab_message['delais'] = $this->service_configuration->getEntiteDeConfiguration('delais_echec_connexion', $this->delais_echec_connexion_defaut)->getValeur() / 60;
                $tab_message['rapport'] = $this->recuperationInfosRapport();
                //$tab_message['resume'] = $this->resumeRapportDeDuivi($tab_message['rapport']);
                $this->service_mailing->sendMailRapportSuiviDesSites('Assistance_IBC@lci-group.fr', $this->listes_users_suivi_sites, "Rapport Journalier d'analyse des sites", $tab_message);
                break;
        }
        return (0);
    }


    private function checkIndisponibilite($connexion, $status, $entity_siteConnexion)
    {
        switch ($connexion) {
            case 'ecatcher':
                // On récupère la date de la derniere connexion en erreur
                //  - Si elle est nulle et qu'on est pas en erreur : 		C'est que la connexion est toujours ok depuis le dernier test.
                //						et qu'on est en erreur : 			C'est que c'est le debut d'une nouvelle erreur de connexion.
                //                                                              -> On inscrit la date de début d'echec de connexion.
                //                                                              -> On incremente le compteur du nombre d'erreur journalier.
                //  - Si elle n'est pas nulle et qu'on est pas en erreur : 	C'est alors qu'on est dans le cas d'une fin d'erreur.
                //                                                              -> On incremente le compteur de durée d'echec journalier.
                //                                                              -> On réinitialise la date d'echec de connexion à NULL.
                //                            et qu'on est en erreur : 		C'est que la connexion est toujours ko depuis le dernier test
                $date_last_erreur = $entity_siteConnexion->getDateEchecConnexion();
                switch ($date_last_erreur) {
                    case NULL:
                        switch ($status) {
                            case 'online':
                                break;
                            default:
                                $entity_siteConnexion->setDateEchecConnexion(new \Datetime());
                                $entity_siteConnexion->setNbEchecConnexionJournalier($entity_siteConnexion->getNbEchecConnexionJournalier() + 1);
                                break;
                        }
                        break;
                    default:
                        switch ($status) {
                            case 'online':
                                // Récupération de la durée depuis le dernier test
                                $dateEtHeure = new \Datetime();
                                $duree = $date_last_erreur->diff(new \Datetime());
                                // Incrémentation du compteur de durée d'echec
                                $a = new \Datetime('00:00');
                                // Si une durée existe on l'incrémente SINON on la créee  depuis la première heure de ce matin
                                if ($entity_siteConnexion->getDureeEchecConnexionJournalier() != NULL) {
                                    $hours = $entity_siteConnexion->getDureeEchecConnexionJournalier()->format('H');
                                    $minutes = $entity_siteConnexion->getDureeEchecConnexionJournalier()->format('i');
                                    $seconds = $entity_siteConnexion->getDureeEchecConnexionJournalier()->format('s');
                                    $nouvelle_duree_erreur = $a->add(new \DateInterval(sprintf('PT%dH%dM%dS', $hours, $minutes, $seconds)))->add($duree);
                                    if ($this->debug) $this->service_log->setLog($nouvelle_duree_erreur->format("H:i"));
                                    $entity_siteConnexion->setDureeEchecConnexionJournalier($nouvelle_duree_erreur);
                                } else {
                                    $nouvelle_duree_erreur = $a->add($duree);
                                    $entity_siteConnexion->setDureeEchecConnexionJournalier($nouvelle_duree_erreur);
                                    if ($this->debug) $this->service_log->setLog($nouvelle_duree_erreur->format("H:i"));
                                }
                                $entity_siteConnexion->setDateEchecConnexion(NULL);
                                break;
                            default:
                                #$date_last_erreur = new \Datetime();
                                break;
                        }
                        break;
                }
                break;
            case 'boilerbox':
                $date_last_erreur = $entity_siteConnexion->getDateEchecConnexionBB();
                switch ($date_last_erreur) {
                    case NULL:
                        switch ($status) {
                            case true:
                                break;
                            default:
                                $entity_siteConnexion->setDateEchecConnexionBB(new \Datetime());
                                $entity_siteConnexion->setNbEchecConnexionJournalierBB($entity_siteConnexion->getNbEchecConnexionJournalier() + 1);
                                break;
                        }
                        break;
                    default:
                        switch ($status) {
                            case true:
                                // Récupération de la durée depuis le dernier test
                                $dateEtHeure = new \Datetime();
                                $duree = $date_last_erreur->diff(new \Datetime());
                                // Incrémentation du compteur de durée d'echec
                                $a = new \Datetime('00:00');
                                // Si une durée existe on l'incrémente SINON on la créee
                                if ($entity_siteConnexion->getDureeEchecConnexionJournalierBB() != NULL) {
                                    $hours = $entity_siteConnexion->getDureeEchecConnexionJournalierBB()->format('H');
                                    $minutes = $entity_siteConnexion->getDureeEchecConnexionJournalierBB()->format('i');
                                    $seconds = $entity_siteConnexion->getDureeEchecConnexionJournalierBB()->format('s');
                                    $nouvelle_duree_erreur = $a->add(new \DateInterval(sprintf('PT%dH%dM%dS', $hours, $minutes, $seconds)))->add($duree);
                                    if ($this->debug) $this->service_log->setLog($nouvelle_duree_erreur->format("H:i"));
                                    $entity_siteConnexion->setDureeEchecConnexionJournalierBB($nouvelle_duree_erreur);
                                } else {
                                    $nouvelle_duree_erreur = $a->add($duree);
                                    $entity_siteConnexion->setDureeEchecConnexionJournalierBB($nouvelle_duree_erreur);
                                    if ($this->debug) $this->service_log->setLog($nouvelle_duree_erreur->format("H:i"));
                                }
                                $entity_siteConnexion->setDateEchecConnexionBB(NULL);
                                break;
                            default:
                                #$date_last_erreur = new \Datetime();
                                break;
                        }
                        break;
                }
                break;
        }
    }



    // Si la date d/m/Y d'acces au site est différente de la date du jour :
    // - On analyse les données
    // - On envoi le rapport
    // - On réinitialiser les données
    // Fonction qui envoie le rapport d'erreur
    public function gestionRapportConnexion($p_str_date_du_dernier_test)
    {
        $date_du_jour = new \Datetime();
        $str_date_du_jour = $date_du_jour->format('d/m/Y');
        $date_du_dernier_test = new \Datetime($p_str_date_du_dernier_test);
        $str_date_du_dernier_test = $date_du_dernier_test->format('d/m/Y');
        if ($this->debug) $this->service_log->setLog("compare : " . $str_date_du_jour . " et " . $str_date_du_dernier_test);
        if ($str_date_du_jour != $str_date_du_dernier_test) {
            $this->sendMail('rapport', NULL);
        }
    }


    // Analyse et Envoi du rapport
    private function recuperationInfosRapport()
    {
        $str_today = date('d/m/Y');
        $date_yesterday = new \Datetime('yesterday 00:00:01');
        $str_yesterday = $date_yesterday->format('d/m/Y');

        $tab_entities_site = $this->em->getRepository('LciBoilerBoxBundle:Site')->findBy(array(), array('intitule' => 'ASC'));
        $tab_sites = array();
        foreach ($tab_entities_site as $entity_site) {
            if ($this->debug) $this->service_log->setLog('Site ' . $entity_site->getAffaire());
            // Vérifier si des erreurs sont en cours si oui les cloturer et calculer les temps d'indisponibilité
            if ($entity_site->getSiteConnexion()) {
                // La recherche ne se fait que pour les sites étant 'Surveillés'
                if ($entity_site->getSiteConnexion()->getSurveillance() === true) {
                    if (!in_array('non', $entity_site->getSiteConnexion()->getAccesDistant())) {
                        // Si aucune date d'acces en succes est définie ou si la date d'acces en succés n'est pas celle de j-1 alors le site n'a pas été joignable de la journée.
                        // Je définie donc la date d'échec de connexion à j-1 début de journée
                        $date_acces_succes = $entity_site->getDateAccessSucceded();
                        if ($date_acces_succes == null) {
                            $entity_site->getSiteConnexion()->setDateEchecConnexion($date_yesterday);
                            $entity_site->getSiteConnexion()->setNbEchecConnexionJournalier('1');
                        } else if ($date_acces_succes->format('d/m/Y') == $str_today) {
                            $this->variable_en_erreur = $date_acces_succes->format('d/m/Y H:i:s');
                            $this->sendMail('erreur_script_access_succeded', $entity_site);
                            $entity_site->setErreurScript('1');
                        } else if ($date_acces_succes->format('d/m/Y') != $str_yesterday) {
                            $entity_site->getSiteConnexion()->setDateEchecConnexion($date_yesterday);
                            $entity_site->getSiteConnexion()->setNbEchecConnexionJournalier('1');
                        }
                        $date_last_erreur = $entity_site->getSiteConnexion()->getDateEchecConnexion();
                        if ($date_last_erreur !== NULL) {
                            ## Si la date est la date d'erreur est la date du jour : Il y a une erreur dans le scripts
                            if ($date_last_erreur->format('d/m/Y') == $str_today) {
                                $this->variable_en_erreur = $date_last_erreur->format('d/m/Y H:i:s');
                                $this->sendMail('erreur_script_today', $entity_site);
                                $entity_site->setErreurScript('2');
                            } elseif ($date_last_erreur->format('d/m/Y') != $str_yesterday) {
                                # Si la date n'est pas celle d'hier on indique 24 h d'indisponibilité
                                $date_last_erreur = $date_yesterday;
                            }

                            // Récupération de la durée depuis le dernier test jusqu'a minuit
                            $dateEtHeure = new \Datetime('00:00:00');
                            $duree = $date_last_erreur->diff($dateEtHeure);
                            // Incrémentation du compteur de durée d'echec
                            $a = new \Datetime('00:00');
                            // Si une durée d'echec VPN existe on l'incrémente SINON on la créee
                            if ($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier() != NULL) {
                                $hours = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier()->format('H');
                                $minutes = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier()->format('i');
                                $seconds = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier()->format('s');
                                $nouvelle_duree = $a->add(new \DateInterval(sprintf('PT%dH%dM%dS', $hours, $minutes, $seconds)))->add($duree);
                                $entity_site->getSiteConnexion()->setDureeEchecConnexionJournalier($nouvelle_duree);
                                if ($this->debug) $this->service_log->setLog($nouvelle_duree->format("H:i"));
                            } else {
                                $nouvelle_duree = $a->add($duree);
                                $entity_site->getSiteConnexion()->setDureeEchecConnexionJournalier($nouvelle_duree);
                                if ($this->debug) $this->service_log->setLog($nouvelle_duree->format("H:i"));
                            }
                        }
                        $date_last_erreur = $entity_site->getSiteConnexion()->getDateEchecConnexionBB();
                        if ($date_last_erreur !== NULL) {
                            ## Si la date est la date d'erreur est la date du jour : Il y a une erreur dans le scripts
                            if ($date_last_erreur->format('d/m/Y') == $str_today) {
                                $this->sendMail('erreur_script_today', $entity_site);
                                $entity_site->setErreurScript('2');
                            } elseif ($date_last_erreur->format('d/m/Y') != $str_yesterday) {
                                # Si la date n'est pas celle d'hier on indique 24 h d'indisponibilité
                                $date_last_erreur = $date_yesterday;
                            }

                            // Récupération de la durée depuis le dernier test jusqu'a minuit
                            $dateEtHeure = new \Datetime('00:00:00');
                            $duree = $date_last_erreur->diff($dateEtHeure);

                            // Incrémentation du compteur de durée d'echec
                            $a = new \Datetime('00:00');
                            // Si une durée d'echec BoilerBox existe on l'incrémente SINON on la créee
                            if ($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB() != NULL) {
                                $hours = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB()->format('H');
                                $minutes = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB()->format('i');
                                $seconds = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB()->format('s');
                                $nouvelle_duree = $a->add(new \DateInterval(sprintf('PT%dH%dM%dS', $hours, $minutes, $seconds)))->add($duree);
                                $entity_site->getSiteConnexion()->setDureeEchecConnexionJournalierBB($nouvelle_duree);
                                if ($this->debug) $this->service_log->setLog($nouvelle_duree->format("H:i"));
                            } else {
                                $nouvelle_duree = $a->add($duree);
                                $entity_site->getSiteConnexion()->setDureeEchecConnexionJournalierBB($nouvelle_duree);
                                if ($this->debug) $this->service_log->setLog($nouvelle_duree->format("H:i"));
                            }
                        }
                        // On enregistre dans le tableau uniquement les sites présentants des erreurs
                        // On enregistre uniquement si le delais d'echec de connexion depasse la limite (en secondes) définie dans le paramètres de configuration : limite_delais_connexion : Par défaut création du paramètre à 5 minutes
                        $delais_connexion = $this->service_configuration->getEntiteDeConfiguration('delais_echec_connexion', $this->delais_echec_connexion_defaut)->getValeur();

                        if ($entity_site->getSiteConnexion()->getNbEchecConnexionJournalier() == 0 && $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier() != NULL) {
                            $entity_site->setErreurScript('3');
                            $this->sendMail('erreur_script_diff', $entity_site);
                        }
                        if ($entity_site->getSiteConnexion()->getNbEchecConnexionJournalierBB() == 0 && $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB() != NULL) {
                            $entity_site->setErreurScript('4');
                            $this->sendMail('erreur_script_diffBB', $entity_site);
                        }


                        $save_site = false;
                        if ((($entity_site->getSiteConnexion()->getNbEchecConnexionJournalier() != 0) || ($entity_site->getSiteConnexion()->getNbEchecConnexionJournalierBB() != 0)) && ($entity_site->getErreurScript() == null)) {
                            // Vérification du délais d'échec de connexion
                            if ($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier() != NULL) {
                                if ($this->dateIntervalToSeconds($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier()) >= $delais_connexion) {
                                    $save_site = true;
                                }
                            }

                            if ($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB() != NULL) {
                                if ($this->dateIntervalToSeconds($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB()) >= $delais_connexion) {
                                    $save_site = true;
                                }
                            }

                            if ($save_site === true) {
                                if ($this->debug) echo 'Enregistrement de ' . $entity_site->getAffaire() . "\n";
                                $tab_sites[$entity_site->getAffaire()] = array();
                                $tab_sites[$entity_site->getAffaire()]['indisponibilite'] = true;
                                $tab_sites[$entity_site->getAffaire()]['affaire'] = $entity_site->getIntitule();
                                $tab_sites[$entity_site->getAffaire()]['dateAcces'] = $entity_site->getDateAccess();
                                $tab_sites[$entity_site->getAffaire()]['dateAccesEnSucces'] = $entity_site->getDateAccessSucceded();
                                $tab_sites[$entity_site->getAffaire()]['typeAccesEnSucces'] = $entity_site->getTypeAccessSucceeded();
                                $tab_sites[$entity_site->getAffaire()]['erreurScript'] = $entity_site->getErreurScript();

                                if ($entity_site->getSiteConnexion()->getNbEchecConnexionJournalier() != 0) {
                                    $tab_sites[$entity_site->getAffaire()]['nombreEchecConnexionEcatcher'] = $entity_site->getSiteConnexion()->getNbEchecConnexionJournalier();
                                    $tab_sites[$entity_site->getAffaire()]['dureeEchecEcatcher'] = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier();
                                    if ($this->dateIntervalToSeconds($tab_sites[$entity_site->getAffaire()]['dureeEchecEcatcher']) >= $delais_connexion) {
                                        $tab_sites[$entity_site->getAffaire()]['echecEcatcher'] = true;
                                    }
                                }
                                if ($entity_site->getSiteConnexion()->getNbEchecConnexionJournalierBB() != 0) {
                                    $tab_sites[$entity_site->getAffaire()]['nombreEchecConnexionBoilerBox'] = $entity_site->getSiteConnexion()->getNbEchecConnexionJournalierBB();
                                    if (isset($tab_sites[$entity_site->getAffaire()]['dureeEchecEcatcher'])) {
                                        $tab_sites[$entity_site->getAffaire()]['dureeEchecBoilerBox'] = $this->sum_the_time($tab_sites[$entity_site->getAffaire()]['dureeEchecEcatcher']->format('H:i:s'), $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB()->format('H:i:s'));
                                    } else {
                                        $tab_sites[$entity_site->getAffaire()]['dureeEchecBoilerBox'] = $entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB();
                                    }
                                    if ($this->dateIntervalToSeconds($tab_sites[$entity_site->getAffaire()]['dureeEchecBoilerBox']) >= $delais_connexion) {
                                        $tab_sites[$entity_site->getAffaire()]['echecBoilerBox'] = true;
                                    }
                                }
                            } else {
                                if ($this->debug) {
                                    print_r($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalier());
                                    print_r($entity_site->getSiteConnexion()->getDureeEchecConnexionJournalierBB());
                                    if ($this->debug) echo "delais d'echec trop faible pour le site " . $entity_site->getAffaire() . "\n";
                                }
                                // Permet d'indiquer la liste des sites ok
                                $tab_sites = $this->saveSiteConnexionOk($entity_site, $tab_sites);
                            }
                        } else {
                            $tab_sites = $this->saveSiteConnexionOk($entity_site, $tab_sites);
                        }

                        // Réinitialisation des données
                        $entity_site->getSiteConnexion()->setNbEchecConnexionJournalier(0);
                        $entity_site->getSiteConnexion()->setDureeEchecConnexionJournalier(NULL);
                        $entity_site->getSiteConnexion()->setDateEchecConnexion(NULL);
                        $entity_site->getSiteConnexion()->setNbEchecConnexionJournalierBB(0);
                        $entity_site->getSiteConnexion()->setDureeEchecConnexionJournalierBB(NULL);
                        $entity_site->getSiteConnexion()->setDateEchecConnexionBB(NULL);
                        $entity_site->setErreurScript(null);

                        // Enregistre en base des données à rafraichir / réinitialiser après envoi du rapport
                        // DEBUG - METTRE EN COMMENTAIRE POUR FAIRE DES tests
                        $this->em->flush();
                    }
                }
            }
        }
        return ($tab_sites);
    }

    private function saveSiteConnexionOk($entity_site, $tab_sites)
    {
        $tab_sites[$entity_site->getAffaire()] = array();
        $tab_sites[$entity_site->getAffaire()]['indisponibilite'] = false;
        $tab_sites[$entity_site->getAffaire()]['affaire'] = $entity_site->getIntitule();
        $tab_sites[$entity_site->getAffaire()]['dateAcces'] = $entity_site->getDateAccess();
        $tab_sites[$entity_site->getAffaire()]['dateAccesEnSucces'] = $entity_site->getDateAccessSucceded();
        $tab_sites[$entity_site->getAffaire()]['typeAccesEnSucces'] = $entity_site->getTypeAccessSucceeded();
        $tab_sites[$entity_site->getAffaire()]['erreurScript'] = $entity_site->getErreurScript();
        return ($tab_sites);
    }

    /* On ne place dans le tableau tab_resume que les sites présentant des erreurs
    private function resumeRapportDeDuivi($tab_rapport)
    {
        $tab_resume = array();
        foreach($tab_rapport as $key => $affaire)
        {
            if (isset($affaire['nombreEchecConnexionEcatcher']))
            {
                array_push($tab_resume, $key);
            } else if (isset($affaire['nombreEchecConnexionBoilerBox']))
            {
                array_push($tab_resume, $key);
            }
        }
        return $tab_resume;
    }
    */


    private function dateIntervalToSeconds($obj_date)
    {
        $hours = $obj_date->format('H');
        $minutes = $obj_date->format('i');
        $secondes = $obj_date->format('s');

        $parametreDateInterval = 'PT';
        $parametreDateInterval .= $hours . 'H';
        $parametreDateInterval .= $minutes . 'M';
        $parametreDateInterval .= $secondes . 'S';

        $dateInterval = new \DateInterval($parametreDateInterval);

        $reference = new \DateTimeImmutable;
        $endTime = $reference->add($dateInterval);
        return $endTime->getTimestamp() - $reference->getTimestamp();
    }


    // Ajoute la durée d'indisponibilité eCatcher à la durée d'indisponibilité BoilerBox
    private function sum_the_time($time1, $time2)
    {
        $times = array($time1, $time2);
        $seconds = 0;
        foreach ($times as $time) {
            list($hour, $minute, $second) = explode(':', $time);
            $seconds += $hour * 3600;
            $seconds += $minute * 60;
            $seconds += $second;
        }
        $hours = floor($seconds / 3600);
        $seconds -= $hours * 3600;
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;
        if ($seconds < 9) {
            $seconds = "0" . $seconds;
        }
        if ($minutes < 9) {
            $minutes = "0" . $minutes;
        }
        if ($hours < 9) {
            $hours = "0" . $hours;
        }
        // Pour débuguer le cas : Si on retourne une date à J+1 il y a une erreur du nombre d'heures d'indisponibilité affiché
        // On compare la date du jour avec la date incrémenté
        // Si la date incrémenté = J+1 on retourne la date du jours à 23:59:59
        $date_du_jour = new \Datetime('00:00');
        $date_retour = new \Datetime('00:00');

        $interval = new \DateInterval('PT' . $hours . 'H' . $minutes . 'M' . $seconds . 'S');
        $date_retour->add($interval);
        if ($date_du_jour->diff($date_retour)->format('%d') != 0) {
            return new \Datetime('23:59:59');
        } else {
            return $date_retour;
        }
    }


}
