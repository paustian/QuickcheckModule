<?php

/**
 * Copyright 2009 Zikula Foundation.
 *
 * This work is contributed to the Zikula Foundation under one or more
 * Contributor Agreements and licensed to You under the following license:
 *
 * @license MIT
 *
 * Please see the NOTICE file distributed with this source code for further
 * information regarding copyright and licensing.
 */
class Quickcheck_HookHandler_Mhp extends Zikula_Hook_AbstractHandler {

    /**
     * Zikula_View instance
     *
     * @var Zikula_View
     */
    private $view;

    /**
     * Post constructor hook.
     *
     * @return void
     */
    public function setup() {
        $this->view = Zikula_View::getInstance("Quickcheck");
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
    public function ui_view(Zikula_DisplayHook $hook) {
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_OVERVIEW)) {
            return;
        }
        $is_admin = SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_ADMIN);
        //get the id of the caller
        $id = $hook->getId();
        //look for it in our exam database
        $exam = modUtil::apiFunc('quickcheck', 'user', 'get', array('art_id' => $id));
        $response = '';
        if ($exam) {
            //we have an exam associated with the id of the caller
            //make the quiz and put it in the response
            foreach ($exam['questions'] as $quest) {
                $questions[] = modUtil::apiFunc('quickcheck', 'user', 'getquestion', array('id' => $quest));
            }
            $total = count($questions);
            $q_ids = array();
            for ($i = 0; $i < $total; $i++) {
                $item = $questions[$i];
                $q_ids[] = $item['id'];
                if ($item['q_type'] == 3) {
                    //matching question, add a new parameter
                    $ran_array = $item['q_answer'];
                    shuffle($ran_array);
                    $item['ran_array'] = $ran_array;
                    $questions[$i] = $item;
                }
            }

            $q_ids = DataUtil::formatForDisplay(serialize($q_ids));
            $letters = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
            $this->view->assign('letters', $letters);
            $this->view->assign('q_ids', $q_ids);
            $this->view->assign('questions', $questions);
            $this->view->assign('notice', $notice);
            $ret_url = $hook->getUrl()->getUrl();
            $this->view->assign('ret_url', $ret_url);
            $this->view->assign('exam_name', $exam_name);
            $this->view->assign('art_id', $id);
            if($is_admin){
                $this->view->assign('admin', 'yes');
            }
            // add this response to the event stack
            $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, 'quickcheck_user_display.htm');
        } else {
            //only let admins see this.
            if ($is_admin) {
                $ret_url = $hook->getUrl()->getUrl();
                $this->view->assign('ret_url', $ret_url);
                $this->view->assign('art_id', $id);
                $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, 'quickcheck_admin_pickquestions.htm');
            } else {
                //just send back an empty response
                $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, '');
            }
        }
        $hook->setResponse($response);
    }

    /**
     * Display hook for edit views.
     *
     * Subject is the object being created/edited that we're attaching to.
     * args[id] Is the ID of the subject.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_Hook $hook
     *
     * @return void
     */
    public function ui_edit(Zikula_DisplayHook $hook) {
        // get data from $event
        $id = $hook->getId();

        if (!$id) {
            $access_type = ACCESS_ADD;
        } else {
            $access_type = ACCESS_EDIT;
        }

        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', $access_type)) {
            return;
        }

        // if validation object does not exist, this is the first time display of the create/edit form.
        if (!$this->validation) {
            // either display an empty form,
            // or fill the form with existing data
            if (!$id) {
                // this is a create action so create a new empty object for editing
                $mhp_data = array('dummydata' => '');
            } else {
                // this is an edit action so we probably need to get the data from the DB for editing
                // for this example however, we don't have any data stored in db, so display something random :)
                $mhp_data = array('dummydata' => rand(1, 9));
            }
        } else {
            // this is a re-entry because the form didn't validate.
            // We need to gather the input from the form and render display
            // get the input from the form (this was populated by the validation hook).
            $mhp_data = $this->validation->getObject();
        }

        // assign the hook data to the template
        $this->view->assign('mhp_data', $mhp_data);

        // and also assign the id
        $this->view->assign('id', $id);

        // add this response to the event stack
        $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, 'Quickcheck_hook_mhp_ui_edit.tpl');
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
    public function ui_delete(Zikula_DisplayHook $hook) {
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_DELETE)) {
            return;
        }

        // do some stuff here like get data from database to show in template
        // our example doesn't have any data to fetch, so we will create a random number to show :)
        $mhp_data = array('dummydata' => rand(1, 9));
        $this->view->assign('mhp_data', $mhp_data);

        // add this response to the event stack
        $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, 'Quickcheck_hook_mhp_ui_delete.tpl');
        $hook->setResponse($response);
    }

    /**
     * process edit hook handler.
     *
     * This should be executed only if the validation has succeeded.
     * This is used for both new and edit actions.  We can determine which
     * by the presence of an ID field or not.
     *
     * Subject is the object being created/edited that we're attaching to.
     * args[id] Is the ID of the subject.
     * args[caller] the module who notified of this event.
     *
     * @param Zikula_Hook $hook
     *
     * @return void
     */
    public function process_edit(Zikula_ProcessHook $hook) {

        if (!$hook->getId()) {
            // new so do an INSERT
        } else {
            // existing so do an UPDATE
        }
    }

    /**
     * delete process hook handler.
     *
     * The subject should be the object that was deleted.
     * args[id] Is the is of the object
     * args[caller] is the name of who notified this event.
     *
     * @param Zikula_Hook $hook
     *
     * @return void
     */
    public function process_delete(Zikula_ProcessHook $hook) {
        // this example does not have an data stored in database to delete
        // however, if i had any, i would execute a db call here to delete them
    }

}

?>
