<?php

namespace Msi\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class BlockTranslationType extends AbstractType
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('published', 'checkbox')
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($builder){
            $form = $event->getForm();
            $data = $event->getData();

            if ($data instanceof \Msi\AdminBundle\Entity\BlockTranslation) {
                $typeId = $data->getObject()->getType();
                $blockHandler = $this->container->get($typeId);
                $builder2 = $this->container->get('form.factory')->createBuilder();
                $blockHandler->buildTranslationForm($builder2);
                $type = (new DynamicType('block_translation_settings'))->setBuilder($builder2);
                if ($builder2->all()) {
                    $form->add($builder->getFormFactory()->createNamed('settings', $type));
                }
            }
        });
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Msi\AdminBundle\Entity\BlockTranslation',
        ]);
    }

    public function getName()
    {
        return 'block_translation';
    }
}
