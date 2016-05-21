<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Zikula\CategoriesModule\Form\Type\CategoriesType;


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
            ->add('importText', TextareaType::class, array('label' => __('Question'), 'required' => false, 'mapped' => false))
            ->add('save', SubmitType::class, array('label' => 'Import Questions'));
        $builder->add('cancel', ButtonType::class, array('label' => __('Cancel')));
        
        $entityCategoryRegistries = \CategoryRegistryUtil::getRegisteredModuleCategories('PaustianQuickcheckModule', 'QuickcheckQuestionEntity', 'id');
        $builder->add('categories', ChoiceType::class, array('placeholder' => 'Choose an option'));
        foreach ($entityCategoryRegistries as $registryId => $parentCategoryId) {
            $builder->add('categories', new CategoriesType($registryId, $parentCategoryId), 
                        ['module' => 'PaustianQuickcheckModule', 'entity' => 'QuickcheckQuestionEntity', 'entityCategoryClass' => 'Paustian\QuickcheckModule\Entity\QuickcheckQuestionCategory']);
        }
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_importtext';
    }
}

