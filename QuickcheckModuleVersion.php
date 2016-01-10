<?php

/**
 * quickcheck Module
 *
 * The quickcheck module is a module for adding short quizzes to the
 * end of modules. This is idea for modules that display information
 * that you then want to test comprehension on.
 * 
 * Purpose of file:  Version information for quickcheck module
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      1.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */

namespace Paustian\QuickcheckModule;

use Zikula\Component\HookDispatcher\ProviderBundle;


class QuickcheckModuleVersion extends \Zikula_AbstractVersion {

    public function getMetaData() {
// The following information is used by the Modules module 
// for display and upgrade purposes
        $meta['name'] = __('Quickcheck');
// the version string must not exceed 10 characters!
        $meta['version'] = '2.0.0';
        $meta['description'] = __('A hookable module for displaying quizzes or exams as part of other modules.');
        $meta['displayname'] = __('Quickcheck');
        $meta['url'] = $this->__('quickcheck');

// The following in formation is used by the credits module
// to display the correct credits
        $meta['core_min'] = '1.4.0'; // Fixed to 1.3.x range
        //$meta['capabilities'] = array(HookUtil::PROVIDER_CAPABLE => array('enabled' => true));
        $meta['author'] = 'Timothy Paustian';
        $meta['contact'] = 'http://inst.bact.wisc.edu';


// This one adds the info to the DB, so that users can click on the 
// headings in the permission module
        $meta['securityschema'] = array('PaustianQuickcheckModule::'=>'exam::question' );
        return $meta;
    }
    
    protected function setupHookBundles() {
        $bundle = new ProviderBundle($this->name, 'provider.paustianquickcheckmodule.ui_hooks.quickcheck', 'ui_hooks', __('Quickcheck Hook Handlers'));
        $bundle->addServiceHandler('display_view', $class, 'display_view', $service);
        $bundle->addServiceHandler('process_delete', $class, 'process_delete', $service);
        $this->registerHookProviderBundle($bundle);
    }
}

?>