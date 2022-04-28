<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Lci\BoilerBoxBundle\Form\Type\SiteConnexionType;
use Lci\BoilerBoxBundle\Form\Type\SiteConfigurationType;

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
                    'label'         => 'Nom',
                    'attr'		    => array (
                        'placeholder'   => 'Nom du site',
					),
                    'required'      => true,
                    'trim'          => true
				))
                ->add('affaire', TextType::class, array(
                    'label'         => 'Code',
                    'attr'		    => array (
                        'placeholder'   => "Code de l'affaire",
                        'class'         => '',
                        'max_length'    => 10
                    ),
                    'required'      => true,
                    'trim'          => true
				))
                ->add('version', TextType::class, array(
                    'label'         => 'Version de l\'ipc (ex: v3)',
                    'attr'		    => array (
                        'placeholder'   => "Version de l'ipc (ex: v3)",
                        'class'         => '',
                        'max_length'    => 10
                    ),
                    'required'      => true,
                    'trim'          => true
				))
				->add('siteConnexion', SiteConnexionType::class, array(
					'label'         => false
				))
				->add('siteConfigurations', CollectionType::class, array(
        			'entry_type'   	=> SiteConfigurationType::class,
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
