<?php

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Zikula\Common\Translator\TranslatorInterface;
use Paustian\QuickcheckModule\Controller\AdminController;

/**
 * Description of QuiccheckTFQuestion
 * Set up the elements for a TF form.
 *
 * @author paustian
 * 
 */
class TFQuestion extends AbstractType {
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
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('quickcheckqtext', TextType::class, array('label' => $this->translator->__('Question'), 'required' => true))
                ->add('quickcheckqexpan', TextareaType::class, array('label' => $this->translator->__('Explanation'), 'required' => true))
                ->add('save', SubmitType::class, array('label' => 'Save Question'));
        $builder->add('cancel', ButtonType::class, array('label' => $this->translator->__('Cancel')));
        $builder->add('quickcheckqanswer', ChoiceType::class, array(
            'choices' => array('True' => '1', 'False' => '0'),
            'required' => true,
            'label' => $this->translator->__('Answer'),
            'choices_as_values' => true,
            'expanded' => true,
            'multiple' => false));
        $builder->add('quickcheckqtype', HiddenType::class, array('data' => AdminController::_QUICKCHECK_TF_TYPE));
        $id = $options['data']['id'];
        if (isset($id)) {
            $builder->add('id', HiddenType::class, array('data' => $id));
        }
        $builder->add('categories', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);
    }

    public function getPrefixName() {
        return 'paustianquickcheckmodule_tfquesiton';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolver $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
        ));
    }

}
