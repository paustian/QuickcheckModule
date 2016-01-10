<?php

namespace Paustian\Quickcheck\HookHandler;

use Zikula\Core\Hook\DisplayHook;
/**
 * Copyright 2015 Timothy Paustian
 *
 * @license MIT
 *
 */
class QuickcheckHookHandler extends AbstractHookHandler {


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
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_OVERVIEW)) {
            return;
        }
        $is_admin = SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_ADMIN);
        //get the id of the caller
        //$id = $hook->getId();
        //look for it in our exam database
        $qb = $this->entityManager->getQueryBuilder();
        /*$exam = modUtil::apiFunc('quickcheck', 'user', 'get', array('art_id' => $id));
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
            if ($is_admin) {
                $this->view->assign('admin', 'yes');
            }
            // add this response to the event stack
            $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, 'quickcheck_user_display.tpl');
        } else {
            //only let admins see this.
            if ($is_admin) {
                $ret_url = $hook->getUrl()->getUrl();
                $this->view->assign('ret_url', $ret_url);
                $this->view->assign('art_id', $id);
                $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, 'quickcheck_admin_pickquestions.tpl');
            } else {
                //just send back an empty response
                $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, '');
            }
        }*/
        $this->uiResponse($hook, '');
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
    public function process_delete(Zikula_DisplayHook $hook) {
        // Security check
        if (!SecurityUtil::checkPermission('Quickcheck::', '::', ACCESS_DELETE)) {
            return;
        }
        $id = $hook->getId();
        if ($id) {
            //Check to see if we have an exam attached to this ID
            $exam = modUtil::apiFunc('quickcheck', 'user', 'get', array('art_id' => $id));
            //if we have an exam, detach it from the hooked sample.
            if ($exam) {
                $exam['art_id'] = -1; //no article attached
                modUtil::apiFunc('quickcheck', 'admin', 'update', $exam);
            }
        }
        //we don't need to respond to this. We just detach the exam.
          // add this response to the event stack
//        $response = new Zikula_Response_DisplayHook('provider.Quickcheck.ui_hooks.mhp', $this->view, '');
  //      $hook->setResponse($response);
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
