<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lci\BoilerBoxBundle\Form\Type\ValidationType;




class BonsAttachementValidationType extends AbstractType {
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
	public function buildForm(FormBuilderInterface $builder, array $option) {
		$builder
		->add('validationTechnique', ValidationType::class, array(
			'label' => 'Validation Technique',
			'data_class' => 'Lci\BoilerBoxBundle\Entity\Validation'
		))
		->add('validationHoraire', ValidationType::class, array(
			'label' => 'Validation Pièces',
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Validation'
		))
		->add('validationSAV', ValidationType::class, array(
			'label' => 'Validation SAV',
			'data_class' => 'Lci\BoilerBoxBundle\Entity\Validation'
		))
		->add('validationFacturation', ValidationType::class, array(
            'label' => 'Validation Facturation',
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
