<?php

namespace Paustian\QuickcheckModule\HookHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Zikula\Bundle\HookBundle\Hook\AbstractHookListener;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var EngineInterface
     */
    protected $renderEngine;
    
    public function __construct(EntityManagerInterface $entityManager, RequestStack $requestStack, EngineInterface $renderEngine)
    {
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
        $this->renderEngine = $renderEngine;
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
        
        
        if(false === $examObj){
            $qb2 = $this->entityManager->createQueryBuilder();
        
            // add select and from params
            $qb2->select('u')
                ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u', 'u.quickcheckname');
            $query2 = $qb2->getQuery();
            $exams = $query2->getResult();
            //Now just use the renderEngine to renger a twig template and send it as a string back as a response.

            $content = $this->renderEngine->render('PaustianQuickcheckModule:Hook:quickcheck.addquiz.html.twig', [
            'exams' => $exams]);
            
            $response = new DisplayHookResponse(QuickcheckModuleVersion::QCPROVIDER_UIAREANAME, $content);
            $hook->setResponse($response);
        } 
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
