<?php

namespace Paustian\QuickcheckModule\HookHandler;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Zikula\Bundle\HookBundle\Hook\AbstractHookListener;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use SecurityUtil;
use Paustian\QuickcheckModule\QuickcheckModuleVersion;
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
     * @var EngineInterface
     */
    protected $renderEngine;

    public function __construct(EntityManagerInterface $entityManager, EngineInterface $renderEngine) {
        $this->entityManager = $entityManager;
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
        $is_admin = SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_ADMIN);
        $route_url = $hook->getUrl();
        if(isset($route_url)){
            $return_url = $route_url->getRoute();
        } else {
            $return_url = "";
            //You need to 
            //throw new \Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException($this->__("A return url must be supplied in the subscriber."));
        }
            $id = $hook->getId();
        $repo = $this->entityManager->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
        $examObj = $repo->get_exam($id);
        $exams = null;
        $admininterface = "";
        if ($is_admin) {
            $qb2 = $this->entityManager->createQueryBuilder();

            // add select and from params
            $qb2->select('u')
                    ->from('PaustianQuickcheckModule:QuickcheckExamEntity', 'u', 'u.quickcheckname');
            $query2 = $qb2->getQuery();
            $exams = $query2->getResult();
            $admininterface = $this->renderEngine->render('PaustianQuickcheckModule:Hook:quickcheck.addquiz.html.twig', [
                'exams' => $exams,
                'art_id' => $id,
                'return_url' => $return_url]);
        }
        if (false === $examObj) {
            //Now just use the renderEngine to renger a twig template and send it as a string back as a response.
            if ($is_admin) {
                $content = $admininterface;
            } else {
                return null;
            }
        } else {
            //render the exam.
            $sq_ids = array();
            $letters = array();
            $questions = array();
            $examQuestions = $examObj->getQuickcheckquestions();
            $examName = $examObj->getQuickcheckname();

            $repo->render_quiz($examQuestions, $questions, $sq_ids, $letters);

            if (!$is_admin) {
                $admininterface = "";
            }
            $content = $this->renderEngine->render('PaustianQuickcheckModule:User:quickcheck_user_renderexam.html.twig', ['letters' => $letters,
                'q_ids' => $sq_ids,
                'questions' => $questions,
                'return_url' => $return_url,
                'exam_name' => $examName,
                'admininterface' => $admininterface]);
        }
        $response = new DisplayHookResponse(QuickcheckModuleVersion::QCPROVIDER_UIAREANAME, $content);
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
    public function process_delete(ProcessHook $hook) {
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_DELETE)) {
            return;
        }
        $id = $hook->getId();
        if ($id) {
            $repo = $this->entityManager->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity');
            $exam = $repo->get_exam($id);
            if (false !== $exam) {
                //set the refid to -1 so that we know no document is attached to this exam.
                $exam->setQuickcheckrefid(-1); 
                $this->entityManager->merge($exam);
                $this->entityManager->flush();
            }
        }
    }

}
