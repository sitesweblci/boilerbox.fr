<?php
# src/Lci/BoilerBoxBundle/Controller/AdminController.php
namespace Lci\BoilerBoxBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\FOSUserEvents;

use Lci\BoilerBoxBundle\Form\Type\SiteType;
use Lci\BoilerBoxBundle\Form\Type\ModificationUserType;
use Lci\BoilerBoxBundle\Form\Type\RoleType;
use Lci\BoilerBoxBundle\Entity\Site;
use Lci\BoilerBoxBundle\Entity\SiteConnexion;
use Lci\BoilerBoxBundle\Entity\Configuration;
use Lci\BoilerBoxBundle\Entity\User;
use Lci\BoilerBoxBundle\Entity\Role;
use Lci\BoilerBoxBundle\Form\Type\SiteConfigurationPourSuppressionType;
use Lci\BoilerBoxBundle\Form\Type\ConfigurationType;




class AdminController extends Controller
{

    private function boolval($var)
    {
        switch ($var) {
            case 'true':
                return 1;
            case 'false':
                return 0;
        }
    }


    public function accueilSiteRegistrationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $choix_action = $_POST['choixAction'];
            switch ($choix_action) {
                case 'deleteSite':
                    $entity_site = $em->getRepository('LciBoilerBoxBundle:Site')->find($_POST['choix_site']);
                    $request->getSession()->getFlashBag()->add('info', 'Site ' . $entity_site->getIntitule() . ' ( ' . $entity_site->getAffaire() . ' ) supprimé');
                    $em->remove($entity_site);
                    $em->flush();
                    break;
                case 'createSite':
                    return $this->redirect($this->generateUrl('lci_register_site'));
                    break;
				case 'createSiteParametre':
					return $this->redirect($this->generateUrl('lci_register_parametre_site'));
                    break;
            }
        }
        $liste_sites = $em->getRepository('LciBoilerBoxBundle:Site')->findBy(array(), array('affaire' => 'ASC'));
        return $this->render('LciBoilerBoxBundle:Registration:accueilSiteRegistration.html.twig', array(
            'liste_sites' => $liste_sites
        ));
    }


    public function modificationSiteAction($idSite = null, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($idSite == null) {
            $idSite = $_POST['idSite'];
        }

        $ent_site = $em->getRepository('LciBoilerBoxBundle:Site')->find($idSite);

		// Création du formulaire depuis l'entité site
        $form = $this->createForm(SiteType::class, $ent_site);

		// Formulaire pour la suppression des paramètres supplémentaires
		$form_parametres_supplementaire = $this->createForm(SiteConfigurationPourSuppressionType::class, null, array('site_id' => $ent_site->getId()));


		// Hydratation de l'entité site avec les données du formulaires renvoyées dans la requête
        $form->handleRequest($request);

        if ($form->isSubmitted()) 
		{
            if ($form->isValid()) 
			{
				// Si un parametre de configuration n'a pas encore d'id c'est que c'est un ajout de paramètre : On défini son lien avec le site
				foreach ($ent_site->getSiteConfigurations() as $ent_configuration) 
				{
					if ($ent_configuration->getId() == null) 
					{
                	    $ent_configuration->setSite($ent_site);
                	} 
				}
                $em->flush();
                $request->getSession()->getFlashBag()->add('info', 'Site ' . $ent_site->getIntitule() . ' ( ' . $ent_site->getAffaire() . ' ) modifié');
                return $this->redirectToRoute('lci_boilerbox_accesSite', ['id_site' => $idSite]);
            }
        } else {
			$form_parametres_supplementaire->handleRequest($request);
			if ($form_parametres_supplementaire->isSubmitted())
			{
				// Récupération de l'identifiant de l'entité siteConfiguration à supprimer
				$ent_siteconfiguration = $em->getRepository('LciBoilerBoxBundle:Siteconfiguration')->find($request->request->get('site_configuration_pour_suppression')['siteConfiguration']);
				// If qui permet la gestion du rafraichissement de la page apres suppression du dernier parametre de configuration supplémentaire
				if ($ent_siteconfiguration != null)
				{
					$em->remove($ent_siteconfiguration);	
					$em->flush();
					// On re créée le formulaire du site car les site dispose d'un parametre de configuration en moins
					$form = $this->createForm(SiteType::class, $ent_site);
				}
			}
		}

        return $this->render('LciBoilerBoxBundle:Registration:changeSite.html.twig', array(
            'form' 					=> $form->createView(),
			'form_pour_suppression'	=> $form_parametres_supplementaire->createView(),
            'entity_site' 			=> $ent_site
        ));
    }


    public function siteRegistrationAction(Request $request)
    {
        $ent_site = new Site();
        $ent_site_connexion = new SiteConnexion();
        $ent_site->setSiteConnexion($ent_site_connexion);
        $ent_site_connexion->setDisponibilite(2);
        $form_site = $this->createForm(SiteType::class, $ent_site);
        if ($request->getMethod() == 'POST') {
            // On hydrate l'objet
            $form_site->handleRequest($request);
            // On test la validité des données
            if ($form_site->isValid()) {
                $em = $this->getDoctrine()->getManager();
                // On crée les liens entre le site et ses paramètres de configuration personnels
                if ($ent_site->getConfigurations()) {
                    foreach ($ent_site->getConfigurations() as $ent_configuration) {
                        $ent_configuration->setSite($ent_site);
                    }
                }
                if ($ent_site->getSiteConnexion()->getConfigurations()) {
                    foreach ($ent_site->getSiteConnexion()->getConfigurations() as $ent_configuration) {
                        $ent_configuration->setSiteConnexion($ent_site->getSiteConnexion());
                    }
                }
                if ($ent_site->getSiteAutres()->getConfigurations()) {
                    foreach ($ent_site->getSiteAutres()->getConfigurations() as $ent_configuration) {
                        $ent_configuration->setSiteAutres($ent_site->getSiteAutres());
                    }
                }


                // Avec l'effet cascade, les entités configurations sont également persistées
                $em->persist($ent_site);
                $em->flush();
                $request->getSession()->getFlashBag()->add('info', 'Site ' . $ent_site->getIntitule() . ' ( ' . $ent_site->getAffaire() . ' ) enregistré');
                return $this->redirectToRoute('lci_boilerbox_accesSite');
            }
        }
        return $this->render('LciBoilerBoxBundle:Registration:newSite.html.twig', array(
            'form' => $form_site->createView()
        ));
    }



	public function siteParametreRegistrationAction(Request $request)
	{
		$ent_configuration = new Configuration();
		$form = $this->createForm(ConfigurationType::class, $ent_configuration);
		$form->handleRequest($request);
		if ($form->isSubmitted())
		{
			if ($form->isValid())
			{
				$em = $this->getDoctrine()->getManager();
				$em->persist($ent_configuration);
				$em->flush();
				$request->getSession()->getFlashBag()->add('info', 'Nouveau paramétre enregistré : '.$ent_configuration->getParametre());
			}
		}
		return  $this->render('LciBoilerBoxBundle:Registration:newSiteParametre.html.twig', array(
            'form' => $form->createView()
        ));
	}


    public function accueilUserRegistrationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $choix_action = $_POST['choixAction'];
            switch ($choix_action) {
                case 'deleteUser':
					// On receptionne en variable $_POST['choix_utilisateur'] : /lci/user/register/update/user/131  -> Il nous faut récupérer l'id de l'url
					// Découpage sur les / pour ne récupérer que l'id
					$url_user_a_supprimer = $_POST['choix_utilisateur'];
					$tab_url = preg_split("/\//", $_POST['choix_utilisateur']);
					$id_user_a_supprimer = array_pop($tab_url);
                    $user = $em->getRepository('LciBoilerBoxBundle:User')->find($id_user_a_supprimer);
                    $em->remove($user);
                    $em->flush();
                    break;
                case 'updateUser':
                    if (isset($_POST['newPassword'])) {
                        //  Si le mot de passe est à réinitialiser
                        $user = $em->getRepository('LciBoilerBoxBundle:User')->find($_POST['idUtilisateur']);
                        $password = trim($_POST['motDePasse']);
                        $salt = $user->getSalt();
                        $password = $this->get('security.encoder_factory')->getEncoder($user)->encodePassword($password, $salt);
                        $user->setPassword($password);
                        $em->flush();
                    } else {
                        // Si une demande de modification d'utilisateur est demandée
                        $user = $em->getRepository('LciBoilerBoxBundle:User')->find($_POST['choix_utilisateur']);
                        $form_user_update = $this->createForm(ModificationUserType::class, $user);
                        return $this->render('LciBoilerBoxBundle:Registration:changeUserOwnRegistration.html.twig', array(
                            'user' => $user,
                            'form' => $form_user_update->createView()
                        ));
                    }
                    break;
                case 'createUser':
                    return $this->redirect($this->generateUrl('fos_user_registration_register'));
            }
        }
        $liste_users = $em->getRepository('LciBoilerBoxBundle:User')->findBy(array(), array('label' => 'ASC'));
        return $this->render('LciBoilerBoxBundle:Registration:accueilUserRegistration.html.twig', array(
            'liste_users' => $liste_users
        ));
    }


    /* Modification d'un utilisateur
    public function userUpdateAction($idUtilisateur, Request $request) {
        // Comme la fonction peut être appelée en GET, on vérifie que l'utilisateur qui appel est soit l'Admin soit l'utilisateur qui modifie sont propre compte
        $em = $this->getDoctrine()->getManager();
        if (($this->get('security.token_storage')->getToken()->getUser()->getId() == $idUtilisateur) || ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')))
        {
            $user = $em->getRepository('LciBoilerBoxBundle:User')->find($idUtilisateur);
            $form_user_update = $this->createForm(ModificationUserType::class, $user);
            $form_user_update->handleRequest($request);
            if ($form_user_update->isSubmitted() && $form_user_update->isValid()) {
                $em->flush();
                $request->getSession()->getFlashBag()->add('info', 'Utilisateur '.$user->getLabel().' modifié');
                return $this->redirectToRoute('lci_accueil_register_user');
            }
            return $this->render('LciBoilerBoxBundle:Registration:changeUserOwnRegistration.html.twig',array(
                'user' => $user,
                'form' => $form_user_update->createView()
            ));
        } else {
             throw $this->createNotFoundException("La page demandée n'existe pas");
        }
    }
    */


    /**
     *
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function registerRoleAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity_role = new Role();
        $form = $this->createForm(RoleType::class, $entity_role);
        $form->handleRequest($request);
        // Par défaut on affiche les utilisateurs ayant le role user
        $role = 'ROLE_USER';

        if ($request->getMethod() == 'POST') {
            if ($form->isValid()) {
                // Enregistrement du nouveau ROLE
                $em->persist($entity_role);
                $em->flush();
            }
            // Si on récupére un rôle on recherche la liste des utilisateurs appartenant au groupe définie par le role
            if (isset($_POST['role'])) {
                $role = $_POST['role'];
            }
        }

        $entities_users_hasrole = $em->getRepository('LciBoilerBoxBundle:User')->myFindByRole($role);
        $entities_role = $em->getRepository('LciBoilerBoxBundle:Role')->findAll();

        return $this->render('LciBoilerBoxBundle:Registration:creerRole.html.twig', array(
            'tableau_des_roles' => $this->container->getParameter('security.role_hierarchy.roles'),
            'entities_role' => $entities_role,
            'entities_user' => $entities_users_hasrole,
            'role' => $role,
            'form' => $form->createView()
        ));
    }


// Affectation d'une nouvelle liste de sites autorisés à un utilisateur
    public function linkUserSitesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            // Récupération de l'utilisateur affecté par le changement
            $user = $em->getRepository('LciBoilerBoxBundle:User')->find($_POST['idUtilisateur']);
            // Effacement des anciens sites autorisés à l'utilisateur
            $user_sites = $user->getSite();
            foreach ($user_sites as $site) {
                $user->removeSite($site);
            }
            $em->flush();
            // Affectation des nouveaux sites à l'utilisateur
            foreach ($_POST as $key => $parametre) {
                // Tous les champs passés en paramètre correspondent aux sites à affecter exceptés : listeUsers et checkAllSites qui ne sont pas à prendre en compte
                if (($key != 'idUtilisateur') && ($key != 'checkAllSites')) {
                    $site = $em->getRepository('LciBoilerBoxBundle:Site')->find($key);
                    $user->addSite($site);
                }
            }
            $em->flush();
        }
        //	Récupération de la liste des utilisateurs et de la liste des sites pour présenter la page d'affectation de liens User/Site
        $liste_sites = $em->getRepository('LciBoilerBoxBundle:Site')->findBy(array(), array('affaire' => 'ASC'));
        $liste_users = $em->getRepository('LciBoilerBoxBundle:User')->findBy(array(), array('label' => 'ASC'));
        return $this->render('LciBoilerBoxBundle:Registration:newLink.html.twig', array(
            'liste_sites' => $liste_sites,
            'liste_users' => $liste_users
        ));
    }


// Affectation d'une nouvelle liste d'utilisateurs autorisés sur un site
    public function linkSiteUsersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            // Récupération du site affecté par le changement
            $site = $em->getRepository('LciBoilerBoxBundle:Site')->find($_POST['idSite']);
            // Effacement des anciens utilisateurs autorisés sur le site
            $site_users = $em->getRepository('LciBoilerBoxBundle:User')->findBySite($site);
            foreach ($site_users as $user) {
                $user->removeSite($site);
            }
            $em->flush();
            // Affectation des nouveaux utilisateurs au site
            foreach ($_POST as $key => $parametre) {
                if (($key != 'idSite') && ($key != 'checkAllUsers')) {
                    $user = $em->getRepository('LciBoilerBoxBundle:User')->find($key);
                    $user->addSite($site);
                }
            }
            $em->flush();
        }
        // Récupération de la liste des utilisateurs et de la liste des sites pour présenter la page d'affectation de liens User/Site
        $liste_sites = $em->getRepository('LciBoilerBoxBundle:Site')->findBy(array(), array('affaire' => 'ASC'));
        $liste_users = $em->getRepository('LciBoilerBoxBundle:User')->findBy(array(), array('label' => 'ASC'));
        return $this->render('LciBoilerBoxBundle:Registration:newLink.html.twig', array(
            'liste_sites' => $liste_sites,
            'liste_users' => $liste_users
        ));
    }


    public function accueilAction()
    {
        return $this->render('LciBoilerBoxBundle:Accueil:accueil.html.twig');
    }


    public function afficheLogsAction()
    {
        $tab_fichier = array();
        $mon_fichier = "logs/connexions.log";
        $index = 0;
        if (file_exists($mon_fichier)) {
            // Ouverture du fichier
            $file = fopen($mon_fichier, "r");
            while ($ligne = fgets($file)) {
                $pattern_tentative = "#Tentative#";
                if (preg_match($pattern_tentative, $ligne, $tab_retour)) {
                    $tab_fichier[$index] = array();
                    $pattern = "#^([^A-Z]+) Tentative de connexion avec l'identifiant (.+)$#";
                    if (preg_match($pattern, $ligne, $tab_retour)) {
                        $tab_fichier[$index]['date'] = $tab_retour[1];
                        $tab_fichier[$index]['connexion'] = 'Tentative de connexion';
                        $tab_fichier[$index]['utilisateur'] = ucfirst($tab_retour[2]);
                        $index++;
                    }
                } else {
                    $pattern = "#^([^A-Z]+)([A-Z][^A-Z]+) de (.+)$#";
                    if (preg_match($pattern, $ligne, $tab_retour)) {
                        $tab_fichier[$index]['date'] = $tab_retour[1];
                        $tab_fichier[$index]['connexion'] = $tab_retour[2];
                        $tab_fichier[$index]['utilisateur'] = ucfirst($tab_retour[3]);
                        $index++;
                    }
                }
            }
        }
        return $this->render('LciBoilerBoxBundle:Utils:affiche_logs.html.twig', array(
            'tab_fichier' => $tab_fichier
        ));
    }


    public function supprimeLogsAction()
    {
        $mon_fichier = "logs/connexions.log";
        system("echo '' > $mon_fichier");
        return $this->render('LciBoilerBoxBundle:Utils:affiche_logs.html.twig', array(
            'message' => 'Suppression des logs de connexion effectuée'
        ));


    }
}
