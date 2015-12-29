<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Zikula\CategoriesModule\Form\Type\CategoryType;

/**
 * Description of QuiccheckTFQuestion
 * Set up the elements for a TF form.
 *
 * @author paustian
 * 
 */
class ImportText extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('importText', 'textarea', array('label' => __('Question'), 'required' => false, 'mapped' => false))
            ->add('save', 'submit', array('label' => 'Import Questions'));
        $builder->add('cancel', 'button', array('label' => __('Cancel')));
        
        $entityCategoryRegistries = \CategoryRegistryUtil::getRegisteredModuleCategories('PaustianQuickcheckModule', 'QuickcheckQuestionEntity', 'id');
        $builder->add('categories', 'choice', array('placeholder' => 'Choose an option'));
        foreach ($entityCategoryRegistries as $registryId => $parentCategoryId) {
            $builder->add('categories', new CategoryType($registryId, $parentCategoryId), array('multiple' => true));
        }
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_importtext';
    }
}

