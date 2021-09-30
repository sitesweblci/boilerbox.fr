<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Lci\BoilerBoxBundle\Form\Type\FichierJointType;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Lci\BoilerBoxBundle\Entity\UserRepository;
use Lci\BoilerBoxBundle\Entity\EquipementRepository;
use Lci\BoilerBoxBundle\Entity\ModuleRepository;


use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class ProblemeTechniqueType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
    */
    public function buildForm(FormBuilderInterface $builder, array $options) {
		$builder->add('id', IntegerType::class, array(
					'label_attr'	  => array('class' => 'identifiant cacher'),
					'attr'      	  => array('class' => 'identifiant cacher')
				))
				->add('module', EntityType::class, array(
					'label' 		  => 'Module(s)',
					'label_attr'	  => array('class' => 'label_smalltext'),
					'attr'			  => array('class' => 'input_select'),
					'class' 		  => 'LciBoilerBoxBundle:Module',
					'choice_label'	  => 'infoSelect',
					'multiple'		  => true,
					'required'		  => true,
					'query_builder'   => function (ModuleRepository $mr) {
                    	return $mr->createQueryBuilder('m')->where('m.actif = true')->orderBy('m.numero', 'ASC');
                    }
				))
                ->add('equipement', EntityType::class, array(
					'label'			  => "Equipement",
					'label_attr'	  => array('class' => 'label_smalltext'),
					'attr'      	  => array('class' => 'input_select'),
					'class' 		  => 'LciBoilerBoxBundle:Equipement',
					'choice_label'	  => 'type',
					'required'		  => true,
					'query_builder'   => function (EquipementRepository $er) {
                    	return $er->createQueryBuilder('e')->where('e.actif = true')->orderBy('e.type', 'ASC');
                    }
				))
				->add('bloquant', CheckboxType::class, array(
						'label'		  => 'Problème bloquant',
						'label_attr'  => array('class' => 'label_smalltext'),
						'attr'			  => array(
							'class'			=> 'input_checkbox'
						),
                      	'required'    => false
				))
				->add('dateSignalement', DateType::class, array(
					'label' 		  => 'Date de signalement',
					'label_attr'	  => array('class' => 'label_date'),
					'attr'      	  => array(
						'class' 		=> 'input_date',
						'placeholder' 	=> 'dd-mm-yyyy'
					),
					'format'		  => 'dd-MM-yyyy',
					'invalid_message' => 'Format de la date incorrect.',
					'widget' 		  => 'single_text'
				))
				 ->add('user', EntityType::class, array(
                    'label'     	  => 'Opérateur désigné',
                    'label_attr'	  => array('class' => 'label_smalltext'),
                    'attr'      	  => array('class' => 'input_select'),
					'class'     	  => 'LciBoilerBoxBundle:User',
					'choice_label' 	  => 'username',
					'query_builder'   => function (UserRepository $ur) {
                    	return $ur->createQueryBuilder('u')->orderBy('u.username', 'ASC');
                    }
                ))
				->add('description', TextareaType::class, array(
                    'label'     	  => 'Description du problème',
					'label_attr'	  => array('class' => 'label_smalltext'),
					'attr'      	  => array(
						'class' 		=> 'frm_texte_box',
						'placeholder'	=> 'Description du problème'
					)
                ))
				->add('solution', TextareaType::class, array(
					'label'     	  => 'Solution retenue',
					'label_attr'	  => array('class' => 'label_smalltext'),
					'attr'      	  => array(
						'class' 		=> 'frm_texte_box',
						'placeholder'	=> 'Solution retenue'
					),
					'required' 		  => false,
				))
				->add('corrige', CheckboxType::class, array(
					'label'			  => 'Problème corrigé',
					'label_attr'	  => array('class' => 'label_smalltext'),
					'attr'			  => array(
						'class'			=> 'input_checkbox'
					),
					'required' 		  => false
				))
				->add('dateCorrection', DateType::class, array(
					'label'     	  => 'Date de résolution',
					'label_attr'	  => array('class' => 'label_date'),
					'attr'      	  => array(
						'class' 		=> 'input_date',
						'placeholder' 	=> 'dd-mm-yyyy'
					),
					'format'    	  => 'dd-MM-yyyy',
					'widget'    	  => 'single_text',
					'invalid_message' => 'Format de la date incorrect.',
					'required' 		  => false
				))
				->add('cloture', CheckboxType::class, array(
					'label'			  => 'Problème clos',
					'label_attr'	  => array('class' => 'label_smalltext'),
					'attr'			  => array(
						'class'			=> 'input_checkbox'
					),
					'required' 		  => false
				))
				->add('dateCloture', DateType::class, array(
					'label'			  => 'Date de clôture',
					'label_attr'	  => array('class' => 'label_date'),
					'attr'      	  => array(
						'class' 		=> 'input_date',
						'placeholder' 	=> 'dd-mm-yyyy'
					),
					'format'    	  => 'dd-MM-yyyy',
					'widget'    	  => 'single_text',
					'invalid_message' => 'Format de la date incorrect.',
					'required'  	  => false
				))
				->add('fichiersJoint', CollectionType::class, array(
					'label'			=> 'Fichier(s) joint(s)',
					'label_attr'    => array('class' => 'label_smalltext'),
					'entry_type'	=> FichierJointType::class,
					'entry_options' => array('data_class' => 'Lci\BoilerBoxBundle\Entity\FichierJoint'),
					'allow_add'		=> true,
					'allow_delete'	=> true
				))
				;
    }
    

    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\ProblemeTechnique'
        ));
    }




    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_problemeTechnique';
    }
}
