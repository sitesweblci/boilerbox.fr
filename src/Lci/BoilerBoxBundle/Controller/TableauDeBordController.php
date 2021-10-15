<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Lci\BoilerBoxBundle\Entity\Site;
use Lci\BoilerBoxBundle\Entity\Replication;


class TableauDeBordController extends AbstractController
{
    public function indexAction(): Response
    {
		return $this->getTableauDesEntitesDeReplications();
    }


	/* Récupère l'ensemble des sites présents dans les bases BoilerBox et BoilerBox-LTS */
	protected function getTableauDesEntitesDeReplications()
    {
		$tableau_des_entites_de_replications = [];
		// Récupération de la liste des masters
		$infos_replications = $this->getDoctrine()->getRepository(Replication::class)->getReplicationsInfos();
		if ($infos_replications)
        {
			foreach($infos_replications as $info_replication)
			{
				$code_affaire = null;
				// Récupération du site associé à la réplication
				// Indicatif du site + code affaire
				// 1 : Site LCI de type C***
				// 2 : Site Altas
				// 3 : Site LCI de type D***
				$connexion_name = $info_replication['Connection_name'];
				$indicatif_connexion_name = substr(substr($connexion_name, -4), 0, 1);	
				switch($indicatif_connexion_name)
				{
					case 1 :
						$code_affaire = 'C';
						break;
					case 2 :
						$code_affaire = 'M';
                        break;
					case 3 :
						$code_affaire = 'D';
                        break;
				}
				if ($code_affaire != null)
				{
					$code_affaire .= substr($info_replication['Connection_name'], -3);
					$e_site = $this->getDoctrine()->getRepository(Site::class)->findOneByAffaire($code_affaire);
					/* Si le site existe on créé l'objet replication lié au site 
						Sinon on log l'erreur / on peut assi envoyer un mail pour indiquer qu'un site master n'existe pas en base .
					*/
					if ($e_site)
					{
						$e_replication = new Replication();
						$e_replication->setSite($e_site);
						$e_replication->setEtatSql($info_replication['Slave_SQL_Running']);
						if (isset($info_replication['Slave_SQL_State']))
		            	{
		            	    $e_replication->setMessageEtatSql($info_replication['Slave_SQL_State']);
		            	} else if (isset($info_replication['Slave_SQL_Running_State']))
		            	{
		            	    $e_replication->setMessageEtatSql($info_replication['Slave_SQL_Running_State']);
		            	} else {
							$e_replication->setMessageEtatSql(null);
						}
		            	$e_replication->setEtatIo($info_replication['Slave_IO_Running']);
		            	$e_replication->setMessageEtatIo($info_replication['Slave_IO_State']);
		            	$e_replication->setRetard($info_replication['Seconds_Behind_Master']);
		            	$e_replication->setMessageErreur($info_replication['Last_IO_Error']);
						$tableau_des_entites_de_replications[] = $e_replication;
					} else {
						// MAIL ERREUR Envoi d'un mail d'erreur
					}
				}
			}
		}
		return $this->render('LciBoilerBoxBundle:TableauDeBord:accueil.html.twig', array(
			'tableau_des_entites_de_replications' => $tableau_des_entites_de_replications
    	));


		/* Fonction qui permet de récupérer les informations de synchronisation pour un site 
			! Exception levée - Erreur du programme si le site passé en argument n'est pas dans la liste des masters
		*/
		/*
		$es_sites = $this->getDoctrine()->getRepository(Site::class)->findAll();
		foreach ($es_sites as $e_site)
		{
			print_r($this->fillEntityReplication($e_site));	
			echo "<br />";
		}
		*/
	}



	/*
	* Return Replication $replication
	*/
	protected function fillEntityReplication($e_site)
	{
		// Création d'une entité réplication
        $e_replication = new Replication();

		$infos_replication = $this->getDoctrine()->getRepository(Replication::class)->getSiteLciReplication($e_site);
		if ($infos_replication)
		{
			// récupération des indicateurs
			$e_replication->setSite($e_site);
			$e_replication->setEtatSql($infos_replication[0]['Slave_SQL_Running']);
			// Indicateur retourné par la commande : getSiteLciReplication
			if (isset($infos_replication[0]['Slave_SQL_State']))
			{
            	$e_replication->setMessageEtatSql($infos_replication[0]['Slave_SQL_State']);
			} else if (isset($infos_replication[0]['Slave_SQL_Running_State']))
			{
				$e_replication->setMessageEtatSql($infos_replication[0]['Slave_SQL_Running_State']);
			}
            $e_replication->setEtatIo($infos_replication[0]['Slave_IO_Running']);
            $e_replication->setMessageEtatIo($infos_replication[0]['Slave_IO_State']);
            $e_replication->setRetard($infos_replication[0]['Seconds_Behind_Master']);
            $e_replication->setMessageErreur($infos_replication[0]['Last_IO_Error']);
		}
		return($e_replication);
	}
}
