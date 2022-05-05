<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;


use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Lci\BoilerBoxBundle\Entity\Configuration;



class SuppressionConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('configuration', EntityType::class, array(
				'class'    		=> 'LciBoilerBoxBundle:Configuration',
				'label'			=> 'Paramètre à supprimer',
				'query_builder' => function(EntityRepository $er)
                {
                    return $er->createQueryBuilder('c')
                            ->orderBy('c.parametre', 'ASC');
                },
                'choice_label'      => 'parametre',
				'mapped'			=> false
			))
			->add('submit', SubmitType::class,  array(
                'label' => 'Supprimer',
                'attr'  => ['class' => 'btn btn--delete']
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
