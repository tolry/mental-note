<?php
/**
 * @author Tobias Olry (tobias.olry@web.de)
 */

namespace Olry\MentalNoteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

use Doctrine\ORM\EntityManager;
use Olry\MentalNoteBundle\Entity\Category;
use Olry\MentalNoteBundle\Form\Transformer\EntryTagTransformer;

class EntryType extends AbstractType
{

    private $em;
    private $user;

    public function __construct(EntityManager $em, $user)
    {
        $this->em = $em;
        $this->user = $user;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $repo = $this->em->getRepository('Olry\MentalNoteBundle\Entity\Tag');

        $tagNames = [];
        foreach ($repo->search('', $this->user)->getQuery()->getResult() as $tag) {
            $tagNames[$tag->getName()] = $tag->getName();
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
                        'choice',
                        [
                            'choices' => $tagNames,
                            'required' => false,
                            'label' => 'Tags',
                            'multiple' => true,
                            'expanded' => false,
                        ]
                    )
                    ->addModelTransformer(new EntryTagTransformer($repo))
            )
        ;

        // add preBindListener in order to accept all given input
        $builder->addEventListener(FormEvents::PRE_BIND, function (FormEvent $e) {
            $form = $e->getForm();
            $data = $e->getData();
            $builder = $form['tags']->getConfig();
            $allowedTags = $builder->getOption('choices');

            foreach ($data['tags'] as $tag) {
                if (! isset($allowedTags[$tag])) {
                    $allowedTags[$tag] = 1;
                }
            }

            $builder->setOption('choices', $allowedTags);
        });
    }

    public function getName()
    {
        return 'entry';
    }
}
