<?php

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Zikula\Common\Translator\TranslatorInterface;
use Paustian\QuickcheckModule\Controller\AdminController;

/**
 * Description of QuickcheckMSelQuestion
 * Set up the elements for a TF form.
 *
 * @author paustian
 * 
 */
class MAnsQuestion extends AbstractType {
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
            ->add('quickcheckqtext', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' => $this->translator->__('Question'), 'required' => true))
            ->add('quickcheckqanswer', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' => $this->translator->__('Answer'), 'required' => true))
            ->add('quickcheckqexpan', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' => $this->translator->__('Explanation'), 'required' => true))
            ->add('save',\Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Save Question'));
        
        $builder->add('quickcheckqtype', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array('data' => AdminController::_QUICKCHECK_MULTIANSWER_TYPE));

        $builder->add('categories', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);
    }

    public function getPrefixName()
    {
        return 'paustianquickcheckmodule_mansquesiton';
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
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
        ));
    }
}
