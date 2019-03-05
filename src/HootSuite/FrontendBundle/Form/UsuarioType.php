<?php

namespace HootSuite\FrontendBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsuarioType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nombre', 'text', array('label'=> 'Nombree'))
            ->add('apellidos', 'text', array('label'=> 'Apellidos'))
            ->add('telefono', 'text', array('label'=> 'Teléfono'))
            ->add('email', 'text', array('label'=> 'Correo'))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => true,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HootSuite\FrontendBundle\Entity\Usuario'
        ));
    }

    public function getName()
    {
        return 'hootsuite_frontend_usuariotype';
    }
}
