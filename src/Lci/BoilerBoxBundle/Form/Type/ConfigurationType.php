<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class ConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('parametre', TextType::class, array(
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
				->add('submit', SubmitType::class,  ['label' => 'Enregistrer']);
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
