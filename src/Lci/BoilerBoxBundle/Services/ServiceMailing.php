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

	public function __construct(\Swift_Mailer $mailer, $templating, $mail_administrateur, $loging, $service_configuration) {
		$this->mailer = $mailer;
		$this->templating = $templating;
		$this->mail_administrateur = $mail_administrateur;
		$this->log = $loging;
		$this->logo = __DIR__.'/../../../../web/bundles/lciboilerbox/images/logo_lci.jpg';
		$this->service_configuration = $service_configuration;
	}


    public function sendMailMultiDestinataires($sujet, $titre, $tab_contenu, $tab_destinataires) {
		$liste_destinataires = "";
		foreach($tab_destinataires as $destinataire) {
			$liste_destinataires .= $destinataire.',';
		}

		$liste_destinataires = substr($liste_destinataires, 0, -1);
		$message = \Swift_Message::newInstance()
            ->setSubject($sujet)
            ->setFrom('Assistance_IBC@lci-group.fr')
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
    public function sendMailPieces($nom_site, $numero_bon, $label_demandeur, $destinataire) 
	{
        $message = \Swift_Message::newInstance()
            ->setSubject("Offre à faire pour l'affaire $nom_site")
            ->setFrom('Assistance_IBC@lci-group.fr')
            ->setTo([$destinataire]);

        $image_link = $message->embed(\Swift_Image::fromPath($this->logo));
        $message->setBody($this->templating->render('LciBoilerBoxBundle:Mail:email_pieces.html.twig', array('nom_site' => $nom_site, 'numero_bon' => $numero_bon, 'label_demandeur' => $label_demandeur,  'image_link' => $image_link)));
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
			->setFrom('Assistance_IBC@lci-group.fr')
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
    public function sendMailRegister($destinataire, $sujet, $user)
    {
        $message = \Swift_Message::newInstance()->setSubject($sujet)
                    ->setFrom('Assistance@lci-group.fr')
                    ->setTo($destinataire);
		$confirmationUrl = 'http://boiler-box.fr/register/confirm/'.$user->getConfirmationToken();
		
        $chemin_image = __DIR__.'/../../../../web/bundles/lciboilerbox/images/logo_lci.jpg';
        $image_link = $message->embed(\Swift_Image::fromPath($chemin_image));
        $message->setBody($this->templating->render('FOSUserBundle:Registration:email.html.twig',
                            [   
                                'image_link'    => $image_link,
                                'user'          => $user,
								'password'		=> $user->getPlainPassword(),
                                'confirmationUrl' => $confirmationUrl
                            ]
                        ),
                        'text/html'
                    )
        ;
        $this->log->setLog("[MAIL];Mail de dev envoyé: ", $this->fichier_log);
        $this->mailer->send($message);
        return (0);
    }

}
