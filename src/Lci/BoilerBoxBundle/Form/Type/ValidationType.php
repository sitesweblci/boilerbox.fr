<?php
namespace Lci\BoilerBoxBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;



class ValidationType extends AbstractType {

	public function buildForm(FormBuilderInterface $builder, array $option) {
		$builder->add('valide', CheckboxType::class, array(
					'label'         => false,
					'label_attr'        => array('class' => 'label_smalltext'),
					'attr'          => array(
                        'class'             => 'input_checkbox'
                    )
				));
	}



    /*
     * @param OptionsResolver $resolver
    */
    public function configureOptions(OptionsResolver $resolver){
        $resolver->setDefaults(array(
            'data_class' => 'Lci\BoilerBoxBundle\Entity\Validation'
        ));
    }


    /**
     * @return string
    */
    public function getName(){
        return 'lci_boilerboxbundle_validation';
    }


}

