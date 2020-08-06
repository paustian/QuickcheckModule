<?php

declare(strict_types=1);
namespace Paustian\QuickcheckModule\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Translation\Bundle\EditInPlace\Activator as EditInPlaceActivator;
use Zikula\MenuModule\ExtensionMenu\ExtensionMenuInterface;
use Zikula\PermissionsModule\Api\ApiInterface\PermissionApiInterface;

class ExtensionMenu implements ExtensionMenuInterface
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var PermissionApiInterface
     */
    private $permissionApi;


    public function __construct(
        FactoryInterface $factory,
        PermissionApiInterface $permissionApi
    ) {
        $this->factory = $factory;
        $this->permissionApi = $permissionApi;
    }

    public function get(string $type = self::TYPE_ADMIN): ?ItemInterface
    {
        if (self::TYPE_ADMIN === $type) {
            return $this->getAdmin();
        }

        if (self::TYPE_ACCOUNT === $type){
            return $this->getAccount();
        }

        if(self::TYPE_USER === $type){
            return $this->getUser();
        }
        return null;
    }

    private function getAdmin(): ?ItemInterface
    {
        if (!$this->permissionApi->hasPermission($this->getBundleName() . '::', '::', ACCESS_ADMIN)) {
            return null;
        }

        $menu = $this->factory->createItem('quickcheckMain');

        //Quickcheck functions
        $menu->addChild('Create New Exam', [
            'route' => $this->router->generate('paustianquickcheckmodule_admin_edit'),
        ])->setAttribute('icon', 'fas fa-plus');

        $menu->addChild('Modify Exam', [
            'route' => $this->router->generate('paustianquickcheckmodule_admin_modify'),
        ])->setAttribute('icon', 'fas fa-list');

        //Question menu
        $menu->addChild('Questions', [
            'uri' => '#',
        ])->setAttribute('icon', 'fas fa-list')
            ->setAttribute('dropdown', true);

        $menu['Questions']->addChild('Modify Questions', [
            'route' => 'paustianquickcheckmodule_admin_editquestions'
            ])->setAttribute('icon', 'fas fa-edit');

        $menu['Questions']->addChild('Create Text Question', [
            'route' => 'paustianquickcheckmodule_admin_edittextquest'
        ]);

        $menu['Questions']->addChild('Create True/False Question', [
            'route' => 'paustianquickcheckmodule_admin_edittfquest'
        ]);

        $menu['Questions']->addChild('Create Multiple Choice Question', [
            'route' => 'paustianquickcheckmodule_admin_editmcquest'
        ]);

        $menu['Questions']->addChild('Create Mult-Answer Question', [
            'route' => 'paustianquickcheckmodule_admin_editmansquest'
        ]);

        $menu['Questions']->addChild('Create Matching Question', [
            'route' => 'paustianquickcheckmodule_admin_editmatchquest'
        ]);

        $menu['Questions']->addChild('List all the IDs of Questions', [
            'route' => 'paustianquickcheckmodule_admin_findmyid'
        ]);

        //the import/export menu
        $menu->addChild('Processing', [
            'route' => $this->router->generate('paustianquickcheckmodule_admin_modify'),
        ])->setAttribute('icon', 'fas fa-microchip');

        $menu['Processing']->addChild('Examine all moderated questions', [
            'route' => 'paustianquickcheckmodule_admin_examinemoderated'
        ]);

        $menu['Processing']->addChild('Examine all hidden questions for exam', [
            'route' => 'paustianquickcheckmodule_admin_examinehidden'
        ]);

        $menu['Processing']->addChild('Create an Exam from Hidden Questions', [
            'route' => 'paustianquickcheckmodule_admin_createexamfromhidden'
        ]);

        $menu['Processing']->addChild('Examine all questions', [
            'route' => 'paustianquickcheckmodule_admin_examineall'
        ]);

        $menu['Processing']->addChild('Move Hidden Questions to public', [
            'route' => 'paustianquickcheckmodule_admin_hiddentopublic'
        ]);

        $menu['Processing']->addChild('Import questions from XML file', [
            'route' => 'paustianquickcheckmodule_admin_importquiz'
        ]);

        $menu['Processing']->addChild('Export questions to XML file', [
            'route' => 'paustianquickcheckmodule_admin_exportquiz'
        ]);

        $menu['Processing']->addChild('Recategorize questions', [
            'route' => 'paustianquickcheckmodule_admin_categorize'
        ]);

        $menu['Processing']->addChild('Find unexplained questions', [
            'route' => 'paustianquickcheckmodule_admin_findunanswered'
        ]);

        return 0 === $menu->count() ? null : $menu;
    }

    private function getAccount() : ?ItemInterface {

    }

    private function getUser() : ?ItemInterface {

    }

    public function getBundleName(): string
    {
        return 'PaustianBookModule';
    }
}