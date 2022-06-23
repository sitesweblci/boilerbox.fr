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



class AjaxTicketController extends Controller
{

	public function envoiMailClotureAction()
	{
		$id_e_ticket 	= $_POST['id_entity'];
		$e_ticket 		= $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($id_e_ticket);
		$texte 			= $_POST['commentaire'];
		$service_mail 	= $this->get('lci_boilerbox.mailing');
    	$service_mail->sendMailClotureTicketIncident($e_ticket->getSite()->getIntitule(), $e_ticket->getNumeroAffaire(), $e_ticket->getNumeroBA(), $e_ticket->getUser()->getLabel(), $e_ticket->getNomDuContact(), $e_ticket->getEmailContactClient(), $texte);
		echo "Mail envoyé";
		return new Response();
	}

}