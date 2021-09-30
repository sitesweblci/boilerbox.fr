<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

use Lci\BoilerBoxBundle\Form\Type\ConfigurationType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/* Pour accéder à distance il faut que i
accesdistant soit ok				-> Alors au minimum l'accès depuis eCatch est possible
Si en plus accesboilerbox est ok	-> alors l'accès à distance peut également être fait depuis le site boiler-box.fr

// ! Si seul l'acces à boilerbox est ok alors l'accès n'est pas considéré comme ok, il faut absoluement que le parametre accesdistant soit aussi défini à ok.
*/


class SiteType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
	    $builder
				->add('intitule', TextType::class, array(
                    'label'         => 'Intitulé',
                    'attr'		    => array (
                        'placeholder'   => 'Intitulé du site',
                        'class'         => 'input_txt_large',
                        'max_length'    => 20
					),
                    'required'      => true,
                    'trim'          => true
				))
                ->add('affaire', TextType::class, array(
                    'label'         => 'Code affaire',
                    'attr'		    => array (
                        'placeholder'   => 'Code affaire',
                        'class'         => 'input_txt_large',
                        'max_length'    => 10
                    ),
                    'required'      => true,
                    'trim'          => true
				))
                ->add('url', TextType::class, array(
                    'label'         => 'Url',
                    'attr'		    => array (
                        'placeholder'   => 'URL',
                        'class'         => 'input_txt_large',
                        'max_length'    => 255
                    ),
                    'required'      => true,
                    'trim'          => true
				))
				->add('accesDistant', ChoiceType::class, array(
					'label'			=> 'Accès à distance possible (au minimum par ecatcher)',	
					'multiple'		=> true,
					'expanded'		=> true,
					'choices'		=> array(
						'Non' 			=> 'non',
						'BoilerBox.fr' 	=> 'boilerbox',
						'Ecatcher' 		=> 'ecatcher',
						'Cloud'			=> 'cloud',
						'Live'			=> 'live'
					),
					'attr'          => array('class' => 'radio_smalltext')
				))
				->add('typeConnexion', ChoiceType::class, array(
                    'label'         => "Connexion vers l'ipc distant",
                    'multiple'      => true,
                    'expanded'      => true,
                    'choices'       => array(
                        '3G'            => '3g',
                        '4G'            => '4g',
                        'Adsl'          => 'adsl'
                    ),
                    'attr'          => array('class' => 'radio_smalltext')
                ))
               ->add('codeLive', TextType::class, array(
                    'label'         => 'Code du live',
                    'attr'          => array (
                        'placeholder'   => 'Code du Live',
                        'class'         => 'input_txt_large',
                        'max_length'    => 255
                    ),
                    'required'      => false,
                    'trim'          => true
                ))
				->add('configurations', CollectionType::class, array(
        			'entry_type'   	=> ConfigurationType::class,
					'label' 		=> false,
        			'allow_add'    	=> true,
        			'allow_delete' 	=> true
      			))
				->add('submit', SubmitType::class,  ['label' => 'Save', 'attr' => ['class' => 'cacher']]);
    }

    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Site'
        ));
    }


    /**
     * @return string
     */
    public function getName(){
        return 'lci_boilerboxbundle_site';
    }
}
