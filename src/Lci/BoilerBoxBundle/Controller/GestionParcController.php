<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Lci\BoilerBoxBundle\Entity\ObjRechercheProbleme;
use Lci\BoilerBoxBundle\Form\Type\ObjRechercheProblemeType;

use Lci\BoilerBoxBundle\Form\Type\ProblemeTechniqueType;
use Lci\BoilerBoxBundle\Entity\ProblemeTechnique;

use Lci\BoilerBoxBundle\Entity\Module;
use Lci\BoilerBoxBundle\Form\Type\ModuleType;

use Lci\BoilerBoxBundle\Entity\Equipement;
use Lci\BoilerBoxBundle\Form\Type\EquipementType;



class GestionParcController extends Controller {

protected $nombre_problemes_affectes;

public function constructPerso() {
	$service_configuration = $this->get('lci_boilerbox.configuration');
    $this->nombre_problemes_affectes = $service_configuration->getNombreProblemesNonClos();
}

public function accueilAction() {
	$this->constructPerso();
    return $this->render('LciBoilerBoxBundle:GestionParc:accueil.html.twig', array(
		'nombre_problemes_affectes' => $this->nombre_problemes_affectes
	));
}


// Affiche la liste des problèmes non clos du module sélectionné dans la partie "Parc des modules"
public function afficheModuleProblemesAction(Request $request) {
	$this->constructPerso(); 
	$objet_recherche_probleme = new ObjRechercheProbleme();
	$objet_recherche_probleme->setModuleId($_POST['module_id']);
	$objet_recherche_probleme->setNonCloture(true);
	$request->getSession()->set('ObjetRecherche', $objet_recherche_probleme);
	$request->getSession()->set('fromVar', 'parcDesModules');
	// Recherche en fonction des paramètres de l'objet
    $tableau_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myFindProblemes($objet_recherche_probleme, 'entities', 'dateSignalement', 'DESC');
    return $this->render('LciBoilerBoxBundle:GestionParc:liste_problemes.html.twig', array(
        'tableau_problemes' => $tableau_probleme_technique
    ));
}


// Affiche la liste des problèmes non clos du module sélectionné dans la partie "Parc des équipements"
public function afficheEquipementProblemesAction(Request $request) {
    $this->constructPerso();
    $objet_recherche_probleme = new ObjRechercheProbleme();
    $objet_recherche_probleme->setEquipementId($_POST['equipement_id']);
    $objet_recherche_probleme->setNonCloture(true);
    $request->getSession()->set('ObjetRecherche', $objet_recherche_probleme);
	$request->getSession()->set('fromVar', 'parcDesEquipements');
    // Recherche en fonction des paramètres de l'objet
    $tableau_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myFindProblemes($objet_recherche_probleme, 'entities', 'dateSignalement', 'DESC');
    return $this->render('LciBoilerBoxBundle:GestionParc:liste_problemes.html.twig', array(
        'tableau_problemes' => $tableau_probleme_technique
    ));
}


public function rechercheProblemeAction(Request $request) {
	$this->constructPerso();
	$objet_recherche_probleme = new ObjRechercheProbleme();
	$formulaire = $this->createForm(ObjRechercheProblemeType::class, $objet_recherche_probleme);
	if ($request->getMethod() == 'POST') {
		$formulaire->handleRequest($request);
		if ($formulaire->isValid()) {
			if (isset($_POST['obj_recherche_probleme']['chk_intervenant'])) {
				$objet_recherche_probleme->setIntervenantId($_POST['obj_recherche_probleme']['intervenant']);
			}
            if (isset($_POST['obj_recherche_probleme']['chk_module'])) {
                $objet_recherche_probleme->setModuleId($_POST['obj_recherche_probleme']['module']);
            }
            if (isset($_POST['obj_recherche_probleme']['chk_equipement'])) {
                $objet_recherche_probleme->setEquipementId($_POST['obj_recherche_probleme']['equipement']);
			}
			$request->getSession()->set('ObjetRecherche', $objet_recherche_probleme);
			// Recherche en fonction des paramètres de l'objet
			$tableau_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myFindProblemes($objet_recherche_probleme, 'entities', 'dateSignalement', 'DESC');
			return $this->render('LciBoilerBoxBundle:GestionParc:liste_problemes.html.twig', array(
				'tableau_problemes' => $tableau_probleme_technique	
			));
		} else {
            $request->getSession()->getFlashBag()->add('info', 'Formulaire invalide : '.$formulaire->getErrors(true));
        }
	}
	return $this->render('LciBoilerBoxBundle:GestionParc:formulaire_recherche.html.twig', array(
		'form' => $formulaire->createView(),
		'nombre_problemes_affectes' => $this->nombre_problemes_affectes
	));
}

// Affiche tous les problèmes techniques : Par défaut problèmes non clos
public function afficheListeProblemesAction(Request $request) {
	$this->constructPerso();
	$objet_recherche_probleme = new ObjRechercheProbleme();
	$objet_recherche_probleme->setNonCloture(true);
	$request->getSession()->set('ObjetRecherche', $objet_recherche_probleme);
	$tableau_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myFindProblemes($objet_recherche_probleme, 'entities', 'dateSignalement', 'DESC');
	return $this->render('LciBoilerBoxBundle:GestionParc:liste_problemes.html.twig', array(
        'tableau_problemes' => $tableau_probleme_technique,
		'nombre_problemes_affectes' => $this->nombre_problemes_affectes
    ));
}

public function triRechercheProblemeAction(Request $request) {
	$this->constructPerso();
	$old_champs_tri = $request->getSession()->get('ChampsTri', 'dateSignalement');
	$objet_recherche_probleme = $request->getSession()->get('ObjetRecherche', array());
	$ordre_tri = $request->getSession()->get('OrdreTri', 'DESC');
    if (isset($_POST['champs_tri'])) {
        $champs_tri = $_POST['champs_tri'];
    } else {
		$champs_tri = $old_champs_tri;
	}
	//echo "Tri sur ".$champs_tri."<br >";
	$request->getSession()->set('ChampsTri', $champs_tri);
	// Si un tri sur une nouvelle colonne est demandé : Tri DESC
	if ($champs_tri != $old_champs_tri) {
		$request->getSession()->set('OrdreTri', 'ASC');
		$ordre_tri = 'ASC';
	} else {
		// Si une tri sur la même colonne est demandé, on inverse le sens du tri
		if ($ordre_tri == 'ASC') {
			$request->getSession()->set('OrdreTri', 'DESC');
			$ordre_tri = 'DESC';
		} else {
			$request->getSession()->set('OrdreTri', 'ASC');
			$ordre_tri = 'ASC';
		}
	}
	$tableau_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myFindProblemes($objet_recherche_probleme, 'entities', $champs_tri, $ordre_tri);
    return $this->render('LciBoilerBoxBundle:GestionParc:liste_problemes.html.twig', array(
        'tableau_problemes' => $tableau_probleme_technique,
		'nombre_problemes_affectes' => $this->nombre_problemes_affectes
    ));
}

public function exportListeProblemesAction(Request $request) {
	$this->constructPerso();
	$objet_recherche_probleme = $request->getSession()->get('ObjetRecherche', array());
	$champs_tri = $request->getSession()->get('ChampsTri', 'dateSignalement');
	$ordre_tri = $request->getSession()->get('OrdreTri', 'DESC');
	$tableau_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myFindProblemes($objet_recherche_probleme, 'entities', $champs_tri, $ordre_tri);

    // IMPRESSION CSV des données présentent dans liste_req
    $chemin = 'tmp/';
    $fichier = 'problemes_techniques_'.date('YmdHis').'.csv';
    $delimiteur = ';';
    $fichier_csv = fopen($chemin.$fichier, 'w+');

    // Correction des caractères spéciaux pour affichage du csv dans excel
    fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));

    // Lecture des données et enregistrement dans le fichier 
    // Titre
    $tab_tmp = array('Problèmes Techniques');
    fputcsv($fichier_csv, $tab_tmp, $delimiteur);
	$tab_tmp = array('Référence', 'Bloquant', 'Date de correction', 'Date de signalement', 'Opérateur', 'Module(s)', 'Equipement', 'Description', 'Solution', 'Fichier(s) joint(s)');
	fputcsv($fichier_csv, $tab_tmp, $delimiteur);
	foreach($tableau_probleme_technique as $key => $tab_probleme) {
		// Référence
		$tab_tmp = array($tab_probleme->getId());
		// Bloquant
		if ($tab_probleme->getBloquant() == true) {
			array_push($tab_tmp, 'X');
		} else {
			array_push($tab_tmp, '');
		}
		// Coorigé
		if ($tab_probleme->getCorrige() == true) {
            if ($tab_probleme->getDateCorrection() != null) {
                 array_push($tab_tmp, $tab_probleme->getDateCorrection()->format('d/m/Y'));
            } else {
				array_push($tab_tmp, 'X');
			}
        } else {
			array_push($tab_tmp, '');
		}
		// Date de signalement, Intervenant
		array_push($tab_tmp, $tab_probleme->getDateSignalement()->format('d/m/Y'), $tab_probleme->getUser()->getUsername().' ('.$tab_probleme->getUser()->getLabel().')');
		// Modules, Equipement, Description, Solution
		$champs_module = '';
		foreach($tab_probleme->getModule() as $key2 => $module) {
			$champs_module .= $module->getNumero().' - ';	
		}
		$champs_module = substr($champs_module, 0, -3);
		array_push($tab_tmp, $champs_module, $tab_probleme->getEquipement()->getType(), $tab_probleme->getDescription(), $tab_probleme->getSolution());
		// Fichiers joints
		if (count($tab_probleme->getFichiersJoint()) != 0) {
			array_push($tab_tmp, 'X');
		} else {
			array_push($tab_tmp, '');
		}
		fputcsv($fichier_csv, $tab_tmp, $delimiteur);
	}
    fclose($fichier_csv);

    $response = new Response();
    $response->headers->set('Content-Type', 'application/force-download');
    $response->headers->set('Content-Disposition', 'attachment;filename="'.$fichier.'"');
    $response->headers->set('Content-Length', filesize($chemin.$fichier));
    $response->setContent(file_get_contents($chemin.$fichier));
    $response->setCharset('UTF-8');
    return $response;
}

