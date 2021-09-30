<?php
#src/Lci/BoilerBoxBundle/Services/ServiceRapport
namespace  Lci\BoilerBoxBundle\Services;

class ServiceRapport {
	private $em;
	private $service_configuration;
	private $service_mail;

	public function __construct($doctrine, $service_configuration, $service_mail) {
		$this->em = $doctrine->getManager();
		$this->service_configuration = $service_configuration;
		$this->service_mail = $service_mail;
	}		

	// Analyse de la date de disponibilité des sites.
	// 1 ) Récupération de l'heure du dernier test Netcat qui va servir d'heure de référence
	// 2 ) Si l'heure est > 1 journée de la date courante : La crontab n'effectue plus les tests -> Affichage d'une alerte dans le rapport.
	// 3 ) Comparaison de l'heure de référence avec l'heure de test netcat réussi pour chaqu'un des sites.
	// 4 ) Si il y a plus d'une journée (24h) entre l'heure de référence et l'heure de réussite de la commande netcat c'est que le site est indisponible depuis trop longtempts -> Affichage d'une alerte dans le rapport.
	// - L'adresse ip des live est la même que celle des sites : On n'effectue donc pas les tests pour les sites Live
	public function setRapportDisponibilite() {
		$erreur = false;
		// Liste des site n'ayant jamais répondus à la commande Netcat
		$t_entities_sites_injoignables = array();
		// Liste des sites n'ayant pas répondus depuis plus d'une journée à la commande Netcat
		$t_entities_sites_indisponibles = array();
		// Message d'erreur lorsque la crontab n'execute pas le script d'indisponibilité
		$erreur_contab = null;

        $date_du_jour = new \Datetime(date('Y-m-d'));
        // On répértorie tous les sites dont la date d'accès en succés est différente de la date du jour (paramètre dateAccessSucceded)
        $t_entities_sites = $this->em->getRepository('LciBoilerBoxBundle:Site')->findAll();
		// MESSAGE DEV : echo "Comparaison avec la date : ".$date_du_jour->format('d-m-Y H:i:s').'<br />';

		// 1 
		// Pour trouver l'heure du dernier test Netcat, on récupére le paramètre de configuration 'date_test_de_disponibilite'
		$date_du_jour = new \Datetime();
		// MESSAGE DEV : echo $date_du_jour->format('d-m-Y H:i:s')."<br />";
		$date_de_reference = date_create_from_format('d-m-Y H:i:s', $this->service_configuration->getEntiteDeConfiguration('date_test_de_disponibilite', $date_du_jour->format('d-m-Y H:i:s'))->getValeur());

		// 2 )
		if (date_diff($date_de_reference, $date_du_jour)->days > 1) {
			$erreur = true;
			$erreur_contab = "! Attention : La crontab n'execute pas le script d'indisponibilité ! ";
		}

		// Récupération de la date de référence sans la partie horaire
		$date_de_reference_min = new \Datetime(date('d-m-Y', $date_de_reference->getTimestamp()));
		// 3 )
		$pattern_site_live = '/supervision/';
       	foreach ($t_entities_sites as $entity_site) 
		{
			if ($entity_site->getSiteConnexion() != null) 
			{
				// Pas de test de disponibilité pour les sites Live
				if (preg_match($pattern_site_live, $entity_site->getSiteConnexion()->getUrl(), $matches) == 0) 
				{
					if ($entity_site->getDateAccessSucceded() !== null) 
					{
						// Récupération de la date de disponibilité sans la partie horaire
						$date_site_disponible_min = new \Datetime(date('d-m-Y', $entity_site->getDateAccessSucceded()->getTimestamp()));
        			    if ($date_de_reference_min > $date_site_disponible_min) 
						{
							$interval = date_diff($date_site_disponible_min, $date_de_reference_min);
        			        // MESSAGE DEV : echo $entity_site->getAffaire()." - Le site ".$entity_site->getIntitule()." est non accessible depuis ".$interval->days." jours<br />";
							// MESSAGE DEV : echo '<br />';
							$erreur = true;
							array_push($t_entities_sites_indisponibles, $entity_site);
        			    }
        			} else {
						// ALERTE DANS LE RAPPORT : Le site n'a jamais été accessible
						// MESSAGE DEV : echo $entity_site->getAffaire()." - Le site ".$entity_site->getIntitule()." n'a jamais été accessible<br />";
						$erreur = true;
						array_push($t_entities_sites_injoignables, $entity_site);
					}
				}
			}
		}
		// 4 ) Ecriture du rapport d'erreur
		$this->service_mail->sendRapportIndisponibilite($t_entities_sites_injoignables, $t_entities_sites_indisponibles, $erreur_contab);
	}
}
