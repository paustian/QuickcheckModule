<?php

declare(strict_types=1);
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Description of QuiccheckTFQuestion
 * Set up the elements for a import form.
 *
 * @author paustian
 * 
 */
class ImportText extends AbstractType {
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * BlockType constructor.
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        $builder
            ->add('importText', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, array('label' =>  $this->translator->trans('Question'), 'required' => false, 'mapped' => false))
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => 'Import Questions'));
        $builder->add('cancel', \Symfony\Component\Form\Extension\Core\Type\ButtonType::class, array('label' =>  $this->translator->trans('Cancel')));
        
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
        return 'paustianquickcheckmodule_importtext';
    }
}

