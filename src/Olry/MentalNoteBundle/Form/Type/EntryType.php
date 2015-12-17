<?php
/**
 * @author Tobias Olry (tobias.olry@web.de)
 */

namespace Olry\MentalNoteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Doctrine\ORM\EntityManager;
use Olry\MentalNoteBundle\Entity\Category;
use Olry\MentalNoteBundle\Form\Transformer\EntryTagTransformer;

class EntryType extends AbstractType
{

    private $em;

    public function __construct(EntityManager $em, $user)
    {
        $this->em = $em;
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $repo = $this->em->getRepository('Olry\MentalNoteBundle\Entity\Tag');

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
                'choice',
                array(
                    'expanded'       => true,
                    'choices'        => Category::getChoiceArray(),
                    'error_bubbling' => true
                )
            )
            ->add('url', 'text', array('label' => 'url', 'attr' => array('class' => 'input-large', 'focus' => 'focus')))
            ->add('title', 'text', array('label' => 'title', 'attr' => array('class' => 'input-large')))
            ->add(
                $builder
                    ->create(
                        'tags',
                        'text',
                        [
                            'datalist' => $allowedTags,
                            'required' => false,
                            'label' => 'Tags',
                            'attr' => [
                                'class' => 'awesomplete',
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