// Fonction qui affiche la liste des problèmes techniques affectés à l'utilisateur courant et en statut non cloturé
public function affectationAction(Request $request){
	$this->constructPerso();
    $entityUser = $this->getUser();
	$username = $entityUser->getUsername();
	$objet_recherche_probleme = new ObjRechercheProbleme();
	$objet_recherche_probleme->setIntervenantId($entityUser->getId());
	$objet_recherche_probleme->setNonCloture(true);
	$request->getSession()->set('ObjetRecherche', $objet_recherche_probleme);
    $tableau_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->myFindProblemes($objet_recherche_probleme, 'entities', 'dateSignalement', 'DESC');
    return $this->render('LciBoilerBoxBundle:GestionParc:liste_problemes.html.twig', array(
        'tableau_problemes' => $tableau_probleme_technique,
		'nombre_problemes_affectes' => $this->nombre_problemes_affectes
    ));
}

// Fonction appelée - lors du clic sur Modif par un utilisateur autre que le gestionnaire du parc de la liste des problemes.
//					- lors du clic sur 'Version imprimable' par le gestionnaire de parc.
public function problemeTechniqueAffichageAction(Request $request) {
	$this->constructPerso();
	$date_jour = new \Datetime();
	$session = $request->getSession();
    $entity_probleme_technique = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:ProblemeTechnique')->find($session->get('id_entity_probleme_technique'));
	$form_probleme_technique = $this->createForm(ProblemeTechniqueType::class, $entity_probleme_technique);
	if ($request->getMethod() == 'POST'){
		$form_probleme_technique->handleRequest($request);
		if ($form_probleme_technique->isValid()){
			$this->getDoctrine()->getManager()->flush();
			$request->getSession()->getFlashBag()->add('info', 'Problème technique mis à jour');
		} else {
			$request->getSession()->getFlashBag()->add('info', 'Formulaire invalide : '.$form_probleme_technique->getErrors(true));
		}
	}
    return $this->render('LciBoilerBoxBundle:GestionParc:affiche_probleme_technique.html.twig', array(
		'date_jour' => $date_jour->format('d/m/Y'),
        'entity_probleme' => $entity_probleme_technique,
        'nombre_problemes_affectes' => $this->nombre_problemes_affectes,
		'form'	=> $form_probleme_technique->createView()
    ));
}


