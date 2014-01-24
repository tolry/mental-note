<?php
namespace Olry\MentalNoteBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

use Olry\MentalNoteBundle\Entity\Entry;
use Olry\MentalNoteBundle\Entity\Category;

/**
 *
 * @author Tobias Olry (tobias.olry@web.de)
 */
class EntryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('category', 'choice',
                array(
                    'expanded' => true,
                    'label' => ' ',
                    'choices' => Category::getChoiceArray(),
                    'error_bubbling' => true
                ))
            ->add('url', 'text', array('label' => 'url', 'attr' => array('class' => 'input-large', 'focus' => 'focus')))
            ->add('title', 'text', array('label' => 'title', 'attr' => array('class' => 'input-large')))
            ->add('tagsString', 'text', array('label' => 'Tags', 'attr' => array('class' => 'input-large')));
    }

    public function getName()
    {
        return 'entry';
    }
}
