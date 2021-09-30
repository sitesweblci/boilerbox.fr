<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;



class ValidationType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $option) {
		$builder->add('valide', CheckboxType::class, array(
					'label'         => ' ',
					'label_attr'        => array('class' => 'label_smalltext'),
					'attr'          => array(
                        'class'             => 'input_checkbox'
                    )
				));
				/*
				->add('dateDeValidation', 'date',array(
					'label'         => 'Date de la signature',
            		'label_attr'    => array ('class' => 'label_smalltext'),
            		'widget'        => 'single_text',
            		'html5'         => false,
            		'format'        => 'dd-MM-yyyy',
            		'invalid_message' => 'Format de la date incorrect.',
            		'attr'          => array(
            		    'class'         => 'smallinput',
            		    'placeholder'   => 'dd/mm/YYYY',
            		    'maxlength'     => 10
            		)
				))
				->add('user', 'entity', array(
					'class' 		=> 'LciBoilerBoxBundle:User',
					'required' 		=> true,
					'label' 		=> 'Validé par',
					'label_attr'      => array ('class' => 'label_smalltext'),
            		'attr'            => array ('class' => 'smallselect'),
            		'property'        => 'label',
					'query_builder'   => function(EntityRepository $er){
                		return $er->createQueryBuilder('u')->orderBy('u.label', 'ASC');
            		},
				));
				*/
	}



    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Validation'
        ));
    }


    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_validation';
    }


}

