<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Lci\BoilerBoxBundle\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;




class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, array(
                    'label'         => 'Nom',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => 'Nom',
                        'style'         => 'width:100%;'
                    ),
                    'required'      => false
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
        	->add('date_maj', DateType::class, array(
        	    'label'         => 'Mise à jour des informations',
        	    'label_attr'    => array ('class' => 'label_smalltext'),
        	    'widget'        => 'single_text',
        	    'html5'         => false,
        	    'format'        => 'dd/MM/yyyy',
        	    'invalid_message' => 'Format de la date incorrect.',
        	    'attr'          => array(
        	        'class'         => 'smallinput',
        	        'placeholder'   => 'dd/mm/YYYY',
        	        'maxlength'     => 10
        	    )
        	));
/*
            ->add('siteBA', EntityType::class, array (
                'class'           => 'LciBoilerBoxBundle:SiteBA',
                'required'        => true,
                'label'           => 'Site rattaché',
                'label_attr'      => array ('class' => 'label_smalltext'),
                'choice_label'    => 'Intitule'
            ));
*/
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
