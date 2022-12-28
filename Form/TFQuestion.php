<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Paustian\QuickcheckModule\Controller\AdminController;
use Zikula\PermissionsModule\Api\PermissionApi;
use Zikula\CategoriesModule\Form\Type\CategoriesType;

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
     * @var PermissionApi
     */
    private $permissionApi;

    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        PermissionApi $permissionApi
    ) {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
                ->add('quickcheckqtext', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
                    'label' => $this->translator->trans('Question'),
                    'empty_data' => '',
                    'required' => true))
                ->add('quickcheckqexpan', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
                    'label' => $this->translator->trans('Explanation'),
                    'empty_data' => '',
                    'required' => true))
                ->add('save', SubmitType::class, array('label' => 'Save Question'))
                ->add('delete', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Delete Question'));
        $builder->add('quickcheckqanswer', ChoiceType::class, array(
            'choices' => array('True' => '1', 'False' => '0'),
            'required' => true,
            'label' => $this->translator->trans('Answer'),
            'expanded' => true,
            'multiple' => false));
        if($this->permissionApi->hasPermission('Quickcheck::', '::', ACCESS_ADMIN)) {
            $builder->add('status', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => $this->translator->trans('Moderation Status') . ':',
                'label_attr' => ['class' => 'radio-inline'],
                'empty_data' => 'default',
                'choices' => [
                    $this->translator->trans('Public') => '0',
                    $this->translator->trans('Moderated') => '1',
                    $this->translator->trans('Hidden for Exam') => '2'
                ],
                'multiple' => false,
                'expanded' => true
            ]);
        }

        $builder->add('quickcheckqtype', HiddenType::class, array('data' => AdminController::_QUICKCHECK_TF_TYPE));
        $id = $options['data']['id'];
        if (isset($id)) {
            $builder->add('id', HiddenType::class, array('data' => $id));
        }
        $builder->add('categories', CategoriesType::class, [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'expanded' => false,
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);
    }

    public function getPrefixName() : string
    {
        return 'paustianquickcheckmodule_tfquesiton';
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
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
        ));
    }

}
