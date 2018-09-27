<?php

namespace Paustian\QuickcheckModule\Container;

use Symfony\Component\Routing\RouterInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\Core\LinkContainer\LinkContainerInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class LinkContainer implements LinkContainerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;

    /**
     * constructor.
     *
     * @param TranslatorInterface $translator
     * @param RouterInterface $router
     * @param PermissionApiInterface $permissionApi
     **/
    public function __construct(
        TranslatorInterface $translator,
        RouterInterface $router,
        PermissionApiInterface $permissionApi
    )
    {
        $this->translator = $translator;
        $this->router = $router;
        $this->permissionApi = $permissionApi;
    }

    /**
     * get Links of any type for this extension
     * required by the interface
     *
     * @param string $type
     * @return array
     */
    public function getLinks($type = LinkContainerInterface::TYPE_ADMIN)
    {
        if (LinkContainerInterface::TYPE_ADMIN == $type) {
            return $this->getAdmin();
        }
        if (LinkContainerInterface::TYPE_ACCOUNT == $type) {
            return $this->getAccount();
        }
        if (LinkContainerInterface::TYPE_USER == $type) {
            return $this->getUser();
        }

        return [];
    }

    /**
     * get the Admin links for this extension
     *
     * @return array
     */
    private function getAdmin()
    {
        $links = [];
        
        if ($this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            //create exam link
            $links[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_edit'),
                'text' => $this->translator->__('Create Exam'),
                'icon' => 'plus');
            $links[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_modify'),
                'text' => $this->translator->__('Modify Exam'),
                'icon' => 'list');
            //The quesiton editing menu
            $submenulinks = array();
            $submenulinks[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_editquestions'),
                'text' => $this->translator->__('Modify Questions'));
            $submenulinks[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_edittextquest'),
                'text' => $this->translator->__('Create Text Question'));
            $submenulinks[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_edittfquest'),
                'text' => $this->translator->__('Create True/False Question'));
            $submenulinks[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_editmcquest'),
                'text' => $this->translator->__('Create Multiple Choice Question'));
            $submenulinks[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_editmansquest'),
                'text' => $this->translator->__('Create Mult-Answer Question'));
            $submenulinks[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_editmatchquest'),
                'text' => $this->translator->__('Create Matching Question'));
            $submenulinks[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_findmyid'),
                'text' => $this->translator->__('List all the IDs of Questions'));
            $links[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_editquestions'),
                'text' => $this->translator->__('Edit Questions'),
                'icon' => 'list',
                'links' => $submenulinks);

            //the import/export menu
            $submenulinks2 = array();
            $submenulinks2[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_importquiz'),
                'text' => $this->translator->__('Import questions from XML file'));
            $submenulinks2[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_exportquiz'),
                'text' => $this->translator->__('Export questions to XML file'));
            $submenulinks2[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_categorize'),
                'text' => $this->translator->__('Recategorize questions'));
            $submenulinks2[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_findunanswered'),
                'text' => $this->translator->__('Find unexplained questions'));
            $submenulinks2[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_upgradeoldquestions'),
                'text' => $this->translator->__('Update old questions'));
            $links[] = array(
                'url' => $this->router->generate('paustianquickcheckmodule_admin_importquiz'),
                'text' => $this->translator->__('Question Processing'),
                'icon' => 'refresh',
                'links' => $submenulinks2);
        }
        return $links;
    }

    private function getUser()
    {
        $links = [];

        return $links;
    }

    private function getAccount()
    {
        $links = [];

        return $links;
    }

    /**
     * set the BundleName as required by the interface
     *
     * @return string
     */
    public function getBundleName()
    {
        return 'PaustianQuickcheckModule';
    }
}
