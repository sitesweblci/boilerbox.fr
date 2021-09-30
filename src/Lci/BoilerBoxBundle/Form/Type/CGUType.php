<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Lci\BoilerBoxBundle\Entity\CGU;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;

class CGUType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('version', TextType::class, array(
                'label'         => false,
                'attr'          => array (
                    'placeholder'   => 'Numéro de version',
                ),
                'required'      => true,
                'trim'          => true
            ))
            ->add('file', FileType::class, array(
                'label' 		=> ' ',
                'label_attr'	=> array('class' => 'label_register')
            ))
            ->add('cguObligatoire', ChoiceType::class, [
                'label'			=> 'Obliger l\'acceptation des CGU?',
                'expanded'		=> true,
                'choices'		=> array(
                    'Non' 			=> false,
                    'Oui' 	=> true,
                ),
                'data' => true,
            ])
            ->add('cguCourant', ChoiceType::class, [
                'label'	   => 'Les CGU deviennent-elles les CGU courantes?',
                'expanded' => true,
                'choices'  => array(
                    'Non' => false,
                    'Oui' => true,
                ),
                'attr' => array(
                    'readonly' => true,
                ),
                'data' => true,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(array(
            'data_class' => CGU::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'lci_boilerboxbundle_cgu';
    }


}
