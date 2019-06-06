<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
/**
 * Description of CategorizeForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 * 
 */
class ExportForm extends AbstractType {
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('export', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => $this->translator->__('Export')))
            ->add('exportall', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => $this->translator->__('Export All')));
    }

    public function getPrefixName()
    {
        return 'paustianquickcheckmodule_exportform';
    }
}
