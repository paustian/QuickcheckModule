{include file="Admin/quickcheck_admin_menu.tpl"}
<div class="z-adminbox">
<h3>{gt text="Attach a quickcheck to a module page"}</h3>
<p>{gt text="Choose the quickcheck item that you want attached to your page"}</p>
<form action="{modurl modname="quickcheck" type="admin" func="attach"}" method="post"
      enctype="multipart/form-data">
<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />         
       <input type="hidden" name="art_id" value="{$art_id}">
       <input type="hidden" name="ret_url" value="{$ret_url}">
 <h4>{gt text="Available quickchecks to attach."}</h4>
<table>
        {section loop=$exams name=i}
        <tr>
            <td><input type="radio" name="exam" value="{$exams[i].id}"></td>
            <td>{$exams[i].name}</td>
          </tr>
        {/section}
    </table>

<p>{button src=button_ok.gif set=icons/small alt="Attach Exam" title="Attach Exam" value="attach_exam"}
{button src=button_cancel.gif set=icons/small alt="Cancel" title="Cancel" value="cancel"}
</p>
</form>
</div>