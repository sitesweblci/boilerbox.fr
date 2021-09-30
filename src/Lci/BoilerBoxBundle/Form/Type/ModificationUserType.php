<?php
// Lci/BoilerBox/Form/Type/ModificationUserType.php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
//use FOS\UserBundle\Form\Type\RegistrationFormType as BaseType; 
use Lci\BoilerBoxBundle\Form\Type\RegistrationFormType as BaseType;



class ModificationUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder;
    }

    public function getParent() {
	    return BaseType::class;
    }

	public function getName() {
		 return 'lci_user_update';
	}

}
