<?php

namespace Lci\BoilerBoxBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class LciBoilerBoxBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
