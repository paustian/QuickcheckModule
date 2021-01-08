<?php

declare(strict_types=1);

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



use Paustian\QuickcheckModule\Entity\Repository\QuickcheckQuestionRepository;
use Paustian\QuickcheckModule\Entity\QuickcheckQuestionEntity;
use Paustian\QuickcheckModule\Controller\AdminController;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;
use Zikula\SearchModule\Entity\SearchResultEntity;
use Zikula\SearchModule\SearchableInterface;
use Zikula\Bundle\CoreBundle\RouteUrl;
use DateTime;

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

    /**
     * @var QuickcheckQuestionRepository
     */
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
    public function amendForm(FormBuilderInterface $form) : void
    {
        // not needed because `active` child object is already added and that is all that is needed.
    }

    /**
     * {@inheritdoc}
     */
    public function getResults(array $words, string $searchType = 'AND', ?array $modVars = []) : array
    {
        //return an empty array if you don't have permission to be able to search questions
        if (!$this->permissionApi->hasPermission('Quickcheck::',"::", ACCESS_ADMIN)){
            return [];
        }
        $hits = $this->questionRepository->getSearchResults($words, $searchType);
        $sessionID = $this->session->getId();
        $returnArray = [];
        $now = new DateTime();
        foreach ($hits as $question) {
            $url = $this->_determineRoute($question);
            //No need for permisison here, restricted to admin already. We only want Admins to be able to
            //search for questions to use. I may change this in the future.
            $result = new SearchResultEntity();
            $result->setTitle("Question ID: " . $question['id'])
                ->setModule('PaustianBookModule')
                ->setText($this->shorten_text($question['quickcheckqtext']))
                ->setSesid($sessionID)
                ->setUrl($url)
                ->setCreated($now);
            $returnArray[] = $result;
        }
        return $returnArray;
    }

    public function getBundleName(): string
    {
        return 'PaustianQuickcheckModule';
    }

    private function _determineRoute(array $question) : RouteUrl
    {
        $url = "";
        switch($question['quickcheckqtype']){
            case AdminController::_QUICKCHECK_TEXT_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_edittextquest', ['question' => $question['id']]);
                break;
            case AdminController::_QUICKCHECK_MULTIPLECHOICE_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_editmcquest', ['question' => $question['id']]);
                break;
            case AdminController::_QUICKCHECK_TF_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_edittfquest', ['question' => $question['id']]);
                break;
            case AdminController::_QUICKCHECK_MATCHING_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_editmatchquest', ['question' => $question['id']]);
                break;
            case AdminController::_QUICKCHECK_MULTIANSWER_TYPE:
                $url = new RouteUrl('paustianquickcheckmodule_admin_editmansquest', ['question' => $question['id']]);
                break;
        }
        return $url;
    }
    public function getErrors() : array
    {
        return [];
    }

    /**
     * private function to shorten the contents text string
     * I think the search display stuff should be doing this
     * but it is not
     */
    private function shorten_text(string $text) : string
    {
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