// Affichage du parc des équipements
public function gestionEquipementsAction(){
    $this->constructPerso();
    $entities_equipements = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Equipement')->myFindEquipementsTries('type', 'ASC');
    return $this->render('LciBoilerBoxBundle:GestionParc:parc_equipements.html.twig', array(
        'nombre_problemes_affectes' => $this->nombre_problemes_affectes,
        'entities_equipements' => $entities_equipements
    ));
}

public function changeEquipementsAction(Request $request) {
    // Fonction qui retourne un formulaire d'équipement sur lequel on ne peut que le définir en tant qu'actif ou inactif
    $em = $this->getDoctrine()->getManager();
    if (isset($_POST['id_equipement'])) {
        $idEquipement = $_POST['id_equipement'];
    } elseif (isset($_POST['equipement']['id'])) {
        $idEquipement = $_POST['equipement']['id'];
    } else {
        $request->getSession()->getFlashBag()->add('info', 'Equipement non renseigné');
        return $this->redirect($this->generateUrl('lci_gestion_equipements'));
    }
    $entity_equipement = $em->getRepository('LciBoilerBoxBundle:Equipement')->find($idEquipement);
    $form_equipement = $this->createForm(EquipementType::class, $entity_equipement);
    if ($request->getMethod() == 'POST') {
        $form_equipement->handleRequest($request);
        if ($form_equipement->isSubmitted()) {
            if ($form_equipement->isValid()) {
                $request->getSession()->getFlashBag()->add('info', 'Enregistrement effectué.');
                $em->persist($entity_equipement);
                $em->flush();
            } else {
                $request->getSession()->getFlashBag()->add('info', 'Formulaire invalide : '.$form_equipement->getErrors());
            }
            return $this->redirect($this->generateUrl('lci_gestion_equipements'));
        }
    }
    $nombre_problemes_affectes = $this->get('lci_boilerbox.configuration')->getNombreProblemesNonClos();
    return $this->render('LciBoilerBoxBundle:GestionParc:change_equipement.html.twig', array(
        'form' => $form_equipement->createView(),
        'nombre_problemes_affectes' => $nombre_problemes_affectes
    ));

}





