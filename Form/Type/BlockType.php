<?php

namespace Msi\CmfBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class BlockType extends AbstractType
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('type', 'choice', [
                'choices' => [
                    'msi_cmf.block.text' => 'Text',
                    'msi_cmf.block.action' => 'Action',
                    'msi_cmf.block.template' => 'Template',
                    'msi_cmf.block.menu' => 'Menu',
                ],
            ])
            ->add('slot', 'choice', [
                'choices' => $this->container->getParameter('msi_cmf.block.slots')
            ])
            ->add('pages', 'entity', [
                'multiple' => true,
                'expanded' => true,
                'class' => $this->container->getParameter('msi_cmf.page.class'),
                'query_builder' => function(EntityRepository $er) {
                    return $er->createQueryBuilder('a')
                        ->leftJoin('a.translations', 't')
                        ->addSelect('t')
                        ->addOrderBy('t.title', 'ASC')
                    ;
                },
            ])
            ->add('translations', 'collection', [
                'label' => ' ',
                'type' => new BlockTranslationType($this->container),
                'options' => [
                    'label' => ' ',
                ]
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Msi\CmfBundle\Entity\Block',
        ]);
    }

    public function getName()
    {
        return 'block';
    }
}
