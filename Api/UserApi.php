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
namespace Paustian\QuickcheckModule\Api;

use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use SecurityUtil;

/**
 * get all strains in database items
 * 
 * @return   array   array of items, or false on failure
 */
class UserApi extends \Zikula_AbstractApi {

    public function getall($args) {
        $items = array();

        //security check, almost anyone can see this
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_OVERVIEW)) {
            return $items;
        }

        // create a QueryBuilder instance
        $qb = $this->entityManager->createQueryBuilder();

        // add select and from params
        $qb->select('u')
                ->from('QuickcheckModule:QuickcheckExamEntity', 'u');

        // add limit and offset
        $startnum = (!isset($args['startnum']) || !is_numeric($args['startnum'])) ? 0 : (int) $args['startnum'];
        $numitems = (!isset($args['numitems']) || !is_numeric($args['numitems'])) ? 0 : (int) $args['numitems'];

        if ($numitems > 0) {
            $qb->setFirstResult($startnum)
                    ->setMaxResults($numitems);
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $objArray = $query->getResult();

        // Check for an error with the database code
        if ($objArray === false) {
            return false;
        }

        // Return the items
        return $objArray;
    }

    /**
     * get a specific exam. You can grab them by id or article id
     * 
     * @param    $args['id']  id of example item to get
     * @return   array         item array, or false on failure
     */
    public function get($args) {
        //security check, almost anyone can see this
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_OVERVIEW)) {
            return false;
        }

        if (isset($args['id'])) {
            $id = $args['id'];
        }
        if (isset($args['art_id'])) {
            $art_id = $args['art_id'];
        }
        $item = FALSE;
        if (isset($id) && is_numeric($id)) {
            $item = $this->entityManager->find('QuickcheckModule:QuickcheckExamEntity', $args['id']);
        } else if (isset($art_id) && is_numeric($art_id)) {
            // create a QueryBuilder instance
            $qb = $this->entityManager->createQueryBuilder();

            // add select and from params
            $qb->select('u')
                            ->from('QuickcheckModule:QuickcheckExamsEntity', 'u')
                            ->where('u.getQuickcheckart_id = ?1') - setParameter(1, $art_id);
            //get the query object
            $query = $qb->getQuery();

            // execute query
            $item = $query->getResult();
        } else {
            LogUtil::registerArgsError();
            return false;
        }

        if ($item != false) {
            $item['quickcheckquestions'] = unserialize($item['quickcheckquestions']);
        }
        //no check for failure. We just return false and the calling
        //function has to deal with it. It's fine for this to fail, because
        //often the hooked module will not have an exam attached.
        // Return the items
        return $item;
    }

}

?>