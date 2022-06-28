<?php
// src/Ipc/ProgBundle/Services/Mailing/ServiceMailing.php
namespace Lci\BoilerBoxBundle\Services;

// Service d'envoi de mails ( par SMTP )
class ServiceMailing
{
protected $mailer;
protected $templating;
protected $fichier_log = 'mailing.log';
protected $log;
protected $mail_administrateur;
protected $logo;
protected $service_configuration;
protected $url_boilerbox;
private $from = array('no-reply-assistance-boilerbox@lci-group.fr' => 'Assistance BoilerBox');

	public function __construct(\Swift_Mailer $mailer, $templating, $mail_administrateur, $loging, $service_configuration, $url_boilerbox) {
		$this->mailer 				 = $mailer;
		$this->templating 			 = $templating;
		$this->mail_administrateur 	 = $mail_administrateur;
		$this->log 					 = $loging;
		$this->logo 				 = __DIR__.'/../../../../web/bundles/lciboilerbox/images/logo_lci.jpg';
		$this->service_configuration = $service_configuration;
		$this->url_boilerbox 		 = $url_boilerbox;
	}


    public function sendMailMultiDestinataires($sujet, $titre, $tab_contenu, $tab_destinataires) {
		$liste_destinataires = "";
		foreach($tab_destinataires as $destinataire) {
			$liste_destinataires .= $destinataire.',';
		}

		$liste_destinataires = substr($liste_destinataires, 0, -1);
		$message = \Swift_Message::newInstance()
            ->setSubject($sujet)
            ->setFrom($this->from)
            ->setTo($tab_destinataires);

        $image_link = $message->embed(\Swift_Image::fromPath($this->logo));
        $message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_bons.html.twig', array('titre' => $titre, 'tab_contenu' => $tab_contenu, 'image_link' => $image_link)));
        $message->setContentType('text/html');
        $nb_delivery = $this->mailer->send($message);
        if ($nb_delivery == 0) {
            $this->log->setLog("[ERROR] [MAIL];Echec de l'envoi de l'email : [Dévalidation de Bons] à $liste_destinataires", $this->fichier_log);
            return(1);
        } else {
            $this->log->setLog("[MAIL];Mail de dévalidation envoyé à $liste_destinataires", $this->fichier_log);
            return(0);
        }
    }

    // Mail envoyé lors de la validation d'une pièce au gestionnaire de pieces (désigné dans le fichier parameters.yml)
    public function sendMailPieces($type, $nom_site, $code_affaire, $numero_bon, $label_demandeur, $destinataire)
    {
		if ($type == 'demande')
		{
        	$message = \Swift_Message::newInstance()
        	    ->setSubject("Demande d'offre de pièces pour l'affaire $code_affaire ( $nom_site )")
				->setFrom($this->from)
        	    ->setTo([$destinataire]);
			$image_link = $message->embed(\Swift_Image::fromPath($this->logo));
        	$message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_pieces.html.twig', array('nom_site' => $nom_site, 'code_affaire' => $code_affaire, 'numero_bon' => $numero_bon, 'label_demandeur' => $label_demandeur,  'image_link' => $image_link)));

		} else if ($type == 'annulation') {
			$message = \Swift_Message::newInstance()
                ->setSubject("Annulation de demande d'offre de pièces pour l'affaire $code_affaire ( $nom_site )")
				->setFrom($this->from)
                ->setTo([$destinataire]);	
			$image_link = $message->embed(\Swift_Image::fromPath($this->logo));
        	$message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_pieces_annulation.html.twig', array('nom_site' => $nom_site, 'code_affaire' => $code_affaire, 'numero_bon' => $numero_bon, 'label_demandeur' => $label_demandeur,  'image_link' => $image_link)));
		} else if($type == 'faite') {
			$message = \Swift_Message::newInstance()
                ->setSubject("Offre de pièces effectuée pour l'affaire $code_affaire ( $nom_site )")
				->setFrom($this->from)
                ->setTo([$destinataire]);
            $image_link = $message->embed(\Swift_Image::fromPath($this->logo));
            $message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_pieces_faite.html.twig', array('nom_site' => $nom_site, 'code_affaire' => $code_affaire, 'numero_bon' => $numero_bon, 'label_demandeur' => $label_demandeur,  'image_link' => $image_link)));
		}

        $message->setContentType('text/html');
        $nb_delivery = $this->mailer->send($message);
        if ($nb_delivery == 0) {
            $this->log->setLog("[ERROR] [MAIL];Echec de l'envoi de l'email : [Offre de pièce BA] à $destinataire", $this->fichier_log);
            return(1);
        } else {
            $this->log->setLog("[MAIL];Mail d'offre de pièce envoyé à $destinataire", $this->fichier_log);
            return(0);
        }
    }




