<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
/**
 * Description of ExamForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 * 
 */
class ExamForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quickcheckname', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array('label' => __('Exam Name'), 'required' => true))
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Save Exam'))
            ->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' => __('Cancel')));
        
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_examform';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckExamEntity',
        ));
    }
}
