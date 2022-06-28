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

	public function sendEmailClotureTicketClientAction()
	{
		$e_ticket 		= $this->getDoctrine()->getManager()->getRepository('LciBoilerBoxBundle:BonsAttachement')->find($_POST['id_entity']);

		if ($e_ticket->getEmailContactClient())
		{
			$service_mailling   = $this->get('lci_boilerbox.mailing');

        	$tab_email                  = array();
        	$tab_email['sujet']         = "Clôture de ticket d'incident sur BoilerBox";
        	$tab_email['from']          = null;
        	$tab_email['to']            = array($e_ticket->getEmailContactClient());
        	$tab_email['cc']            = array('assistance_ibc@lci-group.fr');
        	$tab_email['titre']         = "Bonjour";
        	$tab_email['sous-titre']    = "Le ticket d'incident n°" . $e_ticket->getNumeroBA() . " a été clôturé par nos services.";
        	$tab_email['contenu']       = "Ci dessous les informations sur la(les) solution(s) apportée(s) :\n\n";
        	$tab_email['contenu']       .= "<div style='border:1px solid black; padding:10px;'>" . $_POST['commentaire'] . "</div>";
        	$tab_email['footer']        = "A bientôt sur <a href='http://boiler-box.fr'>BoilerBox</a>\n";
        	$tab_email['footer']        .="Merci de ne pas répondre directement à ce message.";

        	$service_mailling->sendEmail($tab_email);
			echo "Mail envoyé";
		} else {
			echo "Pas de mail client défini : Email de cloture non envoyé";
		}
		return new Response();
	}

}
