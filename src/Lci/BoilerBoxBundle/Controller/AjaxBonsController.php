<?php

namespace Lci\BoilerBoxBundle\Controller;

use Lci\BoilerBoxBundle\Entity\Validation;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Lci\BoilerBoxBundle\Entity\EquipementBATicket;
use Lci\BoilerBoxBundle\Form\Type\EquipementBATicketType;
use Lci\BoilerBoxBundle\Entity\Contact;
use Lci\BoilerBoxBundle\Form\Type\ContactType;
use Lci\BoilerBoxBundle\Entity\SiteBA;
use Lci\BoilerBoxBundle\Form\Type\SiteBAType;



class AjaxBonsController extends Controller
{
    // Fonction qui modifie le paramètre EnqueteNecessaire d'un bon
    public function setEnqueteAction()
    {
        $idBon = $_POST['identifiant'];
        $entity_bon = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($idBon);
        if ($entity_bon->getEnqueteNecessaire() == true) {
            $entity_bon->setEnqueteNecessaire(false);
        } else {
            $entity_bon->setEnqueteNecessaire(true);
        }
        $this->getDoctrine()->getManager()->flush();
        return new Response();
    }


    public function archiveUnFichierDeBonAction()
    {
		$em 			= $this->getDoctrine()->getManager();
        $id_fichier_bon = $_POST['identifiant_fichier'];
        $entity_fichier = $em->getRepository('LciBoilerBoxBundle:Fichier')->find($id_fichier_bon);

        if ($entity_fichier->getArchive() == false) {
            $message_archivage = "Archivé par " . $this->get('security.token_storage')->getToken()->getUser()->getLabel() . " le " . date('d/m/Y à H:i');
            $entity_fichier->setArchive(true);
            $entity_fichier->setInformations($message_archivage);
        } else {
            $message_archivage = "Désarchivé par " . $this->get('security.token_storage')->getToken()->getUser()->getLabel() . " le " . date('d/m/Y à H:i');
            $entity_fichier->setArchive(false);
            $entity_fichier->setInformations($message_archivage);
        }
        $em->flush();
        return new Response();
    }



