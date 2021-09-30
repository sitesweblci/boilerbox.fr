<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Lci\BoilerBoxBundle\Entity\UserRepository;
use Lci\BoilerBoxBundle\Entity\EquipementRepository;
use Lci\BoilerBoxBundle\Entity\ModuleRepository;

use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Lci\BoilerBoxBundle\Entity\User;
use Lci\BoilerBoxBundle\Entity\Module;
use Lci\BoilerBoxBundle\Entity\Equipement;


class ObjRechercheProblemeType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('dateDebut', DateType::class, array(
					'label' 	        => 'Date de début',
					'label_attr'	    => array('class' => 'label_date'),
					'attr'      	    => array(
						'class' 		    => 'input_date',
						'placeholder' 	    => 'dd-mm-yyyy'
					),
					'format'	        => 'dd-MM-yyyy',
					'invalid_message'   => 'Format incorrect',
					'widget'            => 'single_text'
				))
                ->add('dateFin', DateType::class, array(
					'label'		        => 'Date de fin',
					'label_attr'	    => array('class' => 'label_date'),
					'attr'      	    => array(
						'class' 		    => 'input_date',
						'placeholder' 	    => 'dd-mm-yyyy'
					),
					'format'	        => 'dd-MM-yyyy',
					'invalid_message'   => 'Format incorrect',
					'widget'            => 'single_text'
				))
				->add('intervenant', EntityType::class, array(
                    'class'             => User::class,
					'label' 	        => 'Opérateur',
					'label_attr'        => array('class' => 'label_smalltext'),
					'attr'              => array('class' => 'input_txt'),
					'choice_label'      => 'username',
					'mapped' 	        => false,
					'query_builder'     => function (UserRepository $ur) {
                        return $ur->createQueryBuilder('u')->orderBy('u.username', 'ASC');
                    }
				))
				->add('module', EntityType::class, array(
                    'class'             => Module::class,
                    'label'             => 'Module',
                    'label_attr'        => array('class' => 'label_smalltext'),
                    'attr'              => array('class' => 'input_txt'),
                    'choice_label'      => 'infoSelect',
					'mapped'            => false,
					'query_builder'     => function (ModuleRepository $mr) {
                        return $mr->createQueryBuilder('m')->where('m.actif = true')->orderBy('m.numero', 'ASC');
                    }
				))
				->add('equipement', EntityType::class, array(
                    'class'             => Equipement::class,
                    'label'             => "Type d'équipement",
                    'label_attr'        => array('class' => 'label_smalltext'),
                    'attr'              => array('class' => 'input_txt'),
                    'choice_label'      => 'type',
                    'mapped'            => false,
                    'query_builder'     => function (EquipementRepository $er) {
                        return $er->createQueryBuilder('e')->orderBy('e.type', 'ASC');
                    }
                ))
				->add('reference', NumberType::class, array(
					'label'		        => 'Référence',
					'label_attr'        => array('class' => 'label_smalltext'),
					'invalid_message'   => 'Valeur incorrecte',
					'attr'              => array('class' => 'input_date')
				))
				->add('type', TextType::class, array(
					'label'             => 'Type',
                    'label_attr'        => array('class' => 'label_smalltext'),
                    'invalid_message'   => 'Valeur incorrecte',
                    'attr'              => array('class' => 'input_date')
                ))
				->add('chk_intervenant', CheckboxType::class, array(
                    'label'             => 'Opérateur',
					'label_attr'        => array('class' => 'label_smalltext'),
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
                    'mapped'            => false
                ))
                ->add('chk_module', CheckboxType::class, array(
                    'label'             => 'Module',
					'label_attr'        => array('class' => 'label_smalltext'),
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
                    'mapped'            => false
                ))
				->add('chk_equipement', CheckboxType::class, array(
                    'label'             => 'Equipement',
					'label_attr'        => array('class' => 'label_smalltext'),
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
                    'mapped'            => false
                ))
				->add('corrige', CheckboxType::class, array(
					'label'             => 'Problèmes corrigés',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
					'label_attr'        => array('class' => 'label_smalltext')
				))
                ->add('nonCorrige', CheckboxType::class, array(
                    'label'             => 'Problèmes non corrigés',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
                    'label_attr'        => array('class' => 'label_smalltext')
                ))
				->add('cloture', CheckboxType::class, array(
					'label'             => 'Problèmes clos',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
					'label_attr'        => array('class' => 'label_smalltext')
				))
                ->add('nonCloture', CheckboxType::class, array(
                    'label'             => 'Problèmes non clos',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
                    'label_attr'        => array('class' => 'label_smalltext')
                ))
				->add('bloquant', CheckboxType::class, array(
					'label'             => 'Problèmes bloquants',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
					'label_attr'        => array('class' => 'label_smalltext')
				))
                ->add('nonBloquant', CheckboxType::class, array(
                    'label'             => 'Problèmes non bloquants',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
                    'label_attr'        => array('class' => 'label_smalltext')
                ))
				->add('present', CheckboxType::class, array(
					'label'             => 'Modules présents',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
					'label_attr'        => array('class' => 'label_smalltext')
				))
                ->add('nonPresent', CheckboxType::class, array(
                    'label'             => 'Modules non présents',
					'attr'			    => array(
						'class'			    => 'input_checkbox'
					),
                    'label_attr'        => array('class' => 'label_smalltext')
                ))
				;
    }
    


    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\ObjRechercheProbleme'
        ));
    }


    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_ObjRechercheProbleme';
    }
}
