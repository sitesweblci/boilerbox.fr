<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class EquipementType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('id', IntegerType::class, array (
                    'label_attr'      => array ('class' => 'identifiant'),
                    'attr'            => array ('class' => 'identifiant')
            	))
				->add('type', TextType::class, array(
					'label'   	=> "Type d'equipement",
                    'label_attr'=> array('class' => 'label_smalltext'),
                    'required' 	=> true,
                    'trim'     	=> true,
                    'attr'      => array(
                        'placeholder' => "Type d'équipement",
                        'class' => 'input_txt_large'
					)
                ))
            	->add('actif', CheckboxType::class, array (
                	'required'        => false,
                	'label'           => 'Equipement actif',
                	'label_attr'      => array ('class' => 'label_smalltext'),
                	'attr'            => array ('class' => 'checkbox')
            	));
    }
    

    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Equipement'
        ));
    }

    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_equipement';
    }
}
