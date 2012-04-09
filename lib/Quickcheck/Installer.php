<?php
/**
 * quickcheck Module
 *
 * The quickcheck module is a module for entering microbial strain data into
 * a mysql database. The completed database can then be used to identify unknown
 * microbes. I also used this module as an example Zikula module to demonstrates
 * some of the frameworks functionality
 *
 * Purpose of file:  Table information for quickcheck module --
 *                   This file contains all information on database
 *                   tables for the module
 *
 * @package      None
 * @subpackage   Quickcheck
 * @version      2.0
 * @author       Timothy Paustian
 * @copyright    Copyright (C) 2009 by Timothy Paustian
 * @license      http://www.gnu.org/copyleft/gpl.html GNU General Public License
 */


/**
 * initialise the quickcheck module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance.
 * This function MUST exist in the pninit file for a module
 *
 * @author       Timothy Paustian
 * @return       bool       true on success, false otherwise
 */
function quickcheck_init() {
    if (!DBUtil::createTable('quickcheck_exam')) {
        LogUtil::registerError(__('Table creation failed @ create exam table'));
        return false;
    }

    if (!DBUtil::createTable('quickcheck_quest')) {
        LogUtil::registerError(__('Table creation failed @ create quest table'));
        return false;
    }

    //get ready for using categories
    // create our default category
    if (!_quickcheck_createdefaultcategory()) {
        return LogUtil::registerError (__('Category creaction failed.'));
    }

    if (!pnModRegisterHook('item', 'display', 'GUI', 'quickcheck', 'admin', 'manageexams')) {
        LogUtil::registerError(__('Registering the hook for quickcheck failed'));
        return false;
    }
    // Initialisation successful
    return true;
}

function _quickcheck_createdefaultcategory($regpath = '/__SYSTEM__/Modules/Global')
{
    // load necessary classes
    Loader::loadClass('CategoryUtil');
    Loader::loadClassFromModule('Categories', 'Category');
    Loader::loadClassFromModule('Categories', 'CategoryRegistry');

    // get the category path for which we're going to insert our upgraded categories
    $rootcat = CategoryUtil::getCategoryByPath($regpath);
    if ($rootcat) {
        // create an entry in the categories registry
        $registry = new PNCategoryRegistry();
        $registry->setDataField('modname', 'Quickcheck');
        $registry->setDataField('table', 'quickcheck_quest');
        $registry->setDataField('property', 'Main');
        $registry->setDataField('category_id', $rootcat['id']);
        $registry->insert();
    } else {
        return false;
    }

    return true;
}

/**
 * upgrade the Example module from an old version
 *
 * This function can be called multiple times
 * This function MUST exist in the pninit file for a module
 *
 * @author       Timothy Paustian
 * @return       bool       true on success, false otherwise
 */
function quickcheck_upgrade($oldversion) {
    // Upgrade dependent on old version number
    switch($oldversion) {
        case 1.0:
        //nothing to do for the older version
        //structure is the same
    }

    // Update successful
    return true;
}


/**
 * delete the Example module
 *
 * This function is only ever called once during the lifetime of a particular
 * module instance
 * This function MUST exist in the pninit file for a module
 *
 * @author       Timothy Paustian
 * @return       bool       true on success, false otherwise
 */
function quickcheck_delete() {
    if (!DBUtil::dropTable('quickcheck_exam')) {
        return false;
    }
    if (!DBUtil::dropTable('quickcheck_quest')) {
        return false;
    }


    return true;
}



?>