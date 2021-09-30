Dossier non passés avec GIT CLONE	*****************************************
Le dossier web/logs/ n'est pas transmis par git


Chemins écris en dur : A modifier sur le serveur boilerbox.fr ***************************
web/sh/analyseSitesAccess.sh
web/sh/analyseModulesAccess.sh



!! Nommenclatures des sites et modules

Les modules LTS doivent débuter par M*** sinon Echec de la fonction suivante : 
->Page src/Lci/BoilerBoxBundle/Service/ServiceUtilitaires/ : public function analyseAccess($sitesOuModules)

Les sites LCI ne doivent pas débuter par M***

***




Url utilisées	********************


Description											Chemin							Url																Fonction 
**********************************************************************************************************************************************************************
Page d'accueil									lci_boilerbox_index 			/									BoilerBoxController.php: accesSiteAction(SessionInterface $session, Request $request)
Page d'accueil									lci_boilerbox_accesSite			/lci/accesSite
	
Ajout d'un site									lci_register_site				/lci/admin/register/site			AdminController.php: siteRegistrationAction(Request $request)
Modification d'un site							lci_site_update					/lci/admin/site/update/{idSite}		AdminController.php: modificationSiteAction($idSite = null, Request $request)

Ajout du lien entre utilisateur et site			lci_registration_link			/lci/admin/register/link			AdminController.php: linkUserSitesAction(Request $request) 
Ajout du lien entre site et utilisateur			lci_registration_userslink		/lci/admin/register/userslink		AdminController.php: linkSiteUsersAction(Request $request)


Page d'accueil des configurations du site		lci_gestion_admin				/lci/admin/accueil					AdminController.php: accueilAction()

Modification du mot de passe					lci_change_password				/lci/changePassword					UserController:changePasswordAction(Request $request)

Page d'accueil des Bons d'attachements			lci_bons_attachements			/lci/bons/accueil					BonsController:indexAction(Request $request) 

Page d'accueil des Problèmes Techniques			lci_gestion_parc				/lci/gestionnaire_parc/index		GestionParcController:accueilAction()

Lien de déconnexion								fos_user_security_logout