// Fonction qui gère le parc des modules
public function gestionModulesAction(){
	$this->constructPerso();
	// Par défaut affiche le parc des modules existant
	$entities_modules = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Module')->myFindModulesTries('numero', 'ASC');
	return $this->render('LciBoilerBoxBundle:GestionParc:parc_modules.html.twig', array(
		'nombre_problemes_affectes' => $this->nombre_problemes_affectes,
		'entities_modules' => $entities_modules
	));
}

public function triParcModulesAction(Request $request){
    $this->constructPerso();
    $old_champs_tri = $request->getSession()->get('ChampsTriParcModules', 'numero');
    $ordre_tri = $request->getSession()->get('OrdreTriParcModules', 'DESC');
    if (isset($_POST['champs_tri'])) {
        $champs_tri = $_POST['champs_tri'];
    } else {
        $champs_tri = $old_champs_tri;
    }
    $request->getSession()->set('ChampsTriParcModules', $champs_tri);
    // Si un tri sur une nouvelle colonne est demandé : Tri DESC
    if ($champs_tri != $old_champs_tri) {
        $request->getSession()->set('OrdreTriParcModules', 'ASC');
        $ordre_tri = 'ASC';
    } else {
        // Si une tri sur la même colonne est demandé, on inverse le sens du tri
        if ($ordre_tri == 'ASC') {
            $request->getSession()->set('OrdreTriParcModules', 'DESC');
            $ordre_tri = 'DESC';
        } else {
            $request->getSession()->set('OrdreTriParcModules', 'ASC');
            $ordre_tri = 'ASC';
        }
    }
    $entities_parc_modules_trie = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Module')->myFindModulesTries($champs_tri, $ordre_tri);
    return $this->render('LciBoilerBoxBundle:GestionParc:parc_modules.html.twig', array(
		'entities_modules' => $entities_parc_modules_trie
    ));
}


