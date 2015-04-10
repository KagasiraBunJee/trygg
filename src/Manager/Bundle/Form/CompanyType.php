<?php

namespace Manager\Bundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CompanyType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $date = new \DateTime("now");
        $builder
            ->add('name')
            ->add('orgCode')
            ->add('address')
            ->add('contact')
            ->add('phone')
            ->add('image','file', [
                "required" => false,
                'data_class' => null
            ])
            ->add('comment','textarea', [
                'required' => false
            ])
            ->add('saleDate','date',[
                'format' => 'yyyy-MM-dd',
                'input' => 'datetime',
                'widget' => 'single_text',
            ])
            ->add('postalCode')
            ->add('price')
            ->add('product')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Manager\Bundle\Entity\Company'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'company';
    }
}
