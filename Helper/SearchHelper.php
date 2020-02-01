<?php

namespace Paustian\QuickcheckModule\Helper;
/**
 *
 * @copyright (c) 2020, Timothy Paustian
 * @link http://www.microbiologytext.com
 * @version $Id: pnsearchapi.php 22139 2008-02-07 10:57:16Z Timothy Paustian $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package
 * @subpackage Quickcheck
 */



use Paustian\QuickcheckModule\Entity\QuickcheckQuestionRepository;
use Paustian\QuickcheckModule\Controller\AdminController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;
use Zikula\Core\RouteUrl;

class SearchHelper implements SearchableInterface
{
    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * @var SessionInterface
     */
    private $session;

    private $questionRepository;

    /**
     * SearchHelper constructor.
     * @param PermissionApiInterface $permissionApi
     * @param SessionInterface $session
     * @param QuickcheckQuestionRepository $questionRepository
     */
    public function __construct(
        PermissionApiInterface $permissionApi,
        SessionInterface $session,
        QuickcheckQuestionRepository $questionRepository)
    {
        $this->permissionApi = $permissionApi;
        $this->session = $session;
        $this->questionRepository = $questionRepository;
    }
    /**
     * {@inheritdoc}
     */
    public function amendForm(FormBuilderInterface $form)
    {
        // not needed because `active` child object is already added and that is all that is needed.
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(array $words, $searchType = 'AND', $modVars = null)
    {
        //return an empty array if you don't have permission to be able to search questions
        if (!$this->permissionApi->hasPermission('Book::',"::", ACCESS_ADMIN)){
            return [];
        }
        $hits = $this->questionRepository->getSearchResults($words, $searchType);
        $sessionID = $this->session->getId();
        $returnArray = [];
        foreach ($hits as $question) {
            $url = $this->_determineRoute($question);
            //No need for permisison here, restricted to admin already. We only want Admins to be able to
            //search for questions to use. I may change this in the future.
            $result = new SearchResultEntity();
            $result->setTitle("Question ID: " . $question->getId())
                ->setModule('PaustianBookModule')
                ->setText($this->shorten_text($question->getQuickcheckqText()))
                ->setSesid($sessionID)
                ->setUrl($url);
            $returnArray[] = $result;
        }
        return $returnArray;
    }

    private function _determineRoute(QuickcheckQuestionEntity $question){
        $url = "";
        switch($question->getQuickcheckqType()){
            case AdminController::_QUICKCHECK_TEXT_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_edittextquest', ['id' => $question->getId()]);
                break;
            case AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_editmcquest', ['id' => $question->getId()]);
                break;
            case AdminController::_QUICKCHECK_TF_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_edittfquest', ['id' => $question->getId()]);
                break;
            case AdminController::_QUICKCHECK_MATCHING_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_editmatchquest', ['id' => $question->getId()]);
                break;
            case AdminController::_QUICKCHECK_MULTIANSWER_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_editmansquest', ['id' => $question->getId()]);
                break;
        }
        return $url;
    }
    public function getErrors()
    {
        return [];
    }

    /**
     * private function to shorten the contents text string
     * I think the search display stuff should be doing this
     * but it is not
     */
    private function shorten_text($text) {
// Change to the number of characters you want to display
        $chars = 200;
        if(strlen($text) < $chars){
            return $text;
        }
        $text = $text . " ";
        $text = substr($text, 0, $chars);
        $text = substr($text, 0, strrpos($text, ' '));
        $text = $text . "...";

        return $text;
    }

}


