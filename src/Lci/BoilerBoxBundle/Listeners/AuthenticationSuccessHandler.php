<?php
//var/www/html/BoilerBox.fr/BoilerBox/src/Lci/BoilerBoxBundle/Listeners/AuthenticationSuccessHandler.php

namespace Lci\BoilerBoxBundle\Listeners;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class AuthenticationSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($token->getUser()->getCguAccepte()) {
            return new RedirectResponse($this->router->generate('lci_boilerbox_index'));
        }

        return new RedirectResponse($this->router->generate('lci_cgu_lecture'));
    }
}