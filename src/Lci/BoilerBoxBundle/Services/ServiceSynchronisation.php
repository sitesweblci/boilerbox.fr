<?php
//src/Lci/BoilerBoxBundle/Services/ServiceSynchronisation
//Service que gère les sycrhonisation MySQL
namespace Lci\BoilerBoxBundle\Services;

use Lci\BoilerBoxBundle\Entity\configuration;

class ServiceSynchronisation {

	public function __construct($doctrine, $service_configuration, $service_log, $service_mailing, $mail_debuggeur) 
	{
		$this->date_access = new \Datetime();
		$this->mail_debuggeur = $mail_debuggeur;
		$this->em = $doctrine->getManager();
		$this->service_configuration = $service_configuration;
		$this->service_log = $service_log;
		$this->service_mailing = $service_mailing;
		$this->debug = false;
		/*
		$listes_users_suivi_sites = array();
    	foreach($ents_users as $ent_user) {
			array_push($listes_users_suivi_sites,$ent_user->getEmail());
    	}
		$this->listes_users_suivi_sites = $listes_users_suivi_sites;
        $this->ewons = array();
		*/
	}

	// Fonction qui redemmare tous les slaves : Permet de géré l'arrêt sans erreur des synchronisations
	// type est soit all pour relancé toutes les synchronisations
	//          soit une affaire pour relancer uniquement la synchro sur cette affaire
	public function relanceSynchro($arg1 = null)
	{
		$erreur = null;
		if (is_null($arg1))
		{
			shell_exec("mysql -u 'cargo' -p'adm5667' -Bse 'START all slaves'");
			return "Commande de relance des synchronisations effectuée";
		} else {
			$code_type_affaire = substr(strtolower($arg1), 0, 1);
			$code_affaire = substr($arg1, 1);
			//    1xxx pour les sites LCI : Cxxx
    		//    2xxx pour les sites LTS : Mxxx
    		//    3xxx pour les sites LCI : Dxxx
			switch ($code_type_affaire)
			{
				case 'c':
					$master = 'master1'.$code_affaire;
				break;
				case 'm':
					$master = 'master2'.$code_affaire;
                break;
                case 'd':
                    $master = 'master3'.$code_affaire;
					break;
				default:
					$erreur = 'Argument incorrect : Veuillez indiquer un site parmis la liste suivante : Cxxx - Dxxx - Mxxx';
					break;
			}
			if ($erreur)
			{
				return $erreur;
			} else {
				shell_exec("mysql -u 'cargo' -p'adm5667' -Bse 'START slave \"$master\"'");
				return "Commande de relance de la synchronisation pour l'affaire $arg1 effectuée";
			}
		}
	}


}

