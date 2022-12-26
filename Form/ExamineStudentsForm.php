<?php

declare(strict_types=1);
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
/**
 * Description of CategorizeForm
 * Set up the elements for a Exam form. A simple forms
 *
 * @author paustian
 *
 */
class ExamineStudentsForm extends AbstractType {

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
            ->add('datecutoff', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array('label' => false, 'widget' => 'single_text'))
            ->add('save', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class, array('label' => $this->translator->trans('Search')));
    }

    public function getPrefixName() : string
    {
        return 'paustianquickcheckmodule_examinestudentsform';
    }
}

