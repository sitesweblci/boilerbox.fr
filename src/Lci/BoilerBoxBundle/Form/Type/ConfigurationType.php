<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class ConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('parametre', TextType::class, array(
                    'label'         => false,
                    'attr'          => array (
                        'placeholder'   => 'Nom du paramètre',
                    ),
                    'required'      => true,
                    'trim'          => true
                ))
				->add('valeur', TextType::class, array(
                    'label'         => false,
                    'attr'          => array (
                        'placeholder'   => 'Valeur',
                    ),
                    'required'      => true,
                    'trim'          => true
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
