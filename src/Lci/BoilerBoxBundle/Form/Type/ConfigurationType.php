<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;



class ConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('id', IntegerType::class, array(
				'required'	=> false,
				'label'		=> false,
				'attr'		=> array(
					'class'		=> 'cacher'
				)
			))
            ->add('type', ChoiceType::class, array(
                    'label' => 'Type du paramètre',
                    'choices' => [
						'Global'	=> 'global',
                        'Infos'     => 'infos',
                        'Connexion' => 'connexion',
                        'Autre'     => 'autre'
                    ],
                    'required'      => true,
            ))
			->add('parametre', TextType::class, array(
                    'label'         => 'Paramètre',
                    'attr'          => array (
                        'placeholder'   => 'Nom du paramètre',
                    ),
                    'required'      => true,
                    'trim'          => true
            ))
			->add('valeur', TextType::class, array(
                    'label'         => 'Valeur',
                    'attr'          => array (
                        'placeholder'   => 'Valeur',
                    ),
                    'required'      => false,
                    'trim'          => true
            ))
			->add('submit', SubmitType::class,  array(
				'label' => 'Enregistrer', 
				'attr'	=> ['class' => 'btn btn--main']
			));
    }
	
	/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Configuration'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lci_boilerboxbundle_configuration';
    }


}
