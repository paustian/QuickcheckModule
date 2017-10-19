<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
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
     * @param LocaleApiInterface $localeApi
     */
    public function __construct(
        TranslatorInterface $translator)   {
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quickcheckname', \Symfony\Component\Form\Extension\Core\Type\TextType::class, array('label' => $this->translator->__('Exam Name'), 'required' => true))
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => $this->translator->__('Save Exam')))
            ->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' => $this->translator->__('Cancel')));
        
    }

    public function getBlockPrefix()
    {
        return 'paustianquickcheckmodule_examform';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolver $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckExamEntity',
        ));
    }
}
