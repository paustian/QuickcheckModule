<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\CategoriesModule\Form\Type\CategoryType;
/**
 * Description of CategorizeForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 * 
 */
class CategorizeForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('save', 'submit', array('label' => 'Recategorize'))
            ->add('cancel', 'button', array('label' => __('Cancel')));
        
        //set up the category registry list
        $entityCategoryRegistries = \CategoryRegistryUtil::getRegisteredModuleCategories('PaustianQuickcheckModule', 'QuickcheckQuestionEntity', 'id');
        $builder->add('categories', 'choice', array('placeholder' => 'Choose an option'));
        foreach ($entityCategoryRegistries as $registryId => $parentCategoryId) {
            $builder->add('categories', new CategoryType($registryId, $parentCategoryId), array('multiple' => true));
        }
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_categorizeform';
    }
}