public function triParcEquipementsAction(Request $request){
    $this->constructPerso();
    $old_champs_tri = $request->getSession()->get('ChampsTriParcEquipements', 'numero');
    $ordre_tri = $request->getSession()->get('OrdreTriParcEquipements', 'DESC');
    if (isset($_POST['champs_tri'])) {
        $champs_tri = $_POST['champs_tri'];
    } else {
        $champs_tri = $old_champs_tri;
    }
    $request->getSession()->set('ChampsTriParcEquipements', $champs_tri);
    // Si un tri sur une nouvelle colonne est demandé : Tri DESC
    if ($champs_tri != $old_champs_tri) {
        $request->getSession()->set('OrdreTriParcEquipements', 'ASC');
        $ordre_tri = 'ASC';
    } else {
        // Si une tri sur la même colonne est demandé, on inverse le sens du tri
        if ($ordre_tri == 'ASC') {
            $request->getSession()->set('OrdreTriParcEquipements', 'DESC');
            $ordre_tri = 'DESC';
        } else {
            $request->getSession()->set('OrdreTriParcEquipements', 'ASC');
            $ordre_tri = 'ASC';
        }
    }
    $entities_parc_equipements_trie = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Equipement')->myFindEquipementsTries($champs_tri, $ordre_tri);
    return $this->render('LciBoilerBoxBundle:GestionParc:parc_equipements.html.twig', array(
        'entities_equipements' => $entities_parc_equipements_trie
    ));
}


public function changeModulesAction(Request $request) {
	// Fonction qui retourne un formulaire de module sur lequel on ne peut modifier que le nom d'un module ou le définir en tant que disponible ou indisponible
	$em = $this->getDoctrine()->getManager();
	if (isset($_POST['id_module'])) {
		$idModule = $_POST['id_module'];
	} elseif (isset($_POST['module']['id'])) {
		$idModule = $_POST['module']['id'];
	} else {
		$request->getSession()->getFlashBag()->add('info', 'Module non renseigné');
		return $this->redirect($this->generateUrl('lci_gestion_modules'));
	}

	$entity_module = $em->getRepository('LciBoilerBoxBundle:Module')->find($idModule);
	// Sauvegarde de la variable indiquant la présence ou non du module
	$old_presence = $entity_module->getPresent();
	$form_module = $this->createForm(ModuleType::class, $entity_module);
    if ($request->getMethod() == 'POST') {
        $form_module->handleRequest($request);
		// Détecter si des erreurs bloquantes sont présentes sur le module lorsqu'il a sa valeur present qui passe de l'état true à false
        if ($form_module->isSubmitted()) {
			if ($form_module->isValid()) {
				$request->getSession()->getFlashBag()->add('info', 'Enregistrement effectué.');
				if ($entity_module->getPresent() != $old_presence) {
					// Si le module passe à l'état non présent
					if ($entity_module->getPresent() == false){
						// Vérification des problèmes bloquants non résolus pour ce module
						$nombre_problemes_bloquants = $em->getRepository('LciBoilerBoxBundle:Module')->myCountProblemesBloquants($entity_module);
						if ($nombre_problemes_bloquants != 0){
							$request->getSession()->getFlashBag()->add('info', " - AVERTISSEMENT: Le module ".$entity_module->getNumero()." (".$entity_module->getNom().") a quitté le parc avec des problèmes bloquants non clos !");
						}
					} else {
						// Si le module passe à l'état présent : Envoi des checks automatiques
						$this->createCheck($entity_module, 'mécaniciens');
						$this->createCheck($entity_module, 'techniciens');
						// Création des tickets / des mails 'MISE A JOUR AUTOMATE' pour les automates de type IBC
						if ($entity_module->getType() == 'IBC') {
							$this->createCheck($entity_module, 'majAutomAndCom');
						}
					}
				}
	            $em->persist($entity_module);
	            $em->flush();
			} else {
				$request->getSession()->getFlashBag()->add('info', 'Formulaire invalide : '.$form_module->getErrors(true));
			}
            return $this->redirect($this->generateUrl('lci_gestion_modules'));
        }
    }
	$nombre_problemes_affectes = $this->get('lci_boilerbox.configuration')->getNombreProblemesNonClos();
    return $this->render('LciBoilerBoxBundle:GestionParc:change_module.html.twig', array(
        'form' => $form_module->createView(),
        'nombre_problemes_affectes' => $nombre_problemes_affectes
    ));

}

