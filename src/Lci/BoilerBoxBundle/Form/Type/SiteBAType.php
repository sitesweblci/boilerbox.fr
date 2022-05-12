<?php

namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;


use Lci\BoilerBoxBundle\Entity\Contact;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;





class SiteBAType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options) {
	    $builder
				->add('intitule', TextType::class, array(
                    'label'         => 'Nom du site',
					'label_attr'    => array (
						'class' 		=> 'label_smalltext',
					),
					'attr'          => array(
                		'class'         => 'biginput centrer',
                		'placeholder'   => 'Nom du site',
						'style'         => 'width:100%'
            		),
                    'required'      => true,
                    'trim'          => true
				))
				->add('adresse', TextType::class, array(
                    'label'         => 'Adresse',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => "Adresse",
						'style'         => 'width:100%;'
                    ),
					'required'      => false
                ))
                ->add('lienGoogle', TextType::class, array(
                    'label'         => 'Url de Google map',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext',
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => "Copier ici l'url retournée par Google Map",
                        'style'         => 'width:100%'
                    ),
                    'required'      => false
                ))
				->add('informationsClient', TextareaType::class, array(
                    'label'         => 'Informations',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
						'rows'			=> 7,
                        'class'         => 'biginput centrer',
                        'placeholder'   => "Informations complémentaires",
						'style'         => 'width:100%; resize:none'
                    ),
					'required'      => false
                ))
        		->add('fichiersJoint', CollectionType::class, array(
            		'entry_type'    => FichierSiteBAType::class,
					/* Option à ajouter pour résoudre l'erreur -> Warning: spl_object_hash() expects parameter 1 to be object, array given */
					'entry_options'	=> array('data_class' => 'Lci\BoilerBoxBundle\Entity\FichierSiteBA'),
            		'label'         => 'Fichier(s) joints au site',
            		'label_attr'    => array ('class' => 'label_smalltext'),
            		'allow_add'     => true,
            		'allow_delete'  => true,
            		'required'      => true
        		))
				->add('contacts', CollectionType::class, array(
                    'entry_type'    => ContactType::class,
                    'label'         => 'Contact(s) Client',
					'allow_add'     => true,
                    'allow_delete'  => true,
					'required'      => true,
					'mapped'		=> false
                ))
				->add('cheminDossierPhotos', TextType::class, array(
                    'label'         => 'Chemin local vers le repertoires des photos',
                    'label_attr'    => array(
                        'class'         => 'label_smalltext'
                    ),
                    'attr'          => array(
                        'class'         => 'biginput centrer',
                        'placeholder'   => "Chemin vers le repertoire local des photos",
                        'style'         => 'width:100%;'
                    ),
                    'required'      => false
                ))
				->add('reset', ResetType::class);
    }

    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\SiteBA'
        ));
    }


    /**
     * @return string
     */
    public function getName(){
        return 'lci_boilerboxbundle_site_ba';
    }
}
