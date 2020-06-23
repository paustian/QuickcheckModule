<?php

namespace Paustian\QuickcheckModule\HookHandler;


use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Zikula\Bundle\HookBundle\Category\UiHooksCategory;
use Zikula\Bundle\HookBundle\Hook\DisplayHook;
use Zikula\Bundle\HookBundle\Hook\DisplayHookResponse;
use Zikula\Bundle\HookBundle\Hook\ProcessHook;
use Zikula\Bundle\HookBundle\HookProviderInterface;
use Zikula\Bundle\HookBundle\ServiceIdTrait;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Paustian\QuickcheckModule\Entity\Repository\QuickcheckExamRepository;
use Symfony\Component\Templating\EngineInterface;
use Zikula\Core\UrlInterface;


/**
 * Copyright 2017 Timothy Paustian
 *
 * @license MIT
 *
 */


class UiHooksProviderHandler  implements HookProviderInterface
{
    use ServiceIdTrait;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * ProviderHandler constructor.
     * @param RequestStack $requestStack
     */
    public function __construct(TranslatorInterface $translator,
                                PermissionApiInterface $permissionApi,
                                EngineInterface $templating,
                                EntityManager $entityManager,
                                RequestStack $requestStack)
    {
        $this->translator = $translator;
        $this->permissionApi = $permissionApi;
        $this->templating = $templating;
        $this->entityManager = $entityManager;
        $this->requestStack = $requestStack;
    }

    public function getOwner()
    {
        return 'PaustianQuickcheckModule';
    }

    public function getTitle()
    {
        return $this->translator->__('Quickcheck Display Provider');
    }

    public function getCategory()
    {
        return UiHooksCategory::NAME;
    }

    public function getProviderTypes()
    {
        return [
            UiHooksCategory::TYPE_DISPLAY_VIEW => 'uiView',
            UiHooksCategory::TYPE_PROCESS_DELETE => 'processDelete',
            UiHooksCategory::TYPE_PROCESS_EDIT => 'processEdit'
        ];
    }


    public function uiView(DisplayHook $hook)
    {

        // Security check
        $is_admin = $this->permissionApi->hasPermission('Quickcheck::', '::', ACCESS_ADMIN);
        $route_url = $hook->getUrl();
        if(isset($route_url)){
            $return_url = $route_url->getRoute();
        } else {
            $return_url = "";
        }
        //todo:Check to make sure this is getting the ID we expect from the book. It may be something else.
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
            $admininterface = $this->templating->render('PaustianQuickcheckModule:Hook:quickcheck.addquiz.html.twig', [
                'exams' => $exams,
                'art_id' => $id,
                'return_url' => $return_url]);
        }
        if (false === $examObj) {
            //Now just use the templating to renger a twig template and send it as a string back as a response.
            if ($is_admin) {
                $content = $admininterface;
            } else {
                $content = "";
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
            $content = $this->templating->render('PaustianQuickcheckModule:User:quickcheck_user_renderexam.html.twig', ['letters' => $letters,
                'q_ids' => $sq_ids,
                'questions' => $questions,
                'return_url' => $return_url,
                'exam_name' => $examName,
                'admininterface' => $admininterface,
                'print' => false]);
        }
        $response = new DisplayHookResponse($this->getServiceId(), $content);
        $hook->setResponse($response);
    }

    public function processDelete(ProcessHook $hook)
    {

        $this->requestStack->getMasterRequest()->getSession()->getFlashBag()->add('success', 'Ui hook delete properly processed!');
    }

    public function processEdit(ProcessHook $hook)
    {
        $this->requestStack->getMasterRequest()->getSession()->getFlashBag()->add('success', 'Ui hook edit properly processed!');
    }
}

