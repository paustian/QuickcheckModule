<?php

namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Zikula\CategoriesModule\Form\Type\CategoryType;
use Zikula\Bundle\FormExtensionBundle\Form\DataTransformer\NullToEmptyTransformer;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Paustian\QuickcheckModule\Controller\AdminController;

/**
 * Description of QuiccheckTFQuestion
 * Set up the elements for a TF form.
 *
 * @author paustian
 * 
 */
class TFQuestion extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('quickcheckqtext', 'textarea', array('label' => __('Question'), 'required' => true))
                ->add('quickcheckqexpan', 'textarea', array('label' => __('Explanation'), 'required' => true))
                ->add('save', 'submit', array('label' => 'Save Question'));
        $builder->add('cancel', 'button', array('label' => __('Cancel')));
        $builder->add('quickcheckqanswer', 'choice', array(
            'choices' => array('True' => 'yes', 'False' => 'no'),
            'required' => true,
            'label' => __('Answer'),
            'choices_as_values' => true,
            'expanded' => true,
            'multiple' => false));
        $builder->add('quickcheckqtype', 'hidden', array('data' => AdminController::_QUICKCHECK_TF_TYPE));
        $id = $options['data']['id'];
        if (isset($id)) {
            $builder->add('id', 'hidden', array('data' => $id));
        }
        $entityCategoryRegistries = \CategoryRegistryUtil::getRegisteredModuleCategories('PaustianQuickcheckModule', 'QuickcheckQuestionEntity', 'id');
        $builder->add('categories', 'choice', array('placeholder' => 'Choose an option'));
        foreach ($entityCategoryRegistries as $registryId => $parentCategoryId) {
            $builder->add('categories', new CategoryType($registryId, $parentCategoryId), array('multiple' => true));
        }
    }

    public function getName() {
        return 'paustianquickcheckmodule_tfquesiton';
    }

    /**
     * OptionsResolverInterface is @deprecated and is supposed to be replaced by
     * OptionsResolver but docs not clear on implementation
     *
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'data_class' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity',
        ));
    }

}