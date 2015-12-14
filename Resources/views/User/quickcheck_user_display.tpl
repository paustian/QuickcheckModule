<!--This template displays the question for the exam and also provides a form for checking the answers-->
<!--Author: TImothy Paustian date: July 11 2010-->
<hr />
<form action="{modurl modname="quickcheck" type="user" func="gradequiz"}" method="post">
      <input type="hidden" name="ret_url" value="{$ret_url}" />
    <input type="hidden" name="q_ids" value='{$q_ids}' />
    
    <h3>{$exam_name}</h3>
    {if count($notice)>0}
    <p>{gt text='The following sections did not have the number of questions that you requested. All questions in that catagory were added.'}</p>
    <ul>
        {section loop=$notice name=j}
        <li>{$notice[j]}</li>
        {/section}
    </ul>
    {/if}
    {section loop=$questions name=i}
    <p>{math equation="x + 1" x=$smarty.section.i.index}. {$questions[i].q_text}</p>
    {if $questions[i].q_type == 0}
    <!-- text question -->
    <p><textarea cols="60" rows="5" name="{$questions[i].id}"></textarea>
        {elseif $questions[i].q_type == 1}
        <!-- multiple guess question -->
        {section loop=$questions[i].q_answer name=k}
        {$letters[k]}. <input type="radio" name="{$questions[i].id}[]" value="{$smarty.section.k.index}"> {$questions[i].q_answer[k]}<br />
        {/section}
        {elseif $questions[i].q_type == 2}
        <!-- true/false question -->
    <p><input type="radio" name="{$questions[i].id}" value="1"> {gt text="True"}<br/>
        <input type="radio" name="{$questions[i].id}" value="0"> {gt text="False"}<br /></p>
    {elseif $questions[i].q_type == 3}
    <!-- matching question -->
    <input type="hidden" name="{$questions[i].id}[]" value="{$questions[i].ran_array|@implode:','}">
    <table>
        {section loop=$questions[i].ran_array name=j}
        <tr>
            <td>{$questions[i].q_param[j]}</td>
            <td>&nbsp;<input type="text" size="2" maxsize="2" name="{$questions[i].id}[]" />&nbsp;</td>
            <td>{math equation="x + 1" x=$smarty.section.j.index}. {$questions[i].ran_array[j]}</td></tr>
        {/section}
    </table>
    {elseif $questions[i].q_type == 4}
    <!-- multiple answer -->
    {section loop=$questions[i].q_answer name=k}
    {$letters[k]}. <input type="checkbox" name="{$questions[i].id}[]" value="{$smarty.section.k.index}"> {$questions[i].q_answer[k]}<br />
    {/section}
    {/if}
    <br />
    {/section}
    {button src=button_ok.gif set=icons/small alt="Grade Quiz" title="Grade Quiz" value="create"} {gt text="Grade Quiz"}
    {if $admin eq 'yes'}
    </form>
    <hr />
    <form action="{modurl modname="quickcheck" type="admin" func="new_exam"}" method="post">
        <input type="hidden" name="ret_url" value="{$ret_url}" />
        <input type="hidden" name="art_id" value="{$art_id}" />
          {button src=xedit.gif set=icons/extrasmall alt="Modify" title="Modify" value="modify"} {gt text="Modify a Quick Check Quiz"}<br />
        {button src=edit_remove.gif set=icons/extrasmall alt="Remove" title="Remove" value="remove"}{gt text="Remove a Quick Check Quiz"}<br />
    </form>
    {/if}