    public function archivageFichierSiteBAAction()
    {
		$em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_fichier']) && isset($_POST['archive'])) {
            $id_fichier 	= $_POST['id_fichier'];
            $archive 		= $_POST['archive'];
            $ent_fichier 	= $em->getRepository('LciBoilerBoxBundle:FichierSiteBA')->find($id_fichier);
            switch ($archive) {
                case true:
                    $ent_fichier->setArchive(true);
                    break;
                case false:
                    $ent_fichier->setArchive(false);
                    break;
            }
            $em->flush();
        }
        return new Response();
    }


	// Fonction qui supprime un fichier d'un bon (appelé sur la page de saisie des bons)
	public function delFileAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();

		if ($_GET['id_file'])
		{
			$e_fichier = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:Fichier')->find($_GET['id_file']);
			$em->remove($e_fichier);
			$em->flush();
			echo "Fichier supprimé";
		}
		return new Response();
	}

    // Fonction qui supprime un fichier d'un siteBA (appelé sur la page de saisie des bons)
    public function delFileSiteBAAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if ($_GET['id_file'])
        {
            $e_fichier = $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:FichierSiteBA')->find($_GET['id_file']);
            $em->remove($e_fichier);
            $em->flush();
            echo "Fichier supprimé";
        }
        return new Response();
    }

		

	// Ajout d'un commentaire de facturation après validation SAV
	public function setComFactAction()
	{
		$em             		= $this->getDoctrine()->getManager();
		$id_bon 	 			= $_POST['id_bon'];
		$nouveau_commentaire 	= $_POST['commentaire'];

		$e_user_courant 		= $this->get('security.token_storage')->getToken()->getUser();

		$e_bon 					= $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);

		$commentaires_existants =  $e_bon->getCommentaires();
		$e_bon->setCommentaires($commentaires_existants . "<div class='bons_commentaires_titre'>Par " . $e_user_courant->getLabel() . " le " . date('d/m/Y H:i:s') . "</div><div class='bons_commentaires_text'>" . $nouveau_commentaire . "</div>");

		$em->flush();
		$reponse = array('retour' => 'succes');
		return new Response(json_encode($reponse));
	}



	// CREATION D ENTITE : avec des formulaires envoyés par requête Ajax	***********************************************************************************

	// Gestion des CONTACTS	************************************
    public function creerContactsSitesBAAction(Request $request)
    {
		date_default_timezone_set('Europe/Paris'); 

        $em         = $this->getDoctrine()->getManager();
        $e_contact  = new Contact();
        $f_contact  = $this->createForm(ContactType::class, $e_contact);

        $f_contact->handleRequest($request);
        if ($request->isXMLHttpRequest())
        {
            if ($f_contact->isSubmitted())
            {
                // Si c'est une modification de contact on recrée le formulaire à partir du contact à modifier
                if ($f_contact->get('id')->getData() != null)
                {
                    $e_contact = $this->getDoctrine()->getRepository('LciBoilerBoxBundle:Contact')->find($f_contact->get('id')->getData());
                    $f_contact = $this->createForm(ContactType::class, $e_contact);
                    $f_contact->handleRequest($request);
                }
                if ($f_contact->isValid())
                {
                    $e_contact->setDateMaj(new \Datetime());
                    $em->persist($e_contact);
                    $em->flush();

                    $reponse = array(
                        "message" => $e_contact->getId()
                    );

                    return new Response(json_encode($reponse));
                }
                return $this->render('LciBoilerBoxBundle:Bons:creer_contact.html.twig', [
                    'form_contact'  => $f_contact->createView(),
                    'id'            => $e_contact->getId()
                ]);
            }
        }
        return $this->render('LciBoilerBoxBundle:Bons:creer_contact.html.twig', array(
            'form_contact'  => $f_contact->createView(),
            'id'            => $e_contact->getId()
        ));
    }

    public function supprimerContactAction()
    {
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_contact_supp']))
        {
            $id_contact     = $_POST['id_contact_supp'];
            $ent_contact    = $em->getRepository('LciBoilerBoxBundle:Contact')->find($id_contact);
            $em->remove($ent_contact);
            $em->flush();
            return new Response();
        }
    }

    public function modifierContactAction()
    {
		date_default_timezone_set('Europe/Paris'); 

        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_contact_modif']))
        {
            $id_contact     = $_POST['id_contact_modif'];
            $ent_contact    = $em->getRepository('LciBoilerBoxBundle:Contact')->find($id_contact);
            $ent_contact->setNom($_POST['nomContact']);
            $ent_contact->setPrenom($_POST['prenomContact']);
            $ent_contact->setTelephone($_POST['telephoneContact']);
            $ent_contact->setMail($_POST['emailContact']);
            $ent_contact->setFonction($_POST['fonctionContact']);
            $ent_contact->setDateMaj(new \Datetime());
            $em->flush();
            echo 'Contact '.$_POST['nomContact'].' modifié';
            return new Response();
        }
    }


    public function getInfosContactsAction()
    {
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_contact']))
        {
            // Si id_contact est vide on retourne tous les contacts
            if ($_POST['id_contact'] == null)
            {
                $es_contacts    = $em->getRepository('LciBoilerBoxBundle:Contact')->findBy([], ['nom' => 'ASC']);
                // On retourne le fichier serialize
                $serializer     = $this->get('serializer');
                $jsonContent    = $serializer->serialize(
                    $es_contacts,
                    'json', array('groups' => array('groupContact'))
                );
                return new Response($jsonContent);
            } else {
                $e_contact      = $em->getRepository('LciBoilerBoxBundle:Contact')->find($_POST['id_contact']);
                // On retourne le fichier serialize
                $serializer     = $this->get('serializer');
                $jsonContent    = $serializer->serialize(
                    $e_contact,
                    'json', array('groups' => array('groupContact'))
                );
            }
           	return new Response($jsonContent);
        }
        return new Response();
    }


	// Gestion des EQUIPEMENTS	***************************
    public function creerEquipementAction(Request $request)
    {
        $em             = $this->getDoctrine()->getManager();

        $e_equipement   = new EquipementBATicket();

		$f_equipement   = $this->createForm(EquipementBATicketType::class, $e_equipement);
        $f_equipement->handleRequest($request);


		// Si un id est présent dans le formulaire nous somme sur une modification d'équipement : le formulaire modifié est recu
		if ($f_equipement->get('id')->getData() != null)
		{
			$e_equipement   = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($f_equipement->get('id')->getData());
		} else if (isset($_POST['id_equipement']))
        {
			// Nous somme sur une demande de modification d'équipement : Le formulaire de l'équipement doit être envoyé
            // Permet de gérer la modification de l'équipement
            $e_equipement   = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($_POST['id_equipement']);
        }

		$f_equipement   = $this->createForm(EquipementBATicketType::class, $e_equipement);
		$f_equipement->handleRequest($request);

        if ($request->isXMLHttpRequest())
        {
            if ($f_equipement->isSubmitted())
            {
                if ($f_equipement->isValid())
                {
					$e_siteBA = $em->getRepository('LciBoilerBoxBundle:SiteBA')->find($f_equipement->get('siteBA')->getData()->getId());

					// Si l'équipement n'est pas déjà associé au site on l'associe
                   	if (! $e_siteBA->getEquipementBATickets()->contains($e_equipement))
                    {
						// L'entité Site gère la relation inverse pour l'association d'équipement 
                    	$e_siteBA->addEquipementBATicket($e_equipement);
					}
                    $em->persist($e_equipement);
                    $em->flush();


        			// On retourne le fichier serialize
        			$serializer = $this->get('serializer');
        			$jsonContent = $serializer->serialize(
            			$e_equipement,
            			'json', array('groups' => array('groupEquipement'))
        			);
        			return new Response($jsonContent);
/*
					$reponse = array(
                		"message" => $e_equipement->getId()
            		);
        			return new Response(json_encode($reponse));
*/

					
                }
                return $this->render('LciBoilerBoxBundle:Bons:creer_equipement.html.twig', [
                    'form_equipement'   => $f_equipement->createView(),
                    'id'                => $e_equipement->getId()
                ]);
            }
			// On retourne le fichier serialize
                    $serializer = $this->get('serializer');
                    $jsonContent = $serializer->serialize(
                        $e_equipement,
                        'json', array('groups' => array('groupEquipement'))
                    );
                    return new Response($jsonContent);

        }
        return $this->render('LciBoilerBoxBundle:Bons:creer_equipement.html.twig', array(
            'form_equipement'   => $f_equipement->createView(),
            'id'                => $e_equipement->getId()
        ));
    }
    public function delEquipementAction()
    {
        $em = $this->getDoctrine()->getManager();

        if (isset($_POST['id_equipement']))
        {
            $e_equipement = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($_POST['id_equipement']);

            $em->remove($e_equipement);
            $em->flush();
            echo "suppression de ".$_POST['id_equipement']." effectuée";
        }
        return new Response();
    }

    // Fonction qui assigne un équipement à un bon
    public function assignEquipementToBonAction(Request $request)
    {
        $em             = $this->getDoctrine()->getManager();
        $e_equipement   = new EquipementBATicket();
        $f_equipement   = $this->createForm(EquipementBATicketType::class, $e_equipement);
        $f_equipement->handleRequest($request);

        if ($request->isXMLHttpRequest())
        {
            $id_equipement  = $_POST['id_equipement'];
            $id_bon         = $_POST['id_bon'];
            $e_equipement   = $em->getRepository('LciBoilerBoxBundle:EquipementBATicket')->find($id_equipement);
            $e_bon          = $em->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_bon);

            // On affecte l'équipement au site du bon
            $e_equipement->setSiteBA($e_bon->getSite());

            // L'assignation se fait du coté du bon : Il gère l'assignation inverse
            $e_bon->addEquipementBATicket($e_equipement);

            $reponse = array(
                "message" => 'success'
            );
        } else {
            $reponse = array(
                "message" => 'pas de requête xml reçue'
            );
        }
        return new Response(json_encode($reponse));
    }





    // Generate an array contains a key -> value with the errors where the key is the name of the form field
    protected function getErrorMessages(\Symfony\Component\Form\Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $key => $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if (!$child->isValid()) {
                $errors[$child->getName()] = $this->getErrorMessages($child);
            }
        }

        return $errors;
    }


}
