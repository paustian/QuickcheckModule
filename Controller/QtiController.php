<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Translation\TranslatorInterface;
use Zikula\ExtensionsModule\AbstractExtension;
use Zikula\ExtensionsModule\Api\ApiInterface\VariableApiInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use ZipArchive;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;
use Paustian\QuickcheckModule\API\UUID;


/**
 * @Route("/qti")
 *
 * export controllers for the quickcheck module
 * This controller is unfinished as it does not import correctly into Canvas
 * I need to find a synthax checker to figure out why (3/23/2020)
 */

class QtiController extends AbstractController{
    public function __construct(
        AbstractExtension $extension,
        PermissionApiInterface $permissionApi,
        VariableApiInterface $variableApi,
        TranslatorInterface $translator,
        ManagerRegistry $managerRegistry
    ) {
        parent::__construct($extension, $permissionApi, $variableApi, $translator);
        $this->managerRegistry = $managerRegistry;
        $this->entityManager = $managerRegistry->getManager();
    }
    /**
     * @Route ("")
     *
     * choose an exam to export
     *
     * Set up a form to present all the exams and let the user choose
     * The one to modify
     *
     * @param Request $request
     * @return Response
     */
    public function index(Request $request) : Response {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $exams = $this->managerRegistry->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity')->get_all_exams();

        if (!$exams) {
            $this->addFlash('error', $this->trans('There are no exams to export'));
            $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
            return $response;
        }
        return $this->render('@PaustianQuickcheckModule/Admin/quickcheck_admin_modify.html.twig', ['exams' => $exams]);
    }

    /**
     * @Route("/export/{exam}")
     *
     * Export an exam
     *
     * @param Request $request
     * @param QuickcheckExamEntity|null $exam
     * @return Response
     */
    public function export(Request $request, QuickcheckExamEntity $exam = null) : Response {
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
        //you must list a question to do this.

        if(null === $exam){
            $id = $request->query->get('id');
            $exam = $this->entityManager->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity')->findOneBy(['id' => $id]);
            if(null === $exam){
                $this->addFlash('status', $this->trans('You must specify an exam to export.'));
                return $response;
            }
        }
        $examTitle = str_replace(' ', '', $exam->getQuickcheckname());
        //create a bunch of unique Ids that have to be created for the module
        $manifestId = UUID::v4();
        $examQuestionId = UUID::v4();
        $resourceId = UUID::v4();
        //create the folder for the information that we will later zip up
        $directory = realpath(__DIR__ . '/../../../' . 'web/uploads/');
        $directory = $directory . '/' . $examTitle;

        //create the manifest template
        $manifest = $this->renderView("@PaustianQuickcheckModule/Qti/imsmanifest.xml.twig",
            ['manifestId' => $manifestId,
                'examQuestionId' => $examQuestionId,
                'resourceId' => $resourceId]);
        //create the assessment meta data file
        $assessmentMetaData = $this->renderView("@PaustianQuickcheckModule/Qti/assessment_meta.xml.twig",
            ['examQuestionId' => $examQuestionId,
                'examTitle' => $examTitle,
                'examDescription' => 'An exam exported from the Quickcheckmodule']);

        //create the item question data file.
        $questionText = $this->_createQuestionXml($exam);
        $assessmentText = $this->renderView("@PaustianQuickcheckModule/Qti/assessment.xml.twig",
            ['examQuestionId' => $examQuestionId,
                'quesitonText' => $questionText,
                'examTitle' => $examTitle]);

        //write out the zip archive
        $archive = new ZipArchive();
        $result = $archive->open($directory . '.zip', ZipArchive::CREATE);
        if($result !== true){
            $this->addFlash('error', $this->trans('Unable to create a the zip archive'));
            return $response;
        }
        $archive->addFromString($examTitle . '/imsmanifest.xml', $manifest);
        $archive->addFromString($examTitle . '/'. $examQuestionId. '/' . $examQuestionId . '.xml', $assessmentText);
        $archive->addFromString($examTitle . '/'. $examQuestionId. '/asessment_meta.xml', $assessmentMetaData);
        $archive->close();
        $this->addFlash('status', $this->trans('Archive Created'));
        return $response;

    }

    /**
     * @param $exam
     * @return string
     */
    private function _createQuestionXml(QuickcheckExamEntity $exam) : string {
        $questions = $exam->getQuickcheckquestions();

        $items = [];
        $correctAnswerId = 0;
        $returnText = "";
        $qNum = 1;
        foreach($questions as $qId){
            //get the question
            $question = $this->entityManager->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $qId);

            if($question->getQuickcheckqType() !== AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE){
                continue;
            }
            $answers = $question->getQuickcheckqAnswer();
            preg_match_all("|(.*)\|(.*)|", $answers, $matches);
            $ansCount = count($matches[1]);
            $answerIds = "";
            for($i = 0; $i < $ansCount; $i++){
                $items[$i]['ident'] = rand(1000,9999);
                if($i < $ansCount - 1){
                    $answerIds .= $items[$i]['ident'] . ",";
                } else {
                    $answerIds .= $items[$i]['ident'];
                }
                $items[$i]['response'] = $matches[1][$i];
                if($matches[2][$i] == 100){
                    $correctAnswerId = $items[$i]['ident'];
                }
            }
            $returnText .= $this->renderView("@PaustianQuickcheckModule/Qti/itemTemplate.xml.twig",
            ['itemId' => UUID::v4(),
            'answerIds' => $answerIds,
            'assessId' => UUID::v4(),
                'quickchecktext'=> $question->getQuickcheckqText(),
                'items' => $items,
                'correctAnswerId' => $correctAnswerId,
                'title' => $qNum
            ]);
            $qNum++;
        }
        return $returnText;
    }
}