<?php
/**
 * @author Tobias Olry (tobias.olry@web.de)
 */

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type as CoreType;

use Doctrine\ORM\EntityManager;
use AppBundle\Entity\Category;
use AppBundle\Form\Transformer\EntryTagTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntryType extends AbstractType
{

    private $em;
    private $user;
    private $router;

    public function __construct(EntityManager $em, $token, $router)
    {
        $this->em = $em;
        $this->user = $token->getToken()->getUser();
        $this->router = $router;
    }

    public function configureOptions(OptionsResolver $options)
    {
        $options->setDefaults([
            'url-readonly' => false,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $repo = $this->em->getRepository('AppBundle\Entity\Tag');

        $allowedTags = [];
        foreach ($repo->search("", $this->user)->getQuery()->getResult() as $tag) {
            if (trim($tag->getName()) == '') {
                continue;
            }
            $allowedTags[] = $tag->getName();
        }


        $builder
            ->add(
                'category',
                CoreType\ChoiceType::class,
                array(
                    'expanded'       => true,
                    'choices'        => Category::getChoiceArray(),
                    'error_bubbling' => true,
                )
            )
            ->add(
                'url',
                CoreType\TextType::class,
                array(
                    'label' => 'url',
                    'attr' => array(
                        'class' => 'input-large',
                        'readonly' => $options['url-readonly'] ? true : false,
                        'data-metainfo-url' => $this->router->generate('url_metainfo')
                    )
                )
            )
            ->add('title', CoreType\TextType::class, array('label' => 'title', 'attr' => array('class' => 'input-large')))
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
                            ]
                        ]
                    )
                    ->addModelTransformer(new EntryTagTransformer($repo))
            )
        ;
    }

    public function getName()
    {
        return 'entry';
    }
}
