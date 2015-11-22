{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="quickcheck_admin_menu.htm"}
<div class="z-adminbox">
<h3>{gt text="Create an Exam"}</h3>
<p>{gt text="Pick the questions that you would like on the exam, then click Attach Exam"}</p>
<form action="{modurl modname="quickcheck" type="admin" func="create"}" method="post"
      enctype="multipart/form-data">
<input type="hidden" name="authid" value="{insert name="generateauthkey" module="Quickcheck"}" />
       <input type="hidden" name="art_id" value="{$art_id}">
       <input type="hidden" name="ret_url" value="{$ret_url}">
       <h4>Quickcheck name</h4>
    Exam name: <input type="text" name="name" maxLength="255" size="60">
<h4>Questions to be added to exam.</h4>
 <table>
        {assign var='curr_cat' value=''}
        {section loop=$questions name="i"}
        {if $curr_cat != $questions[i].cat_id}
        {assign var='curr_cat' value=$questions[i].cat_id}
        <tr>
            <td colspan="3"><b>{$questions[i].name}</b></td>
        </tr>
        {/if}
        <tr>
            <td><input type="checkbox" value="{$questions[i].value}" name="questions[]"></td>
            <td>{$questions[i].text}</td>
        </tr>
        {/section}
</table>
<p>{button src=button_ok.gif set=icons/small alt="Attach Exam" title="Attach Exam" value="attach_exam"}
{button src=button_cancel.gif set=icons/small alt="Cancel" title="Cancel" value="cancel"}
</p>
</form>
</div>
