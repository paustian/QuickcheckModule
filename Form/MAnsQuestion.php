<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Paustian\QuickcheckModule\Controller\AdminController;
use Zikula\PermissionsModule\Api\PermissionApi;

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
            ->add('quickcheckqanswer', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
                'label' => $this->translator->trans('Answer'),
                'empty_data' => '',
                'required' => true))
            ->add('quickcheckqexpan', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array(
                'label' => $this->translator->trans('Explanation'),
                'empty_data' => '',
                'required' => true))
            ->add('save',\Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Save Question'))
            ->add('delete', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Delete Question'));
        
        $builder->add('quickcheckqtype', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array('data' => AdminController::_QUICKCHECK_MULTIANSWER_TYPE));
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
        $builder->add('categories', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);
    }

    public function getPrefixName() :string
    {
        return 'paustianquickcheckmodule_mansquesiton';
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
