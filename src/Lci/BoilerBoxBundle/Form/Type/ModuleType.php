<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;


class ModuleType extends AbstractType
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
			->add('numero', IntegerType::class, array (
                'required' 	      => true,
                'label'   	      => 'Numéro du module',
                'label_attr'      => array ('class' => 'label_smalltext'),
                'attr'            => array (
                    'min'           => 0,
                    'placeholder'   => 'Numéro du module',
                    'class'         => 'input_txt_large'
                )
			))
			->add('nom', TextType::class, array (
                'label'	          => 'Nom du module',
				'label_attr'      => array ('class' => 'label_smalltext'),
				'attr'		      => array (
                    'placeholder'   => 'Nom',
                    'class'         => 'input_txt_large'
                )	
			))
            ->add('type', TextType::class, array (
                'label'           => 'Type',
                'label_attr'      => array ('class' => 'label_smalltext'),
                'attr'            => array (
                    'placeholder'   => 'Type',
                    'class'         => 'input_txt_large'
                )
            ))
			->add('present', CheckboxType::class, array (
                'required'        => false,
				'label'           => 'Module présent',
                'label_attr'      => array ('class' => 'label_smalltext'),
                'attr'            => array ('class' => 'checkbox')
			))
			->add('site', EntityType::class, array (
				'class'			  => 'LciBoilerBoxBundle:Site',
				'required'		  => false,
				'label'           => 'Destination',
				'label_attr'      => array ('class' => 'label_smalltext'),
				'choice_label'	  => 'affaire'
			))
            ->add('actif', CheckboxType::class, array (
                'required'        => false,
                'label'           => 'Module actif',
                'label_attr'      => array ('class' => 'label_smalltext'),
                'attr'            => array ('class' => 'checkbox')
            ))
            ->add('dateMouvement', DateType::class, array (
                'label'           => 'Depuis le ',
                'label_attr'      => array ('class' => 'label_date'),
                'attr'            => array (
                    'class'         => 'input_date',
                    'paceholder'    => 'dd-mm-yyyy'
                ),
                'format'          => 'dd-MM-yyyy',
                'invalid_message'   => 'Format de la date incorrect.',
                'widget'            => 'single_text'
            ));
    }


    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Module'
        ));
    }

    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_module';
    }
}
