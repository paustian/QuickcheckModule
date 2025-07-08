<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Controller;

use Doctrine\Persistence\ManagerRegistry;
use FontLib\Table\DirectoryEntry;
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

    private function getRandomString(int $n) : string {
        return bin2hex(random_bytes($n));
    }

    // ToDo: Create the xml template and then redo the export function. I only need one file so it should be EASY
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

        //create the folder for the information that we will later zip up
        $namePath = preg_replace("/[^A-Za-z0-9]/", '', $exam->getQuickcheckname());
        $this->directory = realpath($request->server->get('DOCUMENT_ROOT')) . $request->server->get('BASE') . '/uploads/' . $namePath;


        //Create the exam questions
        $examXML = $this->_createQuestionXml($exam);
        //write out the zip archive
        $archive = new ZipArchive();
        $zipName = $this->directory . '.zip';
        $result = $archive->open($this->directory . '.zip', ZipArchive::CREATE);
        if($result !== true){
            $this->addFlash('error', $this->trans('Unable to create a the zip archive'));
            return $response;
        }

        $archive->addFromString($examTitle . ".xml", $examXML);
        $archive->close();
        $this->addFlash('status', $this->trans('Archive Created'));
        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $namePath . '.zip"');
        $response->headers->set('Content-length', filesize($zipName));

        @unlink($zipName);
        return $response;

    }

    /**
     * @param $exam
     * @return string
     */
    private function _createQuestionXml(QuickcheckExamEntity $exam) : string {
        $questions = $exam->getQuickcheckquestions();
        $qNum = 1;
        $answerText = "";
        foreach($questions as $qId) {
            //get the question
            $question = $this->entityManager->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $qId);
            $q_type = $question->getQuickcheckqType();
            $q_body = $question->getQuickcheckqText();
            $answers = $question->getQuickcheckqAnswer();
            $points = 1;
            $address_q_id = $this->getRandomString(16);
            $question_id = $this->getRandomString(16);
            $q_title = "Question $qNum";

            preg_match_all("|(.*)\|(.*)|", $answers, $matches);
            $ansCount = count($matches[1]);
            $choices = [];
            if ($q_type === AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE) {
                for($i = 0; $i < $ansCount; $i++) {
                    $choice = [];
                    $choice['ident'] = $qNum *1000 + $i;
                    $choice['text'] = $matches[1][$i];
                    $choices[$i] = $choice;
                    if($matches[2][$i] == 100){
                        $correctid = $choice['ident'];
                    }
                }
                $answerText .= $this->render('@PaustianQuickcheckModule/Qti/mcq_template.xml.twig', [
                    'question_id' => $question_id,
                    'q_body' => $q_body,
                    'q_title' => $q_title,
                    'points' => $points,
                    'address_q_id' => $address_q_id,
                    'choices' => $choices,
                    'correctid' => $correctid])->getContent(). "\n";
                $qNum++;
            } elseif ($q_type === AdminController::_QUICKCHECK_MULTIANSWER_TYPE){
                $correctIdArr = [];
                for($j = 0; $j < $ansCount; $j++) {
                    $choice = [];
                    $choice['ident'] = $qNum *1000 + $j;
                    $choice['text'] = $matches[1][$j];
                    $choices[$j] = $choice;
                    $answer_correctness = [];
                    if($matches[2][$j] > 0){
                        $answer_correctness['correct'] = 1;
                        $answer_correctness['ident'] = $qNum *1000 + $j;
                    } else {
                        $answer_correctness['correct'] = 0;
                        $answer_correctness['ident'] = $qNum *1000 + $j;
                    }
                    $correctIdArr[] = $answer_correctness;
                }
                $answerText .= $this->render('@PaustianQuickcheckModule/Qti/mans_template.xml.twig', [
                    'question_id' => $question_id,
                    'q_title' => $q_title,
                    'points' => $points,
                    'q_body' => $q_body,
                    'address_q_id' => $address_q_id,
                    'choices' => $choices,
                    'correctids' => $correctIdArr])->getContent() . "\n";
                $qNum++;
            }
        }
        $examQid = $this->getRandomString(16);
        return $this->render('@PaustianQuickcheckModule/Qti/assessment.xml.twig',
            ['examQuestionId' => $examQid,
                'max_attempts' => 1,
                'questionText' => $answerText
            ])->getContent();
    }
}