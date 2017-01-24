<?php

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Paustian\QuickcheckModule\Controller\AdminController;
/**
 * Description of MatchQuestion
 * Set up the elements for a matching question form.
 *
 * @author paustian
 * 
 */
class MatchQuestion extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('quickcheckqtext', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' => __('Question'), 'required' => true))
            ->add('quickcheckqanswer', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' => __('Answer'), 'required' => true))
            ->add('quickcheckqexpan', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' => __('Explanation'), 'required' => true))
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Save Question'));
        $builder->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' => __('Cancel')));
        
        $builder->add('quickcheckqtype', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, array('data' => AdminController::_QUICKCHECK_MATCHING_TYPE));

        $builder->add('categories', 'Zikula\CategoriesModule\Form\Type\CategoriesType', [
            'required' => false,
            'multiple' => false,
            'module' => 'PaustianQuickcheckModule',
            'entity' => 'QuickcheckQuestionEntity',
            'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory',
        ]);
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_matchquesiton';
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
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
        ));
    }
}
