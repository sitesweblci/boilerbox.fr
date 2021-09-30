<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementType as BaseType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;


class BonsAttachementModification2Type extends BaseType {
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
	public function buildForm(FormBuilderInterface $builder, array $options) {
		parent::buildForm($builder, $options);

		$builder->remove('userInitiateur');
		$builder->remove('user');
        $builder->remove('dateInitialisation');
        $builder->remove('numeroAffaire');
        $builder->remove('site');
		$builder->remove('fichiersPdf');
	}


    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\BonsAttachement'
        ));
    }


    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_modifierBonsAttachement';
    }

}
