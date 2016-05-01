<?php

namespace Paustian\QuickcheckModule\HookHandler;

use Zikula\Core\Hook\DisplayHook;
use Zikula\Core\Hook\DisplayHookResponse;
use Zikula\Core\Hook\AbstractHookListener;
use SecurityUtil;
use ServiceUtil;
use Paustian\QuickcheckModule\QuickcheckModuleVersion;
use Zikula_View;
use ModUtil;
/**
 * Copyright 2016 Timothy Paustian
 *
 * @license MIT
 *
 */


class HookHandler extends AbstractHookListener {


    /**
     * Zikula_View instance
     * @var Zikula_View
     */
    public $view;
    
    /**
     * Zikula entity manager instance
     * @var \Doctrine\ORM\EntityManager
     */
    public $em;
    
    public function setup() {
        $this->view = Zikula_View::getInstance("PaustianQuickcheckModule", Zikula_View::CACHE_DISABLED);
        $this->em =  ServiceUtil::get('doctrine.entitymanager');
    }
    
    /**
     * Display hook for view.
     *
     * Subject is the object being viewed that we're attaching to.
     * args[id] is the id of the object.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_Hook $hook
     *
     * @return void
     */
    public function display_view(DisplayHook $hook) {
        // Security check
        /*if (!$this->view->hasPermission('Quickcheck::', '::', ACCESS_OVERVIEW)) {
            return;
        }*/
        $is_admin = SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_ADMIN);
        $return_url = $hook->getUrl();
        $id = $hook->getId();
        
        $examObj = ModUtil::apiFunc('PaustianQuickcheckModule', 'user', 'get_exam', ['article' => $id]);
        
        
        if(null === $examObj){
            $qb2 = $this->em->createQueryBuilder();
        
            // add select and from params
            $qb2->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u', 'u.quickcheckname');
            $query2 = $qb2->getQuery();
            $exams = $query2->getResult();
            $this->view->assign('exams', $exams);
            $this->view->assign('art_id', $id);
            $this->view->assign('return_url', $return_url);
        
            $response = new DisplayHookResponse(QuickcheckModuleVersion::QCPROVIDER_UIAREANAME, $this->view, 'Hook/quickcheck_user_hook.tpl');
        }
        $hook->setResponse($response);
    }

    /**
     * Display hook for delete views.
     *
     * Subject is the object being created/edited that we're attaching to.
     * args[id] Is the ID of the subject.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_Hook $hook
     *
     * @return void
     */
    public function process_delete(DisplayHook $hook) {
        // Security check
        if (!$this->hasPermission('Quickcheck::', '::', ACCESS_DELETE)) {
            return;
        }
        $id = $hook->getId();
        if ($id) {
            //Check to see if we have an exam attached to this ID
            $exam = modUtil::apiFunc('quickcheck', 'user', 'get', array('art_id' => $id));
            //if we have an exam, detach it from the hooked sample.
            if ($exam) {
                $exam['art_id'] = -1; //no article attached
                modUtil::apiFunc('quickcheck', 'admin', 'update', $exam);
            }
        }
        //we don't need to respond to this. We just detach the exam.
          // add this response to the event stack
//        $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, '');
  //      $hook->setResponse($response);
    }

}
