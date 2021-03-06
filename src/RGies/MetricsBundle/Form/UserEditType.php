<?php

namespace RGies\MetricsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserEditType extends AbstractType
{
    protected $_container;

    /**
     * Class constructor.
     */
    public function __construct($container)
    {
        $this->_container = $container;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $roles = array();
        foreach ($this->_container->getParameter('user_roles') as $key => $value) {
            if ($this->_container->get('security.context')->isGranted($key)) {
                $roles[$key] = $value['label'];
            }
        }

        $builder
            ->add('username')
            ->add('password', 'password', array('required' => false))
            ->add('firstname')
            ->add('lastname')
            ->add('jobtitle', 'text', array('required' => false))
            ->add('role', 'choice', array('choices' => $roles, 'label' => 'Access level'))
            ->add('email', 'email')
            ->add('is_active')
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'RGies\MetricsBundle\Entity\User'
        ));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'rgies_MetricsBundle_user';
    }
}
