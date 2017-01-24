<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;


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
            ->add('importText', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' => __('Question'), 'required' => false, 'mapped' => false))
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Import Questions'));
        $builder->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' => __('Cancel')));
        
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
        return 'paustianquickcheckmodule_importtext';
    }
}

