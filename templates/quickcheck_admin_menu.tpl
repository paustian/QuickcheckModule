{*  $Id: quickcheck_user_menu.htm 16293 2005-06-03 11:23:32Z timpaustian $  *}
{insert name='getstatusmsg'}
{adminheader}
<div class="menu">
    <h2>{gt text="Quick check administration menu."}</h2>
    <table>
        <tr>
           </tr>
        <tr>
            <td><a href="{modurl modname="quickcheck" type="admin" func="newTextQuest"}">{gt text="New Text Question"}</a></td>
             <td><a href="{modurl modname="quickcheck" type="admin" func="modify"}">{gt text="Modify Exam"}</a></td>

        </tr>
        <tr>
            <td><a href="{modurl modname="quickcheck" type="admin" func="newTFQuest"}">{gt text="New True/False Question"}</a></td>
            <td><a href="{modurl modname="quickcheck" type="admin" func="editquestions"}">{gt text="Modify Question"}</a></td>
        </tr>
        <tr>
            <td><a href="{modurl modname="quickcheck" type="admin" func="newMCQuest"}">{gt text="New Multiple Choice Question"}</a></td>
            <td><a href="{modurl modname="quickcheck" type="admin" func="categorize"}">{gt text="Recatogorize questions"}</a></td>
        </tr>
        <tr>
            <td><a href="{modurl modname="quickcheck" type="admin" func="newMansQuest"}">{gt text="New Mult-Answer Question"}</a></td>
            <td></td>
        </tr>
        <tr>
            <td><a href="{modurl modname="quickcheck" type="admin" func="newMatchQuest"}">{gt text="New Matching Question"}</a></td>
            <td><a href="{modurl modname="quickcheck" type="admin" func="findunanswered"}">{gt text="Find Unexplained Questions"}</td>
        </tr>
        <tr>
            <td><a href="{modurl modname="quickcheck" type="admin" func="importquiz"}">{gt text="Import an xml file listing questions."}</a></td>
            <td><a href="{modurl modname="quickcheck" type="admin" func="exportquiz"}">{gt text="Export an xml file of questions."}</a></td>
        </tr>
    </table>
</div>