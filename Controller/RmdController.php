<?php

declare(strict_types=1);

namespace Paustian\QuickcheckModule\Controller;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use ZipArchive;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route; // used in annotations - do not remove
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Zikula\Bundle\CoreBundle\Controller\AbstractController;
use Paustian\QuickcheckModule\Entity\QuickcheckExamEntity;


/**
 * @Route("/rmd")
 *
 * export controllers for the quickcheck module
 */

class RmdController extends AbstractController{

    private $directory;

    private $rcommand;

    private $archive;

    /**
     * @Route ("")
     *
     * choose an exam to export
     *
     * Set up a form to present all the exams and let the user choose
     * The one to modify
     * @param Request $request
     * @return Response
     * @throws AccessDeniedException
     */
    public function index(Request $request) : Response {
        if (!$this->hasPermission($this->name . '::', "::", ACCESS_EDIT)) {
            throw new AccessDeniedException();
        }
        $exams = $this->getDoctrine()->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity')->get_all_exams();

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
    public function export(Request $request, QuickcheckExamEntity $exam = null) : Response{
        // Security check - important to do this as early as possible to avoid
        // potential security holes or just too much wasted processing
        if (!$this->hasPermission($this->name . '::', '::', ACCESS_ADD)) {
            throw new AccessDeniedException();
        }
        $response = $this->redirect($this->generateUrl('paustianquickcheckmodule_admin_index'));
        //you must list a question to do this.
        $em = $this->getDoctrine()->getManager();
        if(null === $exam){
            $id = $request->query->get('id');
            $exam = $em->getRepository('PaustianQuickcheckModule:QuickcheckExamEntity')->findOneBy(['id' => $id]);
            if(null === $exam){
                $this->addFlash('status', $this->trans('You must specify an exam to export.'));
                return $response;
            }
        }

        $namePath = preg_replace("/[^A-Za-z0-9]/", '', $exam->getQuickcheckname());
        $this->directory = realpath($request->server->get('DOCUMENT_ROOT')) . $request->server->get('BASE') . '/uploads/' . $namePath;

        $this->archive = new ZipArchive();
        $zipName = $this->directory . '.zip';
        $result = $this->archive->open($zipName, ZipArchive::CREATE);
        if($result !== true){
            $this->addFlash('error', $this->trans('Unable to create a the zip archive'));
            return $response;
        }
        $questions = $exam->getQuickcheckquestions();
        $this->rcommand = " myexam <- c(";
        foreach($questions as $qId) {
            $question = $em->find('PaustianQuickcheckModule:QuickcheckQuestionEntity', $qId);
            //right now only exporting MCQs. I will expand later
            $type = $question->getQuickcheckqType();
            if( ($type === AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE) ||
                ($type === AdminController::_QUICKCHECK_MULTIANSWER_TYPE) ||
                ($type === AdminController::_QUICKCHECK_TF_TYPE)){
                $this->_writeQuestionFile($question, (int)$qId);
            }

        }
        $this->rcommand .= ")\n exams2canvas(myexam)";
        $this->addFlash('status', $this->trans('Use the R command ' . $this->rcommand));
        $this->archive->addFromString("rcommand.txt", $this->rcommand);
        $this->archive->close();

        $response = new Response(file_get_contents($zipName));
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $namePath . '.zip"');
        $response->headers->set('Content-length', filesize($zipName));

        @unlink($zipName);

        return $response;
    }

    /**
     * _writeQuestionFile - write out each question in rmd format
     * @param $question
     * @param $qId
     */
    private function _writeQuestionFile(QuickcheckQuestionEntity $question, int $qId) : void{
        $items = [];
        $answers = $question->getQuickcheckqAnswer();
        if($question->getQuickcheckqType() === AdminController::_QUICKCHECK_TF_TYPE){
            $items[0] = "True";
            $items[1] = "False";
            $items[2]  = "I don't know";
            if($question->getQuickcheckqAnswer() == 1){
                $exsolution = "100";
                $solutions[0] = "That is correct";
                $solutions[1] = "That is incorrect";
            } else {
                $exsolution = "010";
                $solutions[0] = "That is incorrect";
                $solutions[1] = "That is correct";
            }
            $solutions[2] = "That is incorrect";
            $ansCount = 3;
        } else {
            preg_match_all("|(.*)\|(.*)|", $answers, $matches);
            $ansCount = count($matches[1]);
            $exsolution = "";
            $solutions = [];
            $items = [];
            for($i = 0; $i < $ansCount; $i++){
                $items[$i] = $matches[1][$i];
                if($matches[2][$i] > 0){
                    $solutions[$i] = "True. That is a correct answer";
                    $exsolution .= "1";
                } else {
                    $solutions[$i] = "False. That is an incorrect answer";
                    $exsolution .= "0";
                }
            }
        }

        $questionText = $this->renderView("@PaustianQuickcheckModule/Rmd/rmd_export.rmd.twig",
            [  'question' => $question->getQuickcheckqText(),
                'items' => $items,
                'solutions' => $solutions,
                'exname' => $qId,
                'exsolution' => $exsolution,
                'qnum' => $ansCount
            ]);
        $this->archive->addFromString($qId . ".rmd", $questionText);
        $this->rcommand .= ", \"" . $qId . ".rmd\"" ;
    }
}