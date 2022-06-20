<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lci\BoilerBoxBundle\Form\Type\ValidationType;




class TicketIncidentValidationType extends AbstractType {
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
	public function buildForm(FormBuilderInterface $builder, array $option) {
		$builder
		->add('validationIntervention', ValidationType::class, array(
            'label' => 'Intervention demandée',
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Validation'
        ))
        ->add('validationCloture', ValidationType::class, array(
            'label' => 'Cloture',
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Validation'
        ));
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
        return 'lci_boilerboxbundle_bonsAttachement';
    }

}
