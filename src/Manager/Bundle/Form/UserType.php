<?php

namespace Manager\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserType extends AbstractType
{

    private $editing = false;

    public function __construct($editing = false)
    {
        $this->editing = $editing;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('name')
            ->add('last_name');
        if(!$this->editing){
            $builder->add('password');
        } else {
            $builder->add('password', 'password',[
                'required' => false
            ]);
        }
        $builder->add('role', 'choice', array(
            'choices' => array(
                'ROLE_SUPER_ADMIN'   => 'Admin',
                'ROLE_ADMIN' => 'Manager',
            )
        ));
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Manager\Bundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'user';
    }
}