	public function sendProblemeTechniqueMail($sender, $destinataire, $message_probleme_technique) {
		$message = \Swift_Message::newInstance()
			->setSubject('Affectation de problème technique')
			->setFrom($this->from)
			->setTo($destinataire);
		$image_link = $message->embed(\Swift_Image::fromPath($this->logo));
		$message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_probleme_technique.html.twig', array('liste_contenus' => $message_probleme_technique, 'image_link' => $image_link)));
		$message->setContentType('text/html');
		$nb_delivery = $this->mailer->send($message); 
		if ($nb_delivery == 0) {
			$this->log->setLog("[ERROR] [MAIL];Echec de l'envoi de l'email : [Affectation de problème technique] à $destinataire", $this->fichier_log);
			return(1);
		} else {
			$this->log->setLog("[MAIL];Mail envoyé à $destinataire", $this->fichier_log);
			return(0);
		}
	}


    public function sendRapportIndisponibilite($t_entities_sites_injoignables, $t_entities_sites_indisponibles, $str_erreur_crontab) {
        $message = \Swift_Message::newInstance()
            ->setSubject("Rapport d'indisponibilité Boiler-box")
            ->setFrom($this->mail_administrateur)
            ->setTo($this->mail_administrateur);
		$image_link = $message->embed(\Swift_Image::fromPath($this->logo));
        $message ->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_rapport_indisponibilite.html.twig', array(
            't_entities_sites_injoignables' => $t_entities_sites_injoignables,
            't_entities_sites_indisponibles' => $t_entities_sites_indisponibles,
			'str_erreur_crontab' => $str_erreur_crontab,
            'image_link' => $image_link
        )));
        $message->setContentType('text/html');
        $nb_delivery = $this->mailer->send($message);
        if ($nb_delivery == 0) {
            $this->log->setLog("[ERROR] [MAIL];Echec de l'envoi de l'email : [Rapport d'indisponibilité des sites] à ".$this->mail_administrateur, $this->fichier_log);
            return(1);
        } else {
			$this->log->setLog("[MAIL];Mail envoyé à ".$this->mail_administrateur, $this->fichier_log);
            return(0);
        }
    }

	
	public function sendAllMails() {
		$cheminConsole = __DIR__.'/../../../../../bin/console';
		$commande = "php $cheminConsole swiftmailer:spool:send ";
		$retour = shell_exec($commande);
		return(0);
	}


	public function sendMailSiteEnErreur($emetteur, $destinataire, $sujet, $tab_message)  
	{
        $message = \Swift_Message::newInstance()->setSubject($sujet)
                	->setFrom($emetteur)
                   	->setTo($destinataire);
		$chemin_image = __DIR__.'/../../../../web/bundles/lciboilerbox/images/logo_lci.jpg';
		$image_link = $message->embed(\Swift_Image::fromPath($this->logo));
		$message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_site_en_erreur.html.twig', 
                			[ 	'tab_message'   => $tab_message,
								'image_link'    => $image_link
							]
            			),
            			'text/html'
        			)
    	;
		$this->log->setLog("[MAIL];Mail de vérification de site : ".$tab_message['titre']." : ".$tab_message['affaire']." envoyé", $this->fichier_log);
    	$this->mailer->send($message);
		return (0);
	}



	/* La variable tab_message doit être instanciée comme suit : 
		tab_message['titre']
		tab_message['site']
		tab_message['contact'] 
		tab_message['fichiers']
	*/
	public function sendMail($emetteur, $destinataire, $sujet, $tab_message) {
		$message = \Swift_Message::newInstance()->setSubject($sujet)
												->setFrom($emetteur)
												->setTo($destinataire);
		$chemin_image = __DIR__.'/../../../../web/bundles/lciboilerbox/images/logo_lci.jpg';
		$image_link = $message->embed(\Swift_Image::fromPath($this->logo));

		$message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email.html.twig', array(
			'tab_message' 	=> $tab_message,
			'image_link' 	=> $image_link,
			'longitude'		=> 150.644,
			'latitude'		=> -34.397,
			'zoomApi'       => $this->service_configuration->getEntiteDeConfiguration('zoom_api')->getValeur(),
			'apiKey' 		=> $this->service_configuration->getEntiteDeConfiguration('cle_api_google')->getValeur()
		)));
		$message->setContentType('text/html');
		$nb_delivery = $this->mailer->send($message);
        if ($nb_delivery == 0) {
            $this->log->setLog("[ERROR] [MAIL];Echec de l'envoi de l'email à $destinataire", $this->fichier_log);
            return(1);
        } else {
            $this->log->setLog("[MAIL];Mail envoyé à $destinataire", $this->fichier_log);
            return(0);
        }
	}


    public function sendMailRapportSuiviDesSites($emetteur, $destinataire, $sujet, $tab_message)
    {
        $message = \Swift_Message::newInstance()->setSubject($sujet)
                    ->setFrom($emetteur)
                    ->setTo($destinataire);
        $chemin_image = __DIR__.'/../../../../web/bundles/lciboilerbox/images/logo_lci.jpg';
        $image_link = $message->embed(\Swift_Image::fromPath($this->logo));
        $message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_rapport_suivi_des_sites.html.twig',
                            [   'tab_message'   => $tab_message,
                                'image_link'    => $image_link
                            ]
                        ),
                        'text/html'
                    )
        ;
        $this->log->setLog("[MAIL];Mail de rapport des sites envoyé: ", $this->fichier_log);
        $this->mailer->send($message);
        return (0);
    }

    /* fonction d'envoi d'email pour les dev */
    public function sendMailRegister($user)
    {
        $message = \Swift_Message::newInstance()->setSubject("Création de votre accès BoilerBox")
					->setFrom($this->from)
                    ->setTo($user->getEmail());
		$confirmationUrl = $this->url_boilerbox.'register/confirm/'.$user->getConfirmationToken();
		
        //$chemin_image = __DIR__.'/../../../../web/bundles/lciboilerbox/images/logo_lci.jpg';
		$chemin_image = __DIR__.'/../../../../src/Lci/BoilerBoxBundle/Resources/public/images/mail/graphique.jpg';
        $image_link = $message->embed(\Swift_Image::fromPath($chemin_image));
        $message->setBody($this->templating->render('FOSUserBundle:Registration:email.txt.twig',
                            [   
                                'image_link'    => $image_link,
                                'user'          => $user,
								'mot_de_passe'	=> $user->getPlainPassword(),
                                'confirmationUrl' => $confirmationUrl,
								'url_boilerbox'		=> $this->url_boilerbox
                            ]
                        ),
                        'text/html'
                    )
        ;
        $this->log->setLog("[MAIL];Mail de dev envoyé: ", $this->fichier_log);
        $this->mailer->send($message);
        return (0);
    }




	// ****************				EMAIL TICKETS			************************************
	/* Attend en entrée : 


        $tab_email                  = array();
        $tab_email['sujet']         = "Ouverture de ticket d'incident sur BoilerBox";
        $tab_email['from']          = null;
        $tab_email['to']            = array($e_ticket->getEmailContactClient());
		$tab_email['cc']            = array('assistance_ibc@lci-group.fr');
        $tab_email['titre']         = "Bonjour";
        $tab_email['sous-titre']    = "Le ticket d'incident n°" . $e_ticket->getNumeroBA() . " a été ouvert dans nos services suite à votre appel.";
        $tab_email['contenu']       = "Ci dessous les informations que vous nous avez fait parvenir :\n\n";
        $tab_email['contenu']       .= "<div style='border:1px solid black; padding:10px;'>$motif_client</div>";
        $tab_email['footer']        = "Nous mettons tout en oeuvre pour résoudre votre problème et vous répondre dans les meilleurs délais\n";
        $tab_email['footer']        .="Merci de ne pas répondre directement à ce message.";
	*/
    public function sendEmail($tab_email)
    {
		if ($tab_email['from'] != null)
		{
			$from = $tab_email['from'];
		} else{
			$from = $this->from;
		}

		if ($tab_email['cc'] != null)
        {
            $cc = $tab_email['cc'];
        } 

		
        $message 	= \Swift_Message::newInstance()
                		->setSubject($tab_email['sujet'])
						->setFrom($from)
                		->setTo([implode(',', $tab_email['to'])])
						->setContentType('text/html');
		if ($cc)
		{
			$message->setCc($cc);
		}

        $image_link = $message->embed(\Swift_Image::fromPath($this->logo));

        $message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_general.html.twig', array('tab_email' => $tab_email,   'image_link' => $image_link)));

        $nb_delivery = $this->mailer->send($message);

        if ($nb_delivery == 0) 
		{
            $this->log->setLog("[ERROR] [MAIL];Echec de l'envoi de l'email : " . $tab_email['sujet'] . " à " . implode(' - ',$tab_email['to']), $this->fichier_log);
            return(1);
        } else {
            $this->log->setLog("[MAIL];Mail envoyé à " . implode(' - ',$tab_email['to']), $this->fichier_log);
            return(0);
        }
    }


}
