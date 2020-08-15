<?php

declare(strict_types=1);
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
/**
 * Description of ExamForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 * 
 */
class ExamForm extends AbstractType {
    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator)   {
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('quickcheckname', TextType::class, array('label' => $this->translator->trans('Exam Name'), 'required' => true))
            ->add('save', SubmitType::class, array('label' => $this->translator->trans('Save Exam')))
            ->add('cancel', ButtonType::class, array('label' => $this->translator->trans('Cancel')));
        
    }

    public function getBlockPrefix() : string
    {
        return 'paustianquickcheckmodule_examform';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckExamEntity',
        ));
    }
}
