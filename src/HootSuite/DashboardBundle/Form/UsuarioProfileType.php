<?php

namespace HootSuite\DashboardBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UsuarioProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array( 'attr' => array( 'class' => 'form-control', 'placeholder' => 'Nombre y Apellidos' )))
            ->add('email', 'text', array( 'attr' => array( 'class' => 'form-control', 'placeholder' => 'Correo Electrónico' )))
            ->add('phone', 'text', array( 'attr' => array( 'class' => 'form-control', 'placeholder' => 'Teléfono' )))
            ->add('description', 'text', array( 'attr' => array( 'class' => 'form-control', 'placeholder' => 'Descripción' )))
            ->add('password','password',array(
                'required' => true,
                'invalid_message'   => 'La contraseña no es válida',
                'attr' => array( 'class' => 'form-control', 'placeholder' => 'Contraseña' )
                ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'HootSuite\BackofficeBundle\Entity\Usuario'
        ));
    }

    public function getName()
    {
        return 'usuarioprofiletype';
    }
}
