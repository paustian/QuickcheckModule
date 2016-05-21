<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Zikula\CategoriesModule\Form\Type\CategoriesType;
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
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Recategorize'))
            ->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' => __('Cancel')));
        
        //set up the category registry list
        $entityCategoryRegistries = \CategoryRegistryUtil::getRegisteredModuleCategories('PaustianQuickcheckModule', 'QuickcheckQuestionEntity', 'id');
        $builder->add('categories', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, array('placeholder' => 'Choose an option'));
        foreach ($entityCategoryRegistries as $registryId => $parentCategoryId) {
            $builder->add('categories', new CategoriesType($registryId, $parentCategoryId), 
                        ['module' => 'PaustianQuickcheckModule', 'entity' => 'QuickcheckQuestionEntity', 'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory']);
        }
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_categorizeform';
    }
}
