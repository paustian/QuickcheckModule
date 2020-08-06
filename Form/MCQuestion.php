<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Paustian\QuickcheckModule\Controller\AdminController;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

/**
 * Description of QuiccheckTFQuestion
 * Set up the elements for a TF form.
 *
 * @author paustian
 * 
 */
class MCQuestion extends AbstractType {
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator,
        PermissionApiInterface $permissionApi
    ) {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('quickcheckqtext', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' =>  $this->translator->__('Question'), 'required' => true))
            ->add('quickcheckqanswer', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' =>  $this->translator->__('Answer'), 'required' => true))
            ->add('quickcheckqexpan', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' =>  $this->translator->__('Explanation'), 'required' => true))
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => $this->translator->__('Save Question')))
            ->add('delete', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Delete Question'));
        //I only want to show this if admin is looking at it.
        if($this->permissionApi->hasPermission('Quickcheck::', '::', ACCESS_ADMIN)){
            $builder->add('status', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => $this->translator->__('Moderation Status', 'paustianquickcheckmodule') . ':',
                'label_attr' => ['class' => 'radio-inline'],
                'empty_data' => 'default',
                'choices' => [
                    $this->translator->__('Public', 'paustianquickcheckmodule') => '0',
                    $this->translator->__('Moderated', 'paustianquickcheckmodule') => '1',
                    $this->translator->__('Hidden for Exam', 'paustianquickcheckmodule') => '2'
                ],
                'multiple' => false,
                'expanded' => true
            ]);
        }
        $builder->add('quickcheckqtype', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array('data' => AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE));

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
        return 'paustianquickcheckmodule_mcquesiton';
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
