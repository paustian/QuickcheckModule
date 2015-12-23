{*  $Id: quickcheck_user_menu.htm 16293 2005-06-03 11:23:32Z timpaustian $  *}
{insert name='getstatusmsg'}
<div class="menu">
    <h2>{gt text="Quick check administration menu."}</h2>
    <table>
        <tr>
           </tr>
        <tr>
            <td><a href="{route name='paustianquickcheckmodule_admin_newtextquest'}">{gt text="New Text Question"}</a></td>
             <td><a href="{route name='paustianquickcheckmodule_admin_modify'}">{gt text="Modify Exam"}</a></td>

        </tr>
        <tr>
            <td><a href="{route name='paustianquickcheckmodule_admin_newtfquest'}">{gt text="New True/False Question"}</a></td>
            <td><a href="{route name='paustianquickcheckmodule_admin_editquestions'}">{gt text="Modify Question"}</a></td>
        </tr>
        <tr>
            <td><a href="{route name='paustianquickcheckmodule_admin_newmcquest'}">{gt text="New Multiple Choice Question"}</a></td>
            <td><a href="{route name='paustianquickcheckmodule_admin_categorize'}">{gt text="Recatogorize questions"}</a></td>
        </tr>
        <tr>
            <td><a href="{route name='paustianquickcheckmodule_admin_newmansquest'}">{gt text="New Mult-Answer Question"}</a></td>
            <td></td>
        </tr>
        <tr>
            <td><a href="{route name='paustianquickcheckmodule_admin_newmatchquest'}">{gt text="New Matching Question"}</a></td>
            <td><a href="{route name='paustianquickcheckmodule_admin_findunanswered'}">{gt text="Find Unexplained Questions"}</td>
        </tr>
        <tr>
            <td><a href="{route name='paustianquickcheckmodule_admin_importquiz'}">{gt text="Import an xml file listing questions."}</a></td>
            <td><a href="{route name='paustianquickcheckmodule_admin_ex portquiz'}">{gt text="Export an xml file of questions."}</a></td>
        </tr>
    </table>
</div>