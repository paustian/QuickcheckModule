{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="Admin/quickcheck_admin_menu.tpl"}
<div class="z-adminbox">
<h3>{gt text="Pick an exam to edit"}</h3>
<form action="{modurl modname="quickcheck" type="admin" func="modify2"}" method="post">
      <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />         
       <table>
        {section loop=$exams name=i}
        <tr>
            <td><input type="checkbox" name="exams[]" value="{$exams[i].id}"> {$exams[i].name}</td>
            <td>{button src=edit.gif set=icons/extrasmall alt="Edit" title="Edit" value="edit_`$exams[i].id`"}</td>
            <td>{button src=edit_remove.gif set=icons/extrasmall alt="Delete" title="Delete" value="delete_`$exams[i].id`"}</td>
        </tr>
        {/section}
    </table>
    {button src=button_ok.gif set=icons/small alt="Delete checked items" title="Delete checked items" value="delete_exams"} {gt text="Delete checked items"}
</form>
</div>