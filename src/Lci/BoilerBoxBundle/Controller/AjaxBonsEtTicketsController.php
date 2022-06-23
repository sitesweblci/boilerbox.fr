<?php
namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\ResponseHeaderBag;



use Lci\BoilerBoxBundle\Entity\BonsAttachement;
use Lci\BoilerBoxBundle\Entity\SiteBA;

use Lci\BoilerBoxBundle\Form\Type\SiteBAType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementCommentairesType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModificationType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementValidationType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModification1Type;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementModification2Type;


use Lci\BoilerBoxBundle\Entity\Fichier;
use Lci\BoilerBoxBundle\Form\Type\FichierType;

use Lci\BoilerBoxBundle\Entity\Validation;
use Lci\BoilerBoxBundle\Form\Type\ValidationType;

use Lci\BoilerBoxBundle\Entity\ObjRechercheBonsAttachement;
use Lci\BoilerBoxBundle\Form\Type\ObjRechercheBonsAttachementType;

use Lci\BoilerBoxBundle\Entity\Contact;

use Lci\BoilerBoxBundle\Entity\Configuration;

use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Lci\BoilerBoxBundle\Form\Type\EquipementBATicketType;


use Symfony\Component\Form\FormError;

class AjaxBonsEtTicketsController extends Controller
{

    public function ajoutCommentaireAction(Request $request)
    {

        $em                     = $this->getDoctrine()->getManager();
        $id_bon                 = $_POST['id_bon'];
        $nouveau_commentaire    = $_POST['commentaire'];
        $e_user_courant         = $this->get('security.token_storage')->getToken()->getUser();
        $e_bon                  = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);
        $commentaires_existants = $e_bon->getCommentaires();

        $e_bon->setCommentaires($commentaires_existants . "<div class='bons_commentaires_titre'>Par " . $e_user_courant->getLabel() . " le " . date('d/m/Y H:i:s') . "</div><div class='bons_commentaires_text'>" . $nouveau_commentaire . "</div>");

