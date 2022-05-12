<?php
#src/Lci/BoilerBoxBundle/Controller
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;


class UtilsController extends Controller
{
    private $delais_netcat;
    private $tab_details;
    private $label;

    public function __construct()
    {
        $this->delais_netcat = 8;
        $this->tab_details = array();
    }

    public function indexAction()
    {
        return $this->render('LciBoilerBoxBundle:Utils:index.html.twig');
    }

    // Fonction qui récupère le choix de l'utilitaire à afficher et appelle la fonction associée à l'utilitaire
    // Fonction créée également dans un Service - ServiceUtil : function analyseAccess() - A modifier aussi en cas de modification de cette fonction
    // L'utilitaire est demandé à la page 	[ src/Lci/BoilerBoxBundle/Resources/views/Utils/index.html.twig ]
    // L'url appelée est 			[ /utilitaire/choix => lci_utils_validChoice ]
    public function validChoiceAction(Session $session)
    {
        // Récupération de la liste des sites autorisés pour l'utilisateur connecté
        $userLog = $session->get('userLog', array());
        $user = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneBy(array('username' => $userLog['login']));
        $this->label = $user->getLabel();
        // Récupération du choix de l'utilitaire demandé par l'utilisateur
        $choixUtilitaire = $_POST['choixUtilitaire'];
        // Appel de la fonction associée à l'utilitaire demandé
        switch ($choixUtilitaire) {
            case 'printAccess':
                $entities_site = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Site')->findAll();
                return $this->render('LciBoilerBoxBundle:Utils:utils_access.html.twig', array(
                    'entities_sites' => $entities_site,
                    'delais_netcat' => $this->delais_netcat,
                    'tableau_detail' => $this->tab_details,
                    'label' => $this->label
                ));

            case 'gestionRoles':
                return $this->redirectToRoute('lci_register_role');
            case 'afficheLogs':
                return $this->redirectToRoute('lci_affiche_logs_connexion');
			case 'gestionParametres':
				return $this->redirectToRoute('lci_register_parametres');

        }
        return new Response();
    }

	public function affichageDisponibiliteSitesAction(Session $session)
	{	
        // Récupération de la liste des sites autorisés pour l'utilisateur connecté
        $userLog = $session->get('userLog', array());
        $user = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneBy(array('username' => $userLog['login']));
        $this->label = $user->getLabel();
        // Appel de la fonction associée à l'utilitaire demandé
		$entities_site = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Site')->findAll();
		return $this->render('LciBoilerBoxBundle:Utils:utils_access.html.twig', array(
                    'entities_sites' => $entities_site,
                    'delais_netcat' => $this->delais_netcat,
                    'tableau_detail' => $this->tab_details,
                    'label' => $this->label
        ));
		return new Response();
	}
}
