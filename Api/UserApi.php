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
namespace Zikula\QuickcheckModule\Api;

use Zikula\QuickcheckModule\Entity\QuickcheckExamEntity;
use Ziukla\QuickcheckModule\Entity\QuickcheckQuestionEntity;
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
                ->from('QuickcheckModule:QuickcheckExamsEntity', 'u');

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

    /**
     * get all questions
     *  @return array   all of the questions in the module.
     *
     */
    public function getallquestions($args) {
        //security check, almost anyone can see this
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_OVERVIEW)) {
            return false;
        }

        $qb = $this->entityManager->createQueryBuilder();
        if (isset($args['missing_explan']) && $args['missing_explan']) {
            //create a where clause to get only empty explanations
            $qb->select('u')
                ->from('QuickcheckQuestionEntity', 'u')
                ->where('(u.quickcheckq_expan = ?1 OR quickcheckq_expan = ?2) AND (u.quickcheckq_type = ?3 OR u.quickcheckq_type = ?4)')
                ->setParameters(array(1 => 'NULL',
                                2 => '',
                                3 => 4,
                                4 => 1));

            
        } else {
            // add select and from params
            $qb->select('u')
                    ->from('QuickcheckQuestionEntity', 'u');
        }

        // convert querybuilder instance into a Query object
        $query = $qb->getQuery();

        // execute query
        $items = $query->getResult();
        //we need to unserialize all question params and answers.
        $item_count = count($items);
        for ($i = 0; $i < $item_count; $i++) {
            $answer = @unserialize($items[$i]['q_answer']);
            if ($answer !== false) {
                $items[$i]['q_answer'] = $answer;
            }

            $param = @unserialize($items[$i]['q_param']);
            if ($param !== false) {
                $items[$i]['q_param'] = $param;
            }
        }

        // Return the items
        return $items;
    }

    public function getquestion($args) {
        $item = false;
        if (!SecurityUtil::checkPermission('quickcheck::', '::', ACCESS_OVERVIEW)) {
            return $item;
        }
        $id = $args['id'];

        if (!isset($id) || !is_numeric($id)) {
            throw new \InvalidArgumentException(__('id wrong in getquestion'));
        }

        $item = $this->entityManager->find('QuickcheckModule:QuickcheckQuestionEntity', $args['id']);
        if ($item === false) {
            return $item;
        }
        $answer = @unserialize($item['q_answer']);
        if ($answer !== false)
            $item['q_answer'] = $answer;
        $param = @unserialize($item['q_param']);
        if ($param !== false)
            $item['q_param'] = $param;
        // Return the items

        return $item;
    }

    /**
     * utility function to count the number of items held by this module
     * 
     * @return   integer   number of items held by this module
     */
    public function countitems() {
        //Not implemented
    }

}

?>