private function createCheck($entity_module, $type_check){
	// Par défaut on crée un ticket pb technique
	$create_pb = true;
	$entity_probleme_technique = new ProblemeTechnique();
	$entity_probleme_technique->setDateCorrection(null);
	$entity_probleme_technique->setDateCloture(null);
	$entity_probleme_technique->addModule($entity_module);
	switch($type_check){
		case 'mécaniciens':
			$entity_equipement = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Equipement')->findOneByType('CHECK MÉCANICIEN');
			$entity_intervenant = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByUsername('0_MÉCANICIENS');
			$entity_probleme_technique->setDescription("Effectuer le check mécanicien\nVérifier les problèmes déclarés sur le module ".$entity_module->getNumero().' ('.$entity_module->getNom().')');
			$entity_probleme_technique->setBloquant(true);
			break;
		case 'techniciens':
            $entity_equipement = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Equipement')->findOneByType('CHECK TECHNICIEN');
            $entity_intervenant = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByUsername('0_TECHNICIENS');
            $entity_probleme_technique->setDescription("Effectuer le check technicien\nVérifier les problèmes déclarés sur le module ".$entity_module->getNumero().' ('.$entity_module->getNom().')');
			$entity_probleme_technique->setBloquant(true);
			break;
		case 'majAutomAndCom':
			// Création du problème + envoi du mail au gestionnaire de parc
			$entity_equipement = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Equipement')->findOneByType('MISE A JOUR AUTOMATE ET COMMUNICATION');
			$entity_intervenant = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByUsername($this->container->getParameter('gestionnaire_parc_externe'));
            $entity_probleme_technique->setDescription("Effectuer la mise à jour de l'automate et de la communication pour le module ".$entity_module->getNumero().' ('.$entity_module->getNom().')');
			$entity_probleme_technique->setBloquant(true);
			break;
	}

	$entity_probleme_technique->setEquipement($entity_equipement);
	$entity_probleme_technique->setUser($entity_intervenant);
	if ($create_pb == true) {
		$this->getDoctrine()->getManager()->persist($entity_probleme_technique);
		$this->getDoctrine()->getManager()->flush(); 
	}

	// Gestion de l'envoi de mails
	switch($type_check){
		case 'majAutomAndCom':
			if(isset($entity_intervenant)) {
				$this->sendEmailProblemeTechnique($entity_probleme_technique, 'Retour de module');
			}
		break;
	}
	return 0;
}

private function sendEmailProblemeTechnique($entity_probleme, $titre){
    // Envoi de l'email à l'intervenant
    $service_mail = $this->get('lci_boilerbox.mailing');
    $liste_messages = array();
    $liste_messages[] = $titre;
    $liste_messages[] = '%T'."Référence";
    $liste_messages[] = $entity_probleme->getId();
    $liste_messages[] = '%T'."Description du problème";
    $liste_messages[] = nl2br($entity_probleme->getDescription());
    $liste_messages[] = '%T'."Date de signalement";
    $liste_messages[] = $entity_probleme->getDateSignalement()->format('d/m/Y');
    $liste_messages[] = '%T'."Module(s)";
    foreach ($entity_probleme->getModule() as $key => $module){
        $liste_messages[] = $module->getNumero().' ('.$module->getNom().")<br />";
    }
    $liste_messages[] = '%T'."Equipement";
	if ($entity_probleme->getEquipement() != null) {
    	$liste_messages[] = $entity_probleme->getEquipement()->getType();
	}
    if ($entity_probleme->getCorrige() == true) {
        $liste_messages[] = '%T'."Correction";
        $message_correction = '%C'."Problème corrigé";
        if ($entity_probleme->getDateCorrection() != null) {
            $message_correction .= " le ".$entity_probleme->getDateCorrection()->format('d/m/Y');
        }
        $liste_messages[] = $message_correction;
    }
    if ($entity_probleme->getSolution() != null) {
        $liste_messages[] = '%T'."Solution";
        $liste_messages[] = nl2br($entity_probleme->getSolution());
    }
    $liste_messages[] = "<br /><br /><br />";
    $liste_messages[] = "Cordialement.<br />";
    $liste_messages[] = "Responsable de parc.";
	$liste_messages[] = "<br /><br /><br />";
	if ($entity_probleme->getUser() != null) {
    	$service_mail->sendProblemeTechniqueMail($this->getUser()->getEmail(), $entity_probleme->getUser()->getEmail(), $liste_messages);
	}
	return 0;
}


}

