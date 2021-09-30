<?php 
//src/Ipc/ProgBundle/Services/ServiceConfiguration
namespace Lci\BoilerBoxBundle\Services;

use Lci\BoilerBoxBundle\Entity\Configuration;

class ServiceConfiguration {
    protected $dbh;
	protected $em;
    protected $entity_utilisateur;

    public function __construct($connexion, $securityTokenContext, \Doctrine\ORM\EntityManager $em) {
		$this->dbh = $connexion->getDbh();
		if ($securityTokenContext->getToken() != null) {
			$this->entity_utilisateur = $securityTokenContext->getToken()->getUser();
		}
		$this->em = $em;
    }

    // Fonction retournant la date et l'heure de l'ipc
    public function maj_date() {
		date_default_timezone_set('UTC');
		$tab_mois = array("Jan", "Fév", "Mar", "Avr", "Mai", "Jun", "Jul", "Aou", "Sep", "Oct", "Nov", "Déc");
		$date_actuelle = new \Datetime();
		$tab_de_date['heure'] = $date_actuelle->format('H:i:s');
		$tab_de_date['jour'] = $date_actuelle->format('d').' '.$tab_mois[intval($date_actuelle->format('m')) - 1 ].' '.$date_actuelle->format('Y');
		$tab_de_date['timestamp'] = $date_actuelle->getTimestamp();
		return($tab_de_date);
    }

	// Compte le nombre de problèmes affectés à l'utilisateur connecté
	public function getNombreProblemes() {
	    return $this->em->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myCountOwnProblemes($this->entity_utilisateur);
	}

	// Compte le nombre de problèmes non clos affectés à l'utilisateur connecté
	public function getNombreProblemesNonClos(){
		return $this->em->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myCountOwnNoClosProblemes($this->entity_utilisateur);
	}


	// Récupére un paramètre de configuration et le créée si il n'existe pas.
	public function getEntiteDeConfiguration($nom_du_parametre, $valeur_par_defaut = null) {
		$entity_configuration =  $this->em->getRepository('LciBoilerBoxBundle:Configuration')->findOneByParametre($nom_du_parametre);
       	if ($entity_configuration == null) {
            $entity_configuration = new Configuration();
            $entity_configuration->setParametre($nom_du_parametre);
			if ($valeur_par_defaut !== null) {
            	$entity_configuration->setValeur($valeur_par_defaut);
			}
			$this->em->persist($entity_configuration);
			$this->em->flush();
        }
		return $entity_configuration;
	}
}
