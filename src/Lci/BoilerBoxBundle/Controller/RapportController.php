<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;


class RapportController extends Controller {
	// Rapport hebdomadaire qui indique la durée d'indisponibilité des sites non accessibles
	public function rapportDisponibiliteAction() {
		$service_rapport = $this->get('lci_boilerbox.rapport');
		$service_rapport->setRapportDisponibilite();
		return new Response();
	}
}

