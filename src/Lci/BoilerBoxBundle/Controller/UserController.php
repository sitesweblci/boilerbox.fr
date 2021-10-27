<?php

namespace Lci\BoilerBoxBundle\Controller;

use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Model\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Lci\BoilerBoxBundle\Form\Type\ModificationUserType;

class UserController extends Controller
{
    // Modification de l'utilisateur courant (Lorsqu'un utilisateur modifie ses propres informations
    public function userUpdateOwnAction($idUtilisateur, Request $request)
    {
        if (($this->get('security.token_storage')->getToken()->getUser()->getId() == $idUtilisateur) || ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('LciBoilerBoxBundle:User')->find($idUtilisateur);
            $form_user_update = $this->createForm(ModificationUserType::class, $user);
            $form_user_update->handleRequest($request);
            if ($form_user_update->isSubmitted() && $form_user_update->isValid()) {
                $em->flush();
                $request->getSession()->getFlashBag()->add('info', 'Utilisateur ' . $user->getLabel() . ' modifié');

                // Si les mots de passes ont été modifiés - Déconnexion de l'utilisateur
                if ($_POST['modification_user']['plainPassword']['first'] != '') {
                    $dispatcher = $this->get('event_dispatcher');
                    $event = new GetResponseUserEvent($user, $request);
                    $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_INITIALIZE, $event);

                    $userManager = $this->get('fos_user.user_manager');
                    $event = new FormEvent($form_user_update, $request);
                    $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_SUCCESS, $event);
                    $userManager->updateUser($user);
                    if (null === $response = $event->getResponse()) {
                        // Si c'est l'utilisateur qui modifie son mot de passe on le déconnecte	| Si NON on renvoi l'Admin vers la page Gestion des utilisateurs
                        if ($this->get('security.token_storage')->getToken()->getUser()->getId() == $idUtilisateur) {
                            $url = $this->generateUrl('fos_user_security_logout');
                        } else {
                            $url = $this->generateUrl('lci_accueil_register_user');
                        }
                        $response = new RedirectResponse($url);
                    }
                    $dispatcher->dispatch(FOSUserEvents::CHANGE_PASSWORD_COMPLETED, new FilterUserResponseEvent($user, $request, $response));
                    return $response;
                }
                // Sinon retour sur la page d'accueil
                return $this->redirectToRoute('lci_boilerbox_accesSite');
            }
            return $this->render('LciBoilerBoxBundle:Registration:changeUserOwnRegistration.html.twig', array(
                'user' => $user,
                'form' => $form_user_update->createView()
            ));
        } else {
            throw $this->createNotFoundException("La page demandée n'existe pas");
        }
    }

    /*
    public function accueilUserRegistrationAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        if ($request->getMethod() == 'POST') {
            $choix_action = $_POST['choixAction'];
            switch($choix_action) {
            case 'deleteUser':
                if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
                {
                    $user = $em->getRepository('LciBoilerBoxBundle:User')->find($_POST['choix_utilisateur']);
                    $em->remove($user);
                    $em->flush();
                }
            break;
            case 'updateUser':
                if (isset($_POST['newPassword'])) {
                    //  Si le mot de passe est à réinitialiser
                    $user = $em->getRepository('LciBoilerBoxBundle:User')->find($_POST['idUtilisateur']);
                    $form_user_update = $this->createForm(ModificationUserType::class, $user);
                    $password = trim($_POST['motDePasse']);
                    $salt = $user->getSalt();
                    $password = $this->get('security.encoder_factory')->getEncoder($user)->encodePassword($password, $salt);
                    $user->setPassword($password);
                    $em->flush();
                } else {
                    // Si une demande de modification d'utilisateur est demandée
                    $user = $em->getRepository('LciBoilerBoxBundle:User')->find($_POST['choix_utilisateur']);
                    $form_user_update = $this->createForm(ModificationUserType::class, $user);
                    return $this->render('LciBoilerBoxBundle:Registration:changeUserOwnRegistration.html.twig',array(
                        'user' => $user,
                        'form' => $form_user_update->createView()
                    ));
                }
            break;
            case 'createUser':
                if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN'))
                {
                    return $this->redirect($this->generateUrl('fos_user_registration_register'));
                }
            break;
            }
        }
        $liste_users    = $em->getRepository('LciBoilerBoxBundle:User')->findBy(array(), array('label' => 'ASC'));
        return $this->redirectToRoute('lci_boilerbox_accesSite');
    }
    */


}
