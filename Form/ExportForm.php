<?php
namespace Paustian\QuickcheckModule\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
/**
 * Description of CategorizeForm
 * Set up the elements for a Exam form.
 *
 * @author paustian
 * 
 */
class ExportForm extends AbstractType {
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('export', 'submit', array('label' => 'Export'))
            ->add('exportall', 'submit', array('label' => __('Export All')));
    }

    public function getName()
    {
        return 'paustianquickcheckmodule_exportform';
    }
}
