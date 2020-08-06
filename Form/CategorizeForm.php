<?php

declare(strict_types=1);
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\Common\Translator\TranslatorInterface;
/**
 * Description of CategorizeForm
 * Set up the elements for a Exam form. A simple forms
 *
 * @author paustian
 * 
 */
class CategorizeForm extends AbstractType {

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

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => $this->translator->__('Recategorize')))
            ->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' => $this->translator->__('Cancel')));
        
        
        $builder->add('categories', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);
    }

    public function getPrefixName() : string
    {
        return 'paustianquickcheckmodule_categorizeform';
    }
}
