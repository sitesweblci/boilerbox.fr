<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Lci\BoilerBoxBundle\Form\Type\ConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class SiteAutresType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
                ->add('configurations', CollectionType::class, array(
                    'entry_type'    => ConfigurationType::class,
					'label' 		=> false,
                    'allow_add'     => true,
                    'allow_delete'  => true
                ))
                ->add('submit', SubmitType::class,  ['label' => 'Save', 'attr' => ['class' => 'cacher']]);
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\SiteAutres'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'lci_boilerboxbundle_siteautres';
    }


}
