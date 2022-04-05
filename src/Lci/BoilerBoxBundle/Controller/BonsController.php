<?php

namespace Lci\BoilerBoxBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

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

use Symfony\Component\Form\FormError;

class BonsController extends Controller
{
    /**
     * Récupérer la véritable adresse IP d'un visiteur
     */
    function get_ip()
    {
        // IP si internet partagé
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } // IP derrière un proxy
        elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } // Sinon : IP normale
        else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public function indexAction(Request $request)
    {
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SAISIE_BA')) {
            return $this->render('LciBoilerBoxBundle:Bons:index.html.twig');
        } else {
            return $this->visualiserAction(null, $request);
        }
    }

    public function saisieAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $max_upload_size = ini_get('upload_max_filesize');

        $apiKey = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();

        $entities_sitesBA = $em->getRepository('LciBoilerBoxBundle:SiteBA')->findAll();

        // Création d'un formulaire de bon d'attachement +  Récupération de l'utilisateur courant pour définir l'initiateur d'un nouveau bon
        $ent_bons_attachement = new BonsAttachement();
        $ent_user_courant = $this->get('security.token_storage')->getToken()->getUser();
        $ent_bons_attachement->setUserInitiateur($ent_user_courant);
        $formulaire = $this->createForm(BonsAttachementType::class, $ent_bons_attachement);

        $entity_siteBA = new SiteBA();
        $entity_siteBA_update = null;
        $id_last_site = null;
        $formulaire_site = $this->createForm(SiteBAType::class, $entity_siteBA, array(
            'action' => $this->generateUrl('lci_bons_saisie'),
            'method' => 'POST'
        ));

        // Si un formulaire : création de bon ou création / modification de site, de bon a été soumis (retour de type POST)
        if ($request->getMethod() == 'POST') {
            // Si le formulaire de création de bon a été soumis (retour de type POST)
            if ($formulaire->handleRequest($request)->isValid()) {
                // On persist l'entité "Bon d'attachement" et par cascade l'entité" "Fichier"
                // On enregistre le tout en base
                try {
                    // Sauvegarde du site
                    $em->persist($ent_bons_attachement);
                    $em->flush();
                    // Envoi d'un mail à l'intervenant
                    $service_mailling = $this->get('lci_boilerbox.mailing');
                    $emetteur = $ent_bons_attachement->getUserInitiateur()->getEmail();
                    $destinataire = $ent_bons_attachement->getUser()->getEmail();
                    $sujet = "Affectation d'un nouveau bon d'attachement";
                    $tab_message = array();
                    $tab_message['titre'] = "Une nouvelle intervention vous est affectée";
                    $tab_message['site'] = $ent_bons_attachement->getSite()->getIntitule() . " ( " . $ent_bons_attachement->getNumeroAffaire() . " ) ";
                    $messages_contact = "";
                    if (($ent_bons_attachement->getNomDuContact() != null) || ($ent_bons_attachement->getEmailContactClient() != null)) {
                        if ($ent_bons_attachement->getNomDuContact() != null) {
                            $messages_contact = "Votre contact sur site est : " . $ent_bons_attachement->getNomDuContact();
                            if ($ent_bons_attachement->getEmailContactClient() != null) {
                                $messages_contact .= " ( " . $ent_bons_attachement->getEmailContactClient() . " ) ";
                            }
                        } else if ($ent_bons_attachement->getEmailContactClient() != null) {
                            $messages_contact .= "Le mail du contact sur site est : " . $ent_bons_attachement->getEmailContactClient();
                        }
                    } else {
                        $messages_contact = "Aucun contact sur site n'a été renseigné";
                    }
                    $tab_message['contact'] = $messages_contact;
                    $liste_fichiers = "";
                    foreach ($ent_bons_attachement->getFichiersPdf() as $fichier) {
                        $liste_fichiers .= $fichier->getAlt() . ' ';
                    }
                    if ($liste_fichiers != "") {
                        $tab_message['fichiers'] = "Vous pouvez retrouver les fichiers suivants dans le bon d'attachement sur le site boilerbox.fr : $liste_fichiers";
                    } else {
                        $tab_message['fichiers'] = "Aucun fichier n'a été importé pour ce bon";
                    }
                    $service_mailling->sendMail($emetteur, $destinataire, $sujet, $tab_message);
                } catch (\Exception $e) {
                    $pattern_error_files = "#Column 'url' cannot be null#";
                    if (preg_match($pattern_error_files, $e->getMessage())) {
                        $request->getSession()->getFlashBag()->add('info', 'Bon ' . $ent_bons_attachement->getNumeroBA() . " non enregistré. Vous n'avez pas sélectionné de fichier.");
                        return $this->render('LciBoilerBoxBundle:Bons:form_saisie_bons.html.twig', array(
                            'form' => $formulaire->createView(),
                            'form_site' => $formulaire_site->createView(),
                            'max_upload_size' => $max_upload_size,
                            'ents_sitesBA' => $entities_sitesBA,
                            'apiKey' => $apiKey
                        ));
                    } else {
                        throw new \Exception();
                    }
                }
                // On renvoye à la page d'ajout d'un nouveau bon d'attachement avec envoi du message de confirmation d'enregsitrement du bon
                $request->getSession()->getFlashBag()->add('info', 'Bon ' . $ent_bons_attachement->getNumeroBA() . ' enregistré.');

                // Création d'un nouveau formulaire de création de bon d'attachement
                $ent_bons_attachement = new BonsAttachement();
                $ent_bons_attachement->setUserInitiateur($ent_user_courant);
                $formulaire = $this->createForm(BonsAttachementType::class, $ent_bons_attachement);

                return $this->render('LciBoilerBoxBundle:Bons:form_saisie_bons.html.twig', array(
                    'form' => $formulaire->createView(),
                    'form_site' => $formulaire_site->createView(),
                    'max_upload_size' => $max_upload_size,
                    'ents_sitesBA' => $entities_sitesBA,
                    'apiKey' => $apiKey
                ));
            } else {
                // Soit le formulaire de création d'un bon n'est pas valide soit c'est un formulaire de site qui est envoyé
                if ($formulaire->isSubmitted()) {
					// Le formulaire de nouveau bon est donc soumis mais n'est pas valide
					$obj_erreurs = $formulaire->getErrors(true, false);
					$message_erreur = '';
					foreach($obj_erreurs as $obj => $error)
					{
						$message_erreur .= $error.' - ';
					}
                    $request->getSession()->getFlashBag()->add('info', $message_erreur);
                } else {
                    // Le formulaire de nouveau site ou de modification de site est passé
                    // Si un identifiant de site est passé : C'est le formulaire de modification de site qui est passé => Mise à jour de l'entité
                    // Pour cela on enregistre les informations du formulaire dans une nouvelle entité et on met a jour l'entité à modifier
                    if (isset($_POST['id_site_ba'])) {
                        if ($_POST['id_site_ba'] != "") {
                            $entity_siteBA = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($_POST['id_site_ba']);
                            $entity_siteBA_update = new SiteBA();
                            $formulaire_site = $this->createForm(SiteBAType::class, $entity_siteBA_update, array(
                                'action' => $this->generateUrl('lci_bons_saisie'),
                                'method' => 'POST'
                            ));
                            $id_last_site = $_POST['id_site_ba'];
                        }
                    }
                    // J'instancie l'entité $entity_siteBA_update avec les valeurs du formulaire de site reçues
                    // Retourne une erreur (non bloquante car pas de test de validation effectué : data.intitule 	Cette valeur est déjà utilisée.
                    $formulaire_site->handleRequest($request);
                    // !! On ne test pas que le formulaire soit correct avec un form->isValid donc il nous faut intercepter l'exception DBAL en cas d'erreur
                    if ($entity_siteBA_update != null) {
                        $entity_siteBA = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($_POST['id_site_ba']);
                        // Seule la modification du nom du site n'est pas permise
                        $entity_siteBA->setIntitule($entity_siteBA_update->getIntitule());
                        $entity_siteBA->setAdresse($entity_siteBA_update->getAdresse());
                        $entity_siteBA->setLienGoogle($this->transformeUrl($entity_siteBA_update->getLienGoogle()));
                        $entity_siteBA->setInformationsClient($entity_siteBA_update->getInformationsClient());
                        foreach ($entity_siteBA_update->getFichiersJoint() as $ent_fichier) {
                            $entity_siteBA->addFichiersJoint($ent_fichier);
                        }
                        foreach ($entity_siteBA_update->getContacts() as $ent_contact) {
                            $entity_siteBA->addContact($ent_contact);
                        }
                        // Maintenant que j'ai modifié l'entité siteBA je détach l'entité update pour ne pas que doctrine tente de l'enrgistrer en base : Sinon erreur car doublon avec l'entité siteBA
                        $em->detach($entity_siteBA_update);
                    } else {
                        $entity_siteBA->setLienGoogle($this->transformeUrl($entity_siteBA->getLienGoogle()));
                    }
                    // J'effectue moi même la validation des paramètres
                    $retourTest = $this->testEntite($entity_siteBA);
                    if ($retourTest === 0) {
                        // Si tous les paramètres sont corrects je met à jour l'entité en base de données
                        // L'entité est persisté pour gerer le cas ou c'est un nouvelle entité
                        $em->persist($entity_siteBA);
                        try {
                            $em->flush();
                            // Si il y a une demande d'ajout de sauvegarde des contacts
                            if (isset($_POST['site_ba']['contacts'])) {
                                foreach ($_POST['site_ba']['contacts'] as $tab_contact) {
                                    $ent_contact = new Contact();
                                    $ent_contact->setNom($tab_contact['nom']);
                                    $ent_contact->setPrenom($tab_contact['prenom']);
                                    $ent_contact->setTelephone($tab_contact['telephone']);
                                    $ent_contact->setMail($tab_contact['mail']);
                                    $ent_contact->setFonction($tab_contact['fonction']);
                                    $ent_contact->setDateMaj(new \Datetime());
                                    $em->persist($ent_contact);
                                    $entity_siteBA->addContact($ent_contact);
                                }
                                $em->flush();
                            }
                            $request->getSession()->getFlashBag()->add('info', 'Site ' . $entity_siteBA->getIntitule() . ' enregistré');
                        } catch (\Doctrine\DBAL\DBALException $e) {
                            $request->getSession()->getFlashBag()->add('info', "Erreur d'importation");
                            $request->getSession()->getFlashBag()->add('info', $e->getMessage());
                        }
                        // Création d'un nouveau formulaire de création de site
                        $entity_siteBA = new SiteBA();
                        $formulaire_site = $this->createForm(SiteBAType::class, $entity_siteBA, array(
                            'action' => $this->generateUrl('lci_bons_saisie'),
                            'method' => 'POST'
                        ));
                    } else {
                        $request->getSession()->getFlashBag()->add('info', $retourTest);
                    }
                }
                // Par défaut on renvoi sur la page en indiquant le nom du site pour réaffichage de la page précédente
                return $this->render('LciBoilerBoxBundle:Bons:form_saisie_bons.html.twig', array(
                    'form' => $formulaire->createView(),
                    'form_site' => $formulaire_site->createView(),
                    'max_upload_size' => $max_upload_size,
                    'ents_sitesBA' => $entities_sitesBA,
                    'apiKey' => $apiKey,
                    'id_last_site' => $id_last_site
                ));
            }
        } else {
            // Si le formulaire n'a pas encore été affiché
            return $this->render('LciBoilerBoxBundle:Bons:form_saisie_bons.html.twig', array(
                'form' => $formulaire->createView(),
                'form_site' => $formulaire_site->createView(),
                'max_upload_size' => $max_upload_size,
                'ents_sitesBA' => $entities_sitesBA,
                'apiKey' => $apiKey
            ));
        }
    }

    // Fonction qui récupère l'url retournée par google map et extrait la partie recherche
    private function transformeUrl($lienGoogle)
    {

        $pattern = '$^https?://www.google.com/maps/place/(.+?)/$';
        $pattern2 = '$^https?://place(.+)$';
        $patternLatLng = '$^latLng\((.+),(.+)\)$';
        if (preg_match($pattern, $lienGoogle, $matches)) {
            return 'https://www.google.com/maps/embed/v1/place?key=APIKEY&q=' . $matches[1] . '&zoom=ZOOMAPI&maptype=satellite';
        }

        if (preg_match($pattern2, $lienGoogle, $matches)) {
            return 'https://www.google.com/maps/embed/v1/place?key=APIKEY&q=place_id:' . $matches[1] . '&zoom=ZOOMAPI&maptype=satellite';
        }

        if (preg_match($patternLatLng, $lienGoogle, $matches)) {
            //return 'https://www.google.com/maps/embed/v1/view?key=APIKEY&center='.trim($matches[1]).','.trim($matches[2]).'&zoom=ZOOMAPI&maptype=satellite';
            return $lienGoogle;
        }

        return null;
    }

    // Fonction qui permet de modifier une entité de la base pour y ajouter des fichiers
    public function ajoutFichiersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($request->getMethod() == 'GET') {
            $id_bon = $_GET['id_bon'];
        } else {
            $id_bon = $_POST['id_bon'];
        }
        $ent_bons_attachement = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);
        $ent_bons_attachement->setFichiersPdfToNull();
        $form = $this->createForm(BonsAttachementModificationType::class, $ent_bons_attachement);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // Affichage de la liste des nouveau fichiers
                foreach ($ent_bons_attachement->getFichiersPdf() as $fichier) {
                    if ($fichier->getBonAttachement() == null) {
                        $fichier->setBonAttachement($ent_bons_attachement);
                        $em->persist($fichier);
                    }
                }
                $em->flush();
                $request->getSession()->getFlashBag()->add('info', 'Bon ' . $ent_bons_attachement->getNumeroBA() . ' modifié.');
                return $this->render('LciBoilerBoxBundle:Bons:form_ajout_fichiers_bons.html.twig', array(
                    'entity_bon' => $ent_bons_attachement,
                    'form' => $form->createView()
                ));
            } else {
                echo "non valid";
                print_r($form->getErrors(true));
                return new Response();
            }
        }

        return $this->render('LciBoilerBoxBundle:Bons:form_ajout_fichiers_bons.html.twig', array(
            'entity_bon' => $ent_bons_attachement,
            'form' => $form->createView()
        ));
    }

    /* Seul l'initiateur du bon ou l'intervenant peuvent modifier un bon */
    public function modifierUnBonAction($idBon, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $entity_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
        $current_user = $this->get('security.token_storage')->getToken()->getUser();

        if (!($current_user == $entity_bon->getUser()) && !($current_user == $entity_bon->getUserInitiateur()) && !($this->get('security.authorization_checker')->isGranted('ROLE_SAISIE_BA'))) {
            $request->getSession()->getFlashBag()->add('info', "Seul l'initiateur ou l'intervenant peuvent modifier le bon");
            return $this->redirectToRoute('lci_bons_attachements');
        }
        if ($this->get('security.authorization_checker')->isGranted('ROLE_SAISIE_BA')) {
            $form = $this->createForm(BonsAttachementModification1Type::class, $entity_bon);
        } else {
            $form = $this->createForm(BonsAttachementModification2Type::class, $entity_bon);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $em->flush();
                $request->getSession()->getFlashBag()->add('info', 'Bon ' . $entity_bon->getNumeroBA() . ' modifié.');
                $_POST['id_bon'] = $entity_bon->getId();
                // Retour vers la visualisation du bon
                return $this->afficherUnBonAction($request);
            } else {
                $request->getSession()->getFlashBag()->add('info', $form->getErrors(true));
            }
        }
        return $this->render('LciBoilerBoxBundle:Bons:form_modification_bons.html.twig', array(
            'form' => $form->createView(),
            'idBon' => $entity_bon->getId()
        ));
    }

    public function visualiserAction($refresh = null, Request $request)
    {
        $filtre = false;
        if ($refresh !== null) {
            $request->getSession()->remove('objRechercheBon');
        }
        // Si une recherche existe pour le bon affichage de la recherche
        if ($request->getSession()->has('objRechercheBon')) {
            $entity_bon_recherche = $request->getSession()->get('objRechercheBon', null);
            $filtre = true;
            $entities_bons = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->rechercheDesBons($entity_bon_recherche);
        } else {
            // On vérifie quel est le type du compte.
            // Si il a les droits de gestion ba il peut visualiser tous les bons
            // Sinon il ne peut visualiser que ses bons
            if ($this->get('security.authorization_checker')->isGranted('ROLE_GESTION_BA')) {
                // Affichage de tous les bons
                $entities_bons = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->findAll();
            } else {
                // Affichage des bons de l'utilisateur courant
                $ent_user_courant = $this->get('security.token_storage')->getToken()->getUser();
                $entity_bon_recherche = new ObjRechercheBonsAttachement();
                $entity_bon_recherche->setUser($ent_user_courant);
                $entity_bon_recherche->setSaisie(false);
                $entities_bons = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->rechercheDesBons($entity_bon_recherche);
                //$entities_bons = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->myFindByUser($ent_user_courant);

            }
        }
        return $this->render('LciBoilerBoxBundle:Bons:form_visu_bons.html.twig', array(
            'filtre' => $filtre,
            'entities_bon' => $entities_bons
        ));
    }

    // Affichage d'un bon pour la page d'affichage de la liste des fichiers du bon
    // Dans la page du bon on affiche également le forumlaire de validation du bon
    public function afficherUnBonAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $form_message_erreur = "";
        $max_upload_size = ini_get('upload_max_filesize');
        // Si la requete est de type GET : Un rafraichissement de page est demandé. Récupération des anciennes informations
        if ($request->getMethod() == 'POST') {
            if (isset($_POST['id_bon'])) {
                $id_bon = $_POST['id_bon'];
                $request->getSession()->set('idBonAttachement', $id_bon);
            } else {
                // Si un fichier trop volumineux est envoyé :  Information APACHE : PHP.ini
                $form_message_erreur = 'Taille maximum du fichier autorisé : ' . ini_get('upload_max_filesize') . ' - ' . 'Taille maximum tous fichier compris : ' . ini_get('post_max_size');
                $id_bon = $request->getSession()->get('idBonAttachement', null);
            }
        } else {
            $id_bon = $request->getSession()->get('idBonAttachement', null);
        }
        if (!isset($id_bon)) {
            // Si la page est appelée sans passer par boilerbox
            return 'Page non disponible';
        }
        $entity_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);

        $formulaire_validation = $this->createForm(BonsAttachementValidationType::class, $entity_bon);
        $f_ba_commentaires = $this->createForm(BonsAttachementCommentairesType::class, $entity_bon);
        $form = $this->createForm(BonsAttachementModificationType::class, $entity_bon);

        // Gestion de l'ajout de fichiers à un bon
        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    foreach ($entity_bon->getFichiersPdf() as $fichier) {
                        if ($fichier->getBonAttachement() == null) {
                            $fichier->setBonAttachement($entity_bon);
                            $em->persist($fichier);
                            $fichier->setAlt($fichier->getAlt() . " ( " . $this->get('security.token_storage')->getToken()->getUser()->getLabel() . " le " . date('d/m/Y à H:i') . " )");
                            if ($fichier->getUrl() == null) {
                                $entity_bon->removeFichiersPdf($fichier);
                                $em->detach($fichier);
                            }
                        }
                    }
                    $em->flush();
                } else {
                    $form->addError(new FormError($form_message_erreur));
                }
            }
        }
        return $this->render('LciBoilerBoxBundle:Bons:form_visu_un_bon.html.twig', array(
            'entity_bon' => $entity_bon,
            'form_validation' => $formulaire_validation->createView(),
            'form_ajout_fichier' => $form->createView(),
            'form_ajout_commentaires' => $f_ba_commentaires->createView(),
            'max_upload_size' => $max_upload_size
        ));
    }

    public function ajoutCommentairesAction($idBon, Request $request)
    {
        $ent_user_courant = $this->get('security.token_storage')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $e_bon = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
        $commentaires = $e_bon->getCommentaires();
        $form = $this->createForm(BonsAttachementCommentairesType::class, $e_bon);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $nouveaux_commentaires = ucfirst($e_bon->getCommentaires());
                $e_bon->setCommentaires($commentaires . "<div class='bons_commentaires_titre'>Par " . $ent_user_courant->getLabel() . " le " . date('d/m/Y H:i:s') . "</div><div class='bons_commentaires_text'>" . $nouveaux_commentaires . "</div>");
                $em->flush();
            } else {
                echo $form->getErrors();
                throw new \Exception();
            }
        }
        return $this->redirectToRoute('lci_bons_afficher_unbon');
    }

    public function rechercherAction(Request $request)
    {
        // On envoi un formulaire de recherche pour pouvoir affiner la recherche des bons
        // Seulement pour le gestion ba : On autorise la recherche pour un utilisateur en particulier SINON recherche des bons de l'utilisateur courant.
        $session = $request->getSession();

        // Appel du formulaire de recherche : Necessite un objet BonsAttachement
        if ($session->has('objRechercheBon')) {
            $entity_bon_recherche = $session->get('objRechercheBon');
        } else {
            $entity_bon_recherche = new ObjRechercheBonsAttachement();
            //Valeurs par défaut du formulaire
            // Lors d'une nouvelle recherche, par défaut, on indique l'utilisateur courant comme Intervenant des bons à rechercher
            $entity_bon_recherche->setUser($this->get('security.token_storage')->getToken()->getUser());
            //$entity_bon_recherche->setDateMaxIntervention(date('Y-m-d'));
        }
        /*
            Utilisé pour résoudre l'erreur :
            Entities passed to the choice field must be managed. Maybe persist them in the entity manager?
        */
        if ($entity_bon_recherche->getUser()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getUser());
        }
        if ($entity_bon_recherche->getUserInitiateur()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getUserInitiateur());
        }
        if ($entity_bon_recherche->getValideur()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getValideur());
        }

        if ($entity_bon_recherche->getSite()) {
            $this->getDoctrine()->getManager()->persist($entity_bon_recherche->getSite());
        }

        $formulaire_bons_recherche = $this->createForm(ObjRechercheBonsAttachementType::class, $entity_bon_recherche);
        if ($request->getMethod() == 'POST') {
            if ($formulaire_bons_recherche->handleRequest($request)->isValid()) {
                // Si un valideur est demandé mais qu'aucun service n'est selectionné, sélection de tous les services
                // Si la recherche sur la validation est effectuée, on analyse le sens de la validation (bon validé ou bon non validés par un service)
                // Si la recherche porte sur les bons non validés, on mets les valeurs des validations à false
                // Paramètre qui indique si la recherche se fait sur les bons validés (valeur par défaut) ou non validés
                if ($entity_bon_recherche->getSensValidation() === null) {
                    // Si aucun service de validation n'est selectionné mais qu'un valideur est renseigné, on recherche les bons validés par ce valideur dans tous les services
                    if ($entity_bon_recherche->getValideur()) {
                        $entity_bon_recherche->setValidationTechnique(true);
                        $entity_bon_recherche->setValidationHoraire(true);
                        $entity_bon_recherche->setValidationSAV(true);
                        $entity_bon_recherche->setValidationFacturation(true);
                    } else {
                        $entity_bon_recherche->setValidationTechnique(false);
                        $entity_bon_recherche->setValidationHoraire(false);
                        $entity_bon_recherche->setValidationSAV(false);
                        $entity_bon_recherche->setValidationFacturation(false);
                    }
                } else {
                    // Si une validation par service est demandées
                    // Si aucun service n'est selectionné, on considère qu'on recherche les bons de tous les services
                    if ((!$entity_bon_recherche->getValidationTechnique()) && (!$entity_bon_recherche->getValidationHoraire()) && (!$entity_bon_recherche->getValidationSAV()) && (!$entity_bon_recherche->getValidationFacturation())) {
                        $entity_bon_recherche->setValidationTechnique(true);
                        $entity_bon_recherche->setValidationHoraire(true);
                        $entity_bon_recherche->setValidationSAV(true);
                        $entity_bon_recherche->setValidationFacturation(true);
                    }
                }

                // Sauvegarde de l'objet recherche de bon d'attachement pour réaffichage des données lors de la prochaine requête
                $session->set('objRechercheBon', $entity_bon_recherche);

                return $this->redirectToRoute('lci_bons_visualiser');
            }
        }

        return $this->render('LciBoilerBoxBundle:Bons:form_recherche_bons.html.twig', array(
            'form' => $formulaire_bons_recherche->createView()
        ));
    }

    // GESTION DES SITES POUR LES BONS D ATTACHEMENTS
    public function indexSiteAction()
    {
        return $this->render('LciBoilerBoxBundle:Bons:index_site_gestion.html.twig', array());
    }

    // Fonction qui permet de visualiser les informations d'un site
    public function visualiserSitesAction($idSiteActif)
    {
        $ents_sitesBA = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:SiteBA')->findAll();
        $ent_siteBA_actif = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:SiteBA')->find($idSiteActif);

        $ent_siteBA_actif->setLienGoogle($this->putZoomApi($this->putApiKey($ent_siteBA_actif->getLienGoogle())));
        //'https://www.google.com/maps/embed/v1/view?key=APIKEY&center='.trim($matches[1]).','.trim($matches[2]).'&zoom=ZOOMAPI&maptype=satellite';

        $latitude = $this->getLatLng('latitude', $ent_siteBA_actif->getLienGoogle());
        $longitude = $this->getLatLng('longitude', $ent_siteBA_actif->getLienGoogle());

        return $this->render('LciBoilerBoxBundle:Bons:visualiser_sitesBA.html.twig', array(
            'ents_sitesBA' => $ents_sitesBA,
            'ent_siteBA_actif' => $ent_siteBA_actif,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'apiKey' => $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur(),
            'zoomApi' => $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur()
        ));
    }

    private function putApiKey($url)
    {
        $apiKey = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('cle_api_google')->getValeur();
        $pattern = '/APIKEY/';
        return (preg_replace($pattern, $apiKey, $url));
    }

    private function putZoomApi($url)
    {
        $zoomApi = $this->get('lci_boilerbox.configuration')->getEntiteDeConfiguration('zoom_api')->getValeur();
        $pattern = '/ZOOMAPI/';
        return (preg_replace($pattern, $zoomApi, $url));
    }

    private function getLatLng($type, $url)
    {
        $pattern = '/^latLng\((.+?),(.+?)\)$/';
        if (preg_match($pattern, $url, $matches)) {
            switch ($type) {
                case 'latitude':
                    return $matches[1];
                case 'longitude':
                    return $matches[2];
            }
        }
        return null;
    }

    private function testEntite($ent_siteBA)
    {
        $intitule = $ent_siteBA->getIntitule();
        if (($intitule == null) || ($intitule == "")) {
            return "Veuillez indiquer un nom de site";
        }
        $adresse = $ent_siteBA->getAdresse();
        if (($adresse == null) || ($adresse == "")) {
            return "Veuillez indiquer une adresse pour le site";
        }
        /*
        $lien = $ent_siteBA->getLienGoogle();
        if (($lien == null) || ($lien == "")) {
            return "Veuillez indiquer un lien google map";
        }
        */
        return 0;
    }
}
