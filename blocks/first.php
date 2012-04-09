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
 * initialise block
 * 
 * @author       The PostNuke Development Team
 */
function quickcheck_firstblock_init()
{
    // Security
    pnSecAddSchema('Example:Firstblock:', 'Block title::');
}

/**
 * get information on block
 * 
 * @author       The PostNuke Development Team
 * @return       array       The block information
 */
function quickcheck_firstblock_info()
{
    return array('text_type'      => 'First',
                 'module'         => 'quickcheck',
                 'text_type_long' => 'Show first example items (alphabetical)',
                 'allow_multiple' => true,
                 'form_content'   => false,
                 'form_refresh'   => false,
                 'show_preview'   => true);
}

/**
 * display block
 * 
 * @author       The PostNuke Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the rendered bock
 */
function quickcheck_firstblock_display($blockinfo)
{
    
}


/**
 * modify block settings
 * 
 * @author       The PostNuke Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       output      the bock form
 */
function quickcheck_firstblock_modify($blockinfo)
{
    
}


/**
 * update block settings
 * 
 * @author       The PostNuke Development Team
 * @param        array       $blockinfo     a blockinfo structure
 * @return       $blockinfo  the modified blockinfo structure
 */
function quickcheck_firstblock_update($blockinfo)
{
    
}

?>