<?php

/**
 * quickcheck Module
 *
 * The quickcheck module is a module for adding short quizzes to the
 * end of modules. This is idea for modules that display information
 * that you then want to test comprehension on.
 * 
 * Purpose of file:  Table information for quickcheck module --
 *                   Version information.
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      1.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */
class Quickcheck_Version extends Zikula_AbstractVersion {

    public function getMetaData() {
// The following information is used by the Modules module 
// for display and upgrade purposes
        $meta['name'] = __('Quickcheck');
// the version string must not exceed 10 characters!
        $meta['version'] = '1.1.0';
        $meta['description'] = __('A hookable module for displaying quizzes or exams as part of other modules.');
        $meta['displayname'] = __('Quickcheck');

// The following in formation is used by the credits module
// to display the correct credits
        $meta['core_min'] = '1.3.0'; // Fixed to 1.3.x range
        $meta['core_max'] = '1.3.99'; // Fixed to 1.3.x range
        $meta['capabilities'] = array(HookUtil::SUBSCRIBER_CAPABLE => array('enabled' => true));

        $meta['author'] = 'Timothy Paustian';
        $meta['contact'] = 'http://inst.bact.wisc.edu';


// This one adds the info to the DB, so that users can click on the 
// headings in the permission module
        $meta['securityschema'] = array('exam::question' => 'Modulename::');
        return $meta;
    }

    protected function setupHookBundles() {
        $bundle = new Zikula_HookManager_ProviderBundle($this->name, 'provider.quickcheck.ui_hooks.mhp', 'ui_hooks', __('Quickcheck Hook Handlers'));
        $bundle->addServiceHandler('display_view', 'Quickcheck_HookHandler_Mhp', 'ui_view', 'quickcheck.mhp');
        $bundle->addServiceHandler('form_edit', 'Quickcheck_HookHandler_Mhp', 'ui_edit', 'quickcheck.mhp');
        $bundle->addServiceHandler('form_delete', 'Quickcheck_HookHandler_Mhp', 'ui_delete', 'quickcheck.mhp');
        $bundle->addServiceHandler('validate_edit', 'Quickcheck_HookHandler_Mhp', 'validate_edit', 'quickcheck.mhp');
        $bundle->addServiceHandler('validate_delete', 'Quickcheck_HookHandler_Mhp', 'validate_delete', 'quickcheck.mhp');
        $bundle->addServiceHandler('process_edit', 'Quickcheck_HookHandler_Mhp', 'process_edit', 'quickcheck.mhp');
        $bundle->addServiceHandler('process_delete', 'Quickcheck_HookHandler_Mhp', 'process_delete', 'quickcheck.mhp');
        $this->registerHookProviderBundle($bundle);

        $bundle = new Zikula_HookManager_ProviderBundle($this->name, 'provider.quickcheck.filter_hooks.mhpfilter', 'filter_hooks', __('Quickcheck Hook Handler Filter'));
        $bundle->addStaticHandler('filter', 'Quickcheck_HookHandler_Mhp', 'ui_filter', true);
        $this->registerHookProviderBundle($bundle);

        //... provide more area bundles if necessary
    }

}

?>