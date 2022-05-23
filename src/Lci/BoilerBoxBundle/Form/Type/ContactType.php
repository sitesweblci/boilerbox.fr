<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Lci\BoilerBoxBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;





class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('siteBA', EntityType::class, array (
                'class'           => 'LciBoilerBoxBundle:SiteBA',
                'required'        => true,
                'label'           => 'Site rattaché',
                'label_attr'      => array ('class' => 'label_smalltext'),
                'choice_label'    => 'Intitule'
            ))
			->add('id', IntegerType::class, array(
				'required'		=> false,
				'mapped'		=> false,
				'label'			=> false,
				'attr'			=> array(
					'class'			=> 'cacher'
				)
			))
            ->add('nom', TextType::class, array(
                    'label'         => 'Nom',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => 'Nom',
                        'style'         => 'width:100%;'
                    )
			))
            ->add('prenom', TextType::class, array(
                    'label'         => 'Prénom',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => 'Prénom',
                        'style'         => 'width:100%;'
                    ),
                    'required'      => false
            ))
            ->add('telephone', TextType::class, array(
                    'label'         => 'Tel.',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => 'Téléphone',
                        'style'         => 'width:100%;'
                    ),
                    'required'      => false
            ))
            ->add('mail', TextType::class, array(
                    'label'         => 'Mail',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => 'eMail',
                        'style'         => 'width:100%;'
                    ),
                    'required'      => false
            ))
            ->add('fonction', TextType::class, array())
			->add('reset', ResetType::class, [
                'attr' => [
                    'class' => 'cacher'
                ]
            ])
		;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