        $em->flush();
        $reponse = array('retour' => 'succes');
        return new Response(json_encode($reponse));
    }


    // Fonction qui effectue la validation ou la suppression d'une ancienne validation d'une catégorie d'un bon
    public function setValidationAction()
    {
        date_default_timezone_set('Europe/Paris');

        $em         = $this->getDoctrine()->getManager();
        $idBon      = $_POST['identifiant'];
        $type       = $_POST['type'];
        $sens       = $_POST['sens'];
        $entity_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
        $e_user_actif = $this->get('security.token_storage')->getToken()->getUser();
        $entity_validation              = null;
        $entity_user_emetteur_du_bon    = null;
        $entities_users_validation      = null;

        // Si le sens est false c'est que la checkbox est décochée : On définit le champs valide à 0 pour signifier que l'entité Validation est une Dé-Validation
        // Ajout du 02/12/2019 : Lors d'une dévalidation on informe l'ensemble des valideurs par mail.
        if ($sens == 'false')
        {
            switch ($type) {
                case 'technique':
                    $entity_validation          = $entity_bon->getValidationTechnique();
                    $entities_users_validation  = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_TECHNIQUE');
                    break;
                case 'sav':
                    $entity_validation          = $entity_bon->getValidationSAV();
                    $entities_users_validation  = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_SAV');
                    break;
                case 'pieces':
                    $entity_validation          = $entity_bon->getValidationPiece();
                    $entities_users_validation  = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_PIECES');

                    // Si c'est une dévalidation de type pièce on informe le gestionnaire_de_pieces (designation du gestionnaire de pièces dans le fichier app/config/parameters.yml)
                    // ICI DEV Recherche du gestionnaire par son role SERVICE_GESTION_PIECES - A faire pour remplacer la lecture en dure dans le fichier parameter
                    $entity_gestionnaire = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByUsername($this->container->getParameter('gestionnaire_pieces'));
                    if ($entity_gestionnaire) {
                        $service_mail = $this->get('lci_boilerbox.mailing');
                        $service_mail->sendMailPieces('annulation', $entity_bon->getSite()->getIntitule(), $entity_bon->getNumeroAffaire(), $entity_bon->getNumeroBA(), $e_user_actif->getLabel(), $entity_gestionnaire->getEmail());
                    }

                    break;
                case 'pieces_faite':
                    $entity_validation = $entity_bon->getValidationPieceFaite();
                    $entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_GESTION_PIECES');
                    break;
                case 'facturation':
                    $entity_validation = $entity_bon->getValidationFacturation();
                    $entities_users_validation = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_FACTURATION');
                    break;
                case 'intervention':
                    $entity_validation          = $entity_bon->getValidationIntervention();
                    $entities_users_validation  = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_TECHNIQUE');
                    break;
                case 'cloture':
                    $entity_validation          = $entity_bon->getValidationCloture();
                    $entities_users_validation  = $em->getRepository('LciBoilerBoxBundle:User')->myfindByRole('ROLE_SERVICE_TECHNIQUE');
					// ICI DEV envoi de mail de dé cloture
                    break;
                default:
                    break;
            }
            if ($entity_validation != null)
            {
                $entity_user_emetteur_du_bon = $entity_validation->getUser();
                $this->devalidation($entity_validation, $e_user_actif);
                // Envoi du mail à l'ensemble du groupe de dévalidation

                $titre_titre_pour_mail = $type;
                if ($type == 'pieces_faite')
                {
                    $titre_titre_pour_mail = "d'offre de pièces";
                } else if ($type == 'pieces')
                {
                    $titre_titre_pour_mail = "de demande de pièces";
                }
                $this->sendMailDevalidation($titre_titre_pour_mail, $entity_bon->getSite()->getIntitule(), $entity_bon->getNumeroAffaire(), $entity_bon->getNumeroBA(), $entities_users_validation, $e_user_actif, $entity_user_emetteur_du_bon);
            }
        } else {
            // Si l'entité existe on la récupère sinon on l'a créée
            // Une Validation sur un bon d'intervention est effectuée

            $entity_validation = null;
            switch ($type) {
                case 'technique':
                    $entity_validation = $entity_bon->getValidationTechnique();
                    break;
                case 'sav':
                    $entity_validation = $entity_bon->getValidationSAV();
                    break;
                case 'pieces':
                    $entity_validation = $entity_bon->getValidationPiece();
                    break;
                case 'pieces_faite':
                    $entity_validation = $entity_bon->getValidationPieceFaite();
                    break;
                case 'facturation':
                    $entity_validation = $entity_bon->getValidationFacturation();
                    break;
                case 'intervention':
                    $entity_validation = $entity_bon->getValidationIntervention();
                    break;
                case 'cloture':
                    $entity_validation = $entity_bon->getValidationCloture();
					// ICI DEV envoi du mail de cloture
                    break;
                default:
                    break;
            }

            if ($entity_validation == null)
            {
                $entity_validation = new Validation();
            }
            $entity_validation->setValide(true)
                ->setDateDeValidation(new \Datetime())
                ->setType($type)
                ->setUser($e_user_actif)
            ;


            switch ($type) {
                case 'technique':
                    if ($entity_bon->getValidationTechnique() != null)
                    {
                        $entity_validation = $entity_bon->getValidationTechnique();

                    }
                    $entity_bon->setValidationTechnique($entity_validation);
                    break;
                case 'sav':
                    $entity_bon->setValidationSAV($entity_validation);
                    break;
                case 'pieces':
                    // Si c'est une validation de type pièce on informe le gestionnaire_de_pieces (designation du gestionnaire de pièces dans le fichier app/config/parameters.yml)
                    // ICI DEV Recherche du gestionnaire par son role SERVICE_GESTION_PIECES - A faire pour remplacer la lecture en dure dans le fichier parameter
                    $entity_bon->setValidationPiece($entity_validation);
                    $entity_gestionnaire = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:User')->findOneByUsername($this->container->getParameter('gestionnaire_pieces'));
                    if ($entity_gestionnaire) {
                        $service_mail = $this->get('lci_boilerbox.mailing');
                        $service_mail->sendMailPieces('demande', $entity_bon->getSite()->getIntitule(), $entity_bon->getNumeroAffaire(), $entity_bon->getNumeroBA(), $e_user_actif->getLabel(), $entity_gestionnaire->getEmail());
                    }
                    break;
                case 'pieces_faite':
                    // Si la demande d'offre est faite. Envoi d'un mail information à l'emetteur de la demande d'offre
                    $entity_bon->setValidationPieceFaite($entity_validation);
                    $label_gestionnaire = $e_user_actif->getLabel();
                    $email_demandeur = $entity_bon->getValidationPiece()->getUser()->getEmail();

                    $service_mail = $this->get('lci_boilerbox.mailing');
                    $service_mail->sendMailPieces('faite', $entity_bon->getSite()->getIntitule(), $entity_bon->getNumeroAffaire(), $entity_bon->getNumeroBA(), $label_gestionnaire, $email_demandeur);
                    break;
                case 'facturation':
                    $entity_bon->setValidationFacturation($entity_validation);
                    break;
                case 'intervention':
                    $entity_bon->setValidationIntervention($entity_validation);
                    break;
                case 'cloture':
                    $entity_bon->setValidationCloture($entity_validation);
                    break;
                default:
                    break;
            }
            $em->persist($entity_bon);
        }
        $em->flush();
        return new Response();
    }


    // Recherches des informations du site selectionné dans le formulaire de création d'un BA
    public function getSiteBAEntityAction()
    {
        $em                 = $this->getDoctrine()->getManager();

        if (isset($_POST['id_site_ba'])) {
            $id_site_ba = $_POST['id_site_ba'];
        } else {
            $id_site_ba = 1;
        }
        $e_siteba = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($id_site_ba);

        // On retourne le fichier serialize
        $serializer = $this->get('serializer');
        $jsonContent = $serializer->serialize(
            $e_siteba,
            'json', array('groups' => array('groupContact'))
        );
        return new Response($jsonContent);
    }



    public function getGoogleMapAction()
    {
        $url;
        $em = $this->getDoctrine()->getManager();

        $id_site_ba = $_POST['id_site_ba'];
        $e_siteba = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($id_site_ba);

        // transformeUrlReverse
        $patternLatLng = '$^https?://www.google.com/maps/embed/v1/view\?key=APIKEY&center=(.+?),(.+?)&zoom.*$';
        $patternPlace = '$^https://www.google.com/maps/embed/v1/place\?key=APIKEY&q=(.+?)&zoom=.*$';
        if (preg_match($patternLatLng, $e_siteba->getLienGoogle(), $matches))
        {
            $url = 'latLng(' . $matches[1] . ',' . $matches[2] . ')';
        } else if (preg_match($patternPlace, $e_siteba->getLienGoogle(), $matches))
        {
            $url = 'https://www.google.com/maps/place/' . $matches[1] . '/';
        } else {
            $url = $e_siteba->getLienGoogle();
        }

        // putApiKey
        $apiKey = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();
        $pattern = '/APIKEY/';
        $url = preg_replace($pattern, $apiKey, $url);

        // putZoomApi
        $zoomApi = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur();
        $pattern = '/ZOOMAPI/';
        $url = preg_replace($pattern, $zoomApi, $url);

        return new Response($url);
    }




    // Fonction qui enregistre les informations de dévalidation
    private function devalidation($entity_validation, $entity_user)
    {
        date_default_timezone_set('Europe/Paris');

        $entity_validation->setValide(false);
        $entity_validation->setDateDeValidation(new \Datetime());
        $entity_validation->setUser($entity_user);
        return $entity_validation;
	}


    private function sendMailDevalidation($type, $nom_site, $code_affaire, $numero_ba, $entities_users, $entity_devalideur, $entity_user_emetteur)
    {

        $sujet = "Annulation $type pour l'affaire $code_affaire ( $nom_site ) ";
        $titre = "Annulation $type pour l'affaire $code_affaire";

        $tab_destinataires = [];
        foreach ($entities_users as $entity_user)
        {
            // On ne met pas le devalideur dans la liste des utilisateurs qui doivent recevoir le mail de dévalidation
/* Mis en commentaire pour envoyer aussi le mail à l'annuleur pour qu'il ai la confirmation de prise en compte de sa demande
            if ($entity_user->getId() != $entity_devalideur->getId())
            {
                $tab_destinataires[] = $entity_user->getEmail();
            }
*/
        }
        // Ajout de l'emetteur de la demande de validation au mail de dévalidation
        $tab_destinataires[] = $entity_user_emetteur->getEmail();

        $service_mail = $this->get('lci_boilerbox.mailing');

        $tab_contenu = array();
        $tab_contenu[] = '%P';
        $tab_contenu[] = '%PUne annulation ';
        $tab_contenu[] = '%G' . $type . ' ';
        $tab_contenu[] = " a été effectuée sur le bon d'attachement <b>".$numero_ba."</b> ( $nom_site - $code_affaire )<br />";
        $tab_contenu[] = '%P';
        $tab_contenu[] = "<span style='font-size:12px'>( Annulation effectuée le ".date("d/m/Y")." par ".$entity_devalideur->getUsername()." )</span>";
        $tab_contenu[] = '%P';
        $tab_contenu[] = "<br /><br /><br />";
        $tab_contenu[] = "Cordialement.<br /><br />";
        $tab_contenu[] = "Assistance BoilerBox.<br />";
        $tab_contenu[] = "%MAssistance_IBC@lci-group.fr";
        $tab_contenu[] = "<br /><br /><br />";

        $service_mail->sendMailMultiDestinataires($sujet, $titre, $tab_contenu, $tab_destinataires);
        return 0;
    }

    public function choixServiceAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($_POST['service'] == 'Tous')
        {
            return new Response();
        } else {
            $role_service   = strtoupper('role_service_'.$_POST['service']);
        }

        $ents_user                  = $em->getRepository('LciBoilerBoxBundle:User')->findAll();
        $tab_des_membres_du_service = array();

        foreach($ents_user as $e_user)
        {
            if ($role_service != null)
            {
                if ($e_user->hasRole($role_service))
                {
                    array_push($tab_des_membres_du_service, $e_user->getId());
                }
            }
        }
        return new Response(json_encode($tab_des_membres_du_service));
    }


    public function creerSiteBAAction(Request $request)
    {
        $em                 = $this->getDoctrine()->getManager();

        // Lecture de l'option de configuration [upload_max_filesize] pour l'indiquer dans la page html
        $max_upload_size    = ini_get('upload_max_filesize');

        // Clé google pour pouvoir utiliser les API de google
        $apiKey             = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();

        $e_siteBA           = new SiteBA();
        $f_siteBA           = $this->createForm(SiteBAType::class, $e_siteBA);

        return $this->render('LciBoilerBoxBundle:Bons:creer_siteBA.html.twig', array(
            'max_upload_size'   => $max_upload_size,
            'apiKey'            => $apiKey,
            'form_siteBA'       => $f_siteBA->createView(),
            'id'                => $e_siteBA->getId()
        ));
    }


    // Fonction qui vérifie qu'une url existe vers le chemin des photos pour un bon
    // Fonction executée avec l'action de creation et téléchargement du fichier bat
    public function hasUrlAction($id_bon = null)
    {
        $has_url    = false;
        // Si pas d'id de bon on retourne l'info d'erreur
        if ($id_bon == null)
        {
            $reponse = array("hasUrl" => $has_url);
            return new Response(json_encode($reponse));
        }

        $e_bon      = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);

        if ($e_bon->getCheminDossierPhotos() != '')
        {
            $has_url = true;
        }

        $reponse = array("hasUrl" => $has_url);

        return new Response(json_encode($reponse));
    }

}
