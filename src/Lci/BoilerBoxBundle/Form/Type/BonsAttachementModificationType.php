<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


use Doctrine\ORM\EntityRepository;
use Lci\BoilerBoxBundle\Form\Type\FichierType;
use Lci\BoilerBoxBundle\Form\Type\BonsAttachementType as BaseType;


class BonsAttachementModificationType extends BaseType {
	/**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
	public function buildForm(FormBuilderInterface $builder, array $options) 
	{
		parent::buildForm($builder, $options);
		$builder
		->remove('dateSignature')
		->add('numeroBA', TextType::class, array(
            'label'         => 'Numéro du bon',
            'label_attr'    => array ('class' => 'label_smalltext'),
            'required'      => true,
            'trim'          => true,
            'attr'          => array(
                'placeholder'   => 'XXXXXX',
                'maxlength'     => 6
            )
        ))
        ->add('dateSignature', DateType::class, array(
            'label'         => false,
            'widget'        => 'single_text',
            'html5'         => false,
            'format'        => 'dd/MM/yyyy',
            'invalid_message' => 'Format de la date incorrect.',
            'attr'          => array(
                'placeholder'   => 'dd/mm/YYYY',
                'maxlength'     => 10,
                'class'         => ''
            ),
            'required'      => false
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
