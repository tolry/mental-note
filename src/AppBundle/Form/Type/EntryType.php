<?php

declare(strict_types = 1);
/**
 * @author Tobias Olry (tobias.olry@web.de)
 */

namespace AppBundle\Form\Type;

use AppBundle\Entity\Category;
use AppBundle\Form\Transformer\EntryTagTransformer;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as CoreType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{
    private $entityManager;
    private $user;
    private $router;

    public function __construct(EntityManager $entityManager, $token, $router)
    {
        $this->entityManager = $entityManager;
        $this->user = $token->getToken()->getUser();
        $this->router = $router;
    }

    public function configureOptions(OptionsResolver $options): void
    {
        $options->setDefaults([
            'url-readonly' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $repo = $this->entityManager->getRepository('AppBundle\Entity\Tag');

        $allowedTags = [];
        foreach ($repo->search('', $this->user)->getQuery()->getResult() as $tag) {
            if (trim($tag->getName()) === '') {
                continue;
            }
            $allowedTags[] = $tag->getName();
        }

        $builder
            ->add(
                'category',
                CoreType\ChoiceType::class,
                [
                    'expanded' => true,
                    'choices' => Category::getChoiceArray(),
                    'error_bubbling' => true,
                ]
            )
            ->add(
                'url',
                CoreType\TextType::class,
                [
                    'label' => 'url',
                    'attr' => [
                        'class' => 'input-large',
                        'readonly' => $options['url-readonly'] ? true : false,
                        'data-metainfo-url' => $this->router->generate('url_metainfo'),
                    ],
                ]
            )
            ->add('title', CoreType\TextType::class, ['label' => 'title', 'attr' => ['class' => 'input-large']])
            ->add(
                $builder
                    ->create(
                        'tags',
                        CoreType\TextType::class,
                        [
                            'required' => false,
                            'label' => 'Tags',
                            'attr' => [
                                'class' => 'awesomplete',
                                'data-list' => implode(', ', $allowedTags),
                                'data-multiple' => 1,
                                'data-minchars' => 1,
                            ],
                        ]
                    )
                    ->addModelTransformer(new EntryTagTransformer($repo))
            )
            ->add('pending', CoreType\ChoiceType::class, [
                    'expanded' => true,
                    'choices' => [
                        'pending' => true,
                        'archived' => false,
                    ],
                    'error_bubbling' => true,
                ])
        ;
    }

    public function getName(): string
    {
        return 'entry';
    }
}
