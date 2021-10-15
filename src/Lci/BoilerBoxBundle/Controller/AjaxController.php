<?php

namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\Session;

use Lci\BoilerBoxBundle\Entity\CommentairesSite;
use Lci\BoilerBoxBundle\Entity\FichierV2;
use Symfony\Component\HttpFoundation\Request;

class AjaxController extends Controller
{

    private function getUserConnected()
    {
        return $this->get('security.token_storage')->getToken()->getUser();
    }

    // AJAX : Récupération des informations de connexion
    public function getUserLogAction()
    {
        /* Permet de changer les mots de passe Admin / Technicien et Client
        $pass = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Zoneadmin')->findOneBy(array('login' => 'Admin'));
        $pass->setPassword('@dm|n5667');
        $this->container->get('doctrine')->getManager()->flush();
        */
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $password = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Zoneadmin')->findOneBy(array('login' => 'Admin'))->getPassword();
            echo "Admin;$password";
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN_LTS')) {
            $password = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Zoneadmin')->findOneBy(array('login' => 'AdminLTS'))->getPassword();
            echo "AdminLTS;$password";
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_TECHNICIEN_LTS')) {
            $password = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Zoneadmin')->findOneBy(array('login' => 'TechnicienLTS'))->getPassword();
            echo "TechnicienLTS;$password";
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_TECHNICIEN')) {
            $password = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Zoneadmin')->findOneBy(array('login' => 'Technicien'))->getPassword();
            echo "Technicien;$password";
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $password = $this->container->get('doctrine')->getManager()->getRepository('LciBoilerBoxBundle:Zoneadmin')->findOneBy(array('login' => 'Client'))->getPassword();
            echo "Client;$password";
        }
        return new Response();
    }

    // Fonction qui retourne la liste des sites auxquel un utilisateur a accés
    // L'identifiant de l'utilisateur est passé en paramètre de l'appel AJAX
    public function getUserSitesAction()
    {
        $id_user = $_POST['idUser'];
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('LciBoilerBoxBundle:User')->find($id_user);
        $user_sites = $user->getSite();
        $tab_entity_sites = array();
        foreach ($user_sites as $site) {
            $tab_entity_sites[$site->getId()] = '(' . $site->getAffaire() . ') ' . $site->getIntitule();
        }
        return new Response(json_encode($tab_entity_sites));
    }

    // Fonction qui retourne la liste des sites auxquel un utilisateur a accés
    // L'identifiant de l'utilisateur est passé en paramètre de l'appel AJAX
    public function getUserListeSitesAction()
    {
        $ent_user = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        //$user = $em->getRepository('LciBoilerBoxBundle:User')->find($id_user);
        $champs_de_tri = $_SESSION['champs_de_tri'];
        $ordre_de_tri = $_SESSION['ordre_de_tri'];
        $ents_sites = $em->getRepository('LciBoilerBoxBundle:site')->myFindByUser($ent_user, $champs_de_tri, $ordre_de_tri);

        // On retourne le fichier serialize
        $serializer = $this->get('serializer');
        $jsonContent = $serializer->serialize(
            $ents_sites,
            'json', array('groups' => array('groupSite'))
        );
        return new Response($jsonContent);
    }





    // Fonction qui retourne la liste des utilisateurs ayant accés à un site
    // L'identifiant du site est passé en paramètre de l'appel AJAX
    public function getSiteUsersAction()
    {
        $id_site = $_POST['idSite'];
        $em = $this->getDoctrine()->getManager();
        $site = $em->getRepository('LciBoilerBoxBundle:Site')->find($id_site);
        $site_users = $em->getRepository('LciBoilerBoxBundle:User')->findBySite($site);
        $tab_entity_users = array();
        foreach ($site_users as $user) {
            $tab_entity_users[$user->getId()] = $user->getLabel();
        }
        echo json_encode($tab_entity_users);
        return new Response();
    }


    //Fonction qui retourne une entité selon son id
    public function getCommentairesSiteAction()
    {
        $id_site = $_POST['idSite'];
        $ent_site = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Site')->find($id_site);
        /*
        foreach($ent_site->getCommentaires() as $comm) {
            echo json_encode($comm);
        }
        */
        foreach ($ent_site->getCommentaires() as $ent_commentaire) {
            $serializer = $this->get('serializer');
            $jsonContent = $serializer->serialize(
                $ent_commentaire,
                'json', array('groups' => array('groupCommentaire'))
            );
            echo $jsonContent;
        }
        return new Response();
    }


    public function sendEmailRappelProblemeTechniqueAction()
    {
        return $this->redirect($this->generateUrl('lci_mail_probleme_rappel'));
    }


    // Fonction qui change la variable de session changeFrom
    public function changeFromVarAction(Session $session)
    {
        if (isset($_GET['fromVar'])) {
            $fromVar = $_GET['fromVar'];
            $session->set('fromVar', $fromVar);
        }
        return new Response();
    }


    // Fonction qui change la variable de session provenance
    public function changeVarProvenanceAction(Session $session)
    {
        if (isset($_GET['provenance'])) {
            $fromVar = $_GET['provenance'];
            $session->set('provenance', $fromVar);
        }
        return new Response();
    }


    // Fonction qui retourne la valeur d'une variable de session
    public function getVariableDeSessionAction(Session $session)
    {
        if (isset($_GET['variable'])) {
            $variable = $_GET['variable'];
            echo json_encode($session->get($variable));
        }
        return new Response();
    }


    // Utilitaire NETCAT : Affiche la liste des sites avec un indicateur indiquant la disponibilité du site
    public function refreshASiteStatutAction()
    {
        $idSite = $_POST['idSite'];

        // **** Recherche de la liste des sites enregistrés en base de donnée
        $entity_site = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Site')->find($idSite);

        $service_utilitaire = $this->get('lci_boilerbox.utilitaires');
        $tab_details = $service_utilitaire->pingDeSite($entity_site);

        return new JsonResponse($tab_details);
    }


    // Fonction qui prend en argument une url de type http://c671.boiler-box.fr/ (ou http://c714.boiler-box.fr:81/) et retourne l'url c671.boiler-box.fr (ou c714.boiler-box.fr)
    private function recuperationSiteUrl($url)
    {
        $urlSite = $url;
        $pattern_live = '/^(http:\/\/.+?\/)/';
        if (preg_match($pattern_live, $url, $tabUrlLive)) {
            $urlSite = $tabUrlLive[1];
        }
        $tab_param_url = array();
        $pattern_url = '/^http:\/\/(.+?):?(\d*?)\/$/';
        if (preg_match($pattern_url, $urlSite, $tab_url)) {
            if ($tab_url[2] == null) {
                $tab_url[2] = 80;
            }
            $tab_param_url['url'] = $tab_url[1];
            $tab_param_url['port'] = $tab_url[2];
        } else {
            $tab_param_url['url'] = $url;
            $tab_param_url['port'] = 80;
        }
        return ($tab_param_url);
    }

    private function interpretation_netcat($entitySite, $retour_commande_netcat, $dateAccess)
    {
        if ($retour_commande_netcat == 0) {
            $entitySite->getSiteConnexion()->setDisponibilite(0);
            $entitySite->setDateAccessSucceded($dateAccess);
        } else {
            $entitySite->getSiteConnexion()->setDisponibilite(2);
        }
        $this->getDoctrine()->getManager()->flush();
        return (0);
    }


    // Pour désactiver l'authentification double facteur, on supprime le paramètre totp et l'url du QrCode de l'utilisateur.
    public function desactivationAuthAction(Session $session)
    {
        $user = $this->get('security.token_storage')->getToken()->getUser();
        $user->setTotpKey('');
        $user->setQrCode('');
        $this->getDoctrine()->getManager()->flush();
        $session->set('totp_auth', false);
        return new Response();
    }


    /* Fonction qui ajoute, modifie ou supprime un commentaire de site */
    public function setCommentairesSiteAction()
    {
        $action = 'creer';
        $ent_user = $this->get('security.token_storage')->getToken()->getUser();
        if (isset($_POST['action'])) {
            $action = $_POST['action'];
        }
        switch ($action) {
            case 'creer':
                // Lors de la création on retourne l'identifiant du nouveau commentaire pour pouvoir le supprimer ou le modifier au besoin
                $commentaire = $_POST['commentaire'];
                $id_site = $_POST['idSite'];
                $ent_site = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Site')->find($id_site);
                $ent_commentaire = new CommentairesSite();
                $ent_commentaire->setUser($ent_user);
                $ent_commentaire->setDtCreation(new \Datetime(date('Y-m-d H:i:s')));
                $ent_commentaire->setCommentaire($commentaire);
                $ent_commentaire->setSite($ent_site);
                $ent_commentaire->setModePrive($ent_user->getModePrive());
                $this->getDoctrine()->getManager()->persist($ent_commentaire);
                $this->getDoctrine()->getManager()->flush();
                echo $ent_commentaire->getId();
                break;
            case 'modifier':
                $commentaire = $_POST['commentaire'];
                $id_commentaire = $_POST['idCommentaire'];
                $ent_commentaire = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:CommentairesSite')->find($id_commentaire);
                $ent_commentaire->setUser($this->get('security.token_storage')->getToken()->getUser());
                $ent_commentaire->setCommentaire($commentaire);
                $this->getDoctrine()->getManager()->flush();
                break;
            case 'supprimer':
                $id_commentaire = $_POST['idCommentaire'];
                $ent_commentaire = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:CommentairesSite')->find($id_commentaire);
                $this->getDoctrine()->getManager()->remove($ent_commentaire);
                $this->getDoctrine()->getManager()->flush();
                break;
        }
        return new Response();
    }

    // Ajout d'un fichier au site selectionné dans la popup de la page accueil
    public function setSiteFichierAction(Request $request)
    {
        $fichier = $request->files->get('fichierDuSite');
        $ent_user = $this->get('security.token_storage')->getToken()->getUser();
        if (!is_null($fichier)) {
            $ent_site = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Site')->find($_POST['fichierIdSite']);
            $ent_fichier = new FichierV2();
            $ent_fichier->setFile($fichier);
            $ent_fichier->setUSer($ent_user);
            // La liaison se fait depuis le site car il execute lui meme  la méthode de liaison du fichier vers le site
            $ent_site->addFichier($ent_fichier);
            $information = $ent_user->getLabel();
            $ent_fichier->setInformations($information);
            $ent_fichier->setModePrive($ent_user->getModePrive());

            // Cette action déclanche la création des paramètres url, filename, path en PREpersist sur l'entité fichier
            // Et le déplacement du fichier en POST persist
            $this->getDoctrine()->getManager()->persist($ent_fichier);

            $this->getDoctrine()->getManager()->flush();
            // On retourne le fichier serialize
            $serializer = $this->get('serializer');
            $jsonContent = $serializer->serialize(
                $ent_fichier,
                'json', array('groups' => array('groupSite'))
            );
            echo $jsonContent;
        }
        return new Response();
        //return new JsonResponse($status);
    }

    //Fonction qui supprime un fichier de site
    public function removeFichierAction()
    {
        $em = $this->getDoctrine()->getManager();
        $ent_fichier = $em->getRepository('LciBoilerBoxBundle:FichierV2')->find($_POST['id_fichier']);
        $em->remove($ent_fichier);
        $em->flush();
    }

    // Fonction qui retourne une entité Site selon son id : Cette entité est serialisée pour lecture en javascript
    public function getSiteAction()
    {
        $id_site = $_POST['idSite'];
        $ent_site = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Site')->find($id_site);
        $serializer = $this->get('serializer');
        $jsonContent = $serializer->serialize(
            $ent_site,
            'json', array('groups' => array('groupSite'))
        );
        echo $jsonContent;
        return new Response();
    }

    // Fonction  qui inverse le mode (prive / publique) d'un utilisateur
    public function switchModeAction()
    {
        $ent_user = $this->getUserConnected();
        $ent_user->setModePrive(!$ent_user->getModePrive());
        $this->getDoctrine()->getManager()->flush();
        return new Response();
    }
}
