<?php
/**
 * @author Tobias Olry (tobias.olry@web.de)
 */

namespace Olry\MentalNoteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Doctrine\ORM\EntityManager;

use Olry\MentalNoteBundle\Entity\Entry;
use Olry\MentalNoteBundle\Entity\Category;

use Olry\MentalNoteBundle\Form\Transformer\EntryTagTransformer;

class EntryType extends AbstractType
{
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', 'choice',
                array(
                    'expanded'       => true,
                    'label'          => ' ',
                    'choices'        => Category::getChoiceArray(),
                    'error_bubbling' => true
                ))
            ->add('url', 'text', array('label' => 'url', 'attr' => array('class' => 'input-large', 'focus' => 'focus')))
            ->add('title', 'text', array('label' => 'title', 'attr' => array('class' => 'input-large')))
            ->add(
                $builder
                    ->create('tags', 'text', array('label' => 'Tags', 'attr' => array('class' => 'input-large')))
                    ->addModelTransformer(new EntryTagTransformer($this->em->getRepository('Olry\MentalNoteBundle\Entity\Tag')))
                )
        ;
    }

    public function getName()
    {
        return 'entry';
    }
}
