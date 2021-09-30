<?php

namespace Lci\BoilerBoxBundle\Controller;

use Lci\BoilerBoxBundle\Entity\CGU;
use Lci\BoilerBoxBundle\Form\Type\CGUType;
use Otp\Otp;
use ParagonIE\ConstantTime\Encoding;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CGUController extends Controller
{
    public function formCGUAction(Request $request)
    {
        $cgu = new CGU();
        $form = $this->createForm(CGUType::class, $cgu);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $serviceCGU = $this->container->get('lci_boilerbox.cgu');
            $serviceCGU->createCGU($cgu, $this->getUser());
            $request->getSession()->getFlashBag()->add('info', 'Les CGU courantes ont été ajoutées');

            return $this->redirectToRoute('lci_cgu_show');
        }

        return $this->render('@LciBoilerBox/CGU/form_cgu.html.twig', array(
            'form' => $form->createView()
        ));
    }

    public function deleteCGUAction(Request $request, CGU $cgu)
    {
        if ($cgu->getCguCourant()) {
            $request->getSession()->getFlashBag()->add('info', 'Les CGU courantes ne peuvent pas être supprimées');

            return $this->redirectToRoute('lci_cgu_show');
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($cgu);
        $em->flush();

        return $this->redirectToRoute('lci_cgu_show');
    }

    public function showCGUAction()
    {
        return $this->render('@LciBoilerBox/CGU/liste_cgu.html.twig', array(
            'filtre' => false,
            'tab_cgu' => $this->getDoctrine()->getRepository(CGU::class)->findAll(),
        ));
    }

    public function telechargerCGUAction(CGU $cgu) {
        // Envoi de la réponse
        $chemin_file = $cgu->getUploadsDirectory().$cgu->getFilename();
        $response = new Response();
        if (is_file($chemin_file)) {
            $response->headers->set('Content-Type', 'application/pdf');
            $response->headers->set('Content-Disposition', 'inline;filename="'.$cgu->getFilename().'"');
            $response->headers->set('Content-Length', filesize($chemin_file));
            $response->setContent(file_get_contents($chemin_file));
            $response->setCharset('UTF-8');
        } else {
            $response->setContent('file not found');
        }

        return $response;
    }

    public function lectureCGUAction(SessionInterface $session) {
        if ($session->get('auth') === true) {
            if ($session->get('totp_auth') === false) {
                $session->set('auth', true);
            }
        } else {
            if ($session->get('auth') == false) {
                $session->set('auth', true);
            }
        }

        $cgu = $this->getDoctrine()->getManager()->getRepository(CGU::class)->findOneBy(['cguCourant' => true]);

        return $this->render('@LciBoilerBox/CGU/lecture_cgu.html.twig', array(
            'cgu' => $cgu,
        ));
    }

    public function accepterCGUAction() {
        $this->getUser()->setCguAccepte(true);
        $this->getDoctrine()->getManager()->flush();

       return $this->redirectToRoute('lci_boilerbox_accesSite');
    }
}
