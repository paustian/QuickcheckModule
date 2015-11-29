{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="Admin/quickcheck_admin_menu.tpl"}

<div class="z-adminbox">
{if $id==''}
<h3>{gt text="Create a Multiple Choice Questions"}</h3>
{else}
<h3>{gt text="Modify a Multiplc Choice Question"}</h3>
{/if}
<p>{gt text="Write your question and the answers to choose from. Make sure you mark the correct answer."}</p>
<form action="{modurl modname="quickcheck" type="admin" func="createMCQuestion"}" method="post"
      enctype="multipart/form-data">
      <input type="hidden" name="num_mc_choices" value="{$num_mc_choices}"/>
    <input type="hidden" name="pointer" value="{$pointer}" />
    <input type="hidden" name="q_type" value="{$type}" />
    <input type="hidden" name="id" value="{$id}" />
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Exams"}" />
    <p>{gt text="Enter the question"}: </p>
    <p><textarea name="q_text" id="q_text" wrap="soft" rows="3" cols="60">{$q_text}</textarea></p>
    <p>{gt text="Compose and choose the correct answers for the question:"} </p>
    <table class="questions">
        <tr class="table_header"><td class="questions">{gt text="Question choice"}</td><td class="questions">{gt text="Percent Correct"}</td></tr>
        {section name=i loop=$num_mc_choices}
        <tr>
            <td><input type="text" name="q_answer[]" id="q_answer" size="70" max="400" value="{$q_answer[i]}"></td>
            <td>
                <select name="per_correct[]" id="per_correct">
                {html_options options="$percent_correct_val" selected="`$q_param[i]`"}
                </select>
            </td>
        </tr>
        {/section}

        <tr>
            <td>{button src=edit_remove.gif set=icons/small alt="Remove" title="Remove" value="remove"} Remove Choice</td>
            <td>{button src=edit_add.gif set=icons/small alt="Add" title="Add" value="add"} Add Choice</td>
        </tr>

    </table>
    <p>{gt text="Write the explanation for the corect answer:"} </p>
    <textarea name="q_explan" id="q_explan" wrap="soft" rows="3" cols="60">{$q_explan}</textarea>
    <div class="pn-formrow">
        <label for="pages_categories">{gt text="Category"}</label>
        { gt text="Choose Category" }
        {nocache}
        <ul id="pages_categories" class="selector_category">
            {foreach from=$catregistry key=property item=category}
            {if isset($selectedValue)}
             <li>{selector_category category=$category name="quickcheck_quest[__CATEGORIES__][$property]" field="id" selectedValue=$selectedValue defaultValue="0"}</li>
            {else}
             <li>{selector_category category=$category name="quickcheck_quest[__CATEGORIES__][$property]" field="id" defaultValue="0"}</li>
             {/if}
             {/foreach}
        </ul>
        {/nocache}
    </div>
    {button src=button_ok.gif set=icons/small alt="_CREATE" title="_CREATE" value="create"} 
    {if $id==''}
    {gt text="Create Question"}
    {else}
    {gt text="Modify Question"}
    {/if}
</form>
</div>