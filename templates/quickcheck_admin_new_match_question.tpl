{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="quickcheck_admin_menu.htm"}
<div class="z-adminbox">
<h3>{gt text="New Matching Question"}</h3>
<p>{gt text="Create a new matching question. Put the matched text into the fact and matching concept dialogs. When the question is asked, they will be randomized."}</p>
<form action="{modurl modname="quickcheck" type="admin" func="createMatchQuestion"}" method="post"
      enctype="multipart/form-data">
      <input type="hidden" name="q_type" value="{$type}" />
      <input type="hidden" name="num_mc_choices" value="{$num_mc_choices}" />
      <input type="hidden" name="id" value="{$id}" />
      <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Quickcheck"}" />
     <p>{gt text="Text of Question"}: </p>
    <p><textarea name="q_text" id="q_text" wrap="soft" rows="3" cols="60">{$q_text}</textarea></p>
    <p>{gt text="Questions facts and matching concepts"}: </p>
    <table class="questions">
        <tr class="table_header"><td class="questions">Fact</td><td class="questions">Matching Concept</td></tr>
        {section name=i loop=$num_mc_choices}
        <tr>
            <td>
                <textarea cols="35" rows="3" name="q_param[]">{$q_param[i]}</textarea>
            </td>
            <td>
                <textarea cols="35" rows="3" name="q_answer[]">{$q_answer[i]}</textarea>
             </td>
        </tr>
        {/section}

        <tr>
            <td>{button src=edit_remove.gif set=icons/small alt="Remove" title="Remove" value="remove"} Remove Choice</td>
            <td>{button src=edit_add.gif set=icons/small alt="Add" title="Add" value="add"} Add Choice</td>
        </tr>

    </table>
    <br />
    <p>{gt text="Write any explanation that you might want for your matching problem:"} </p>
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
    {button src=button_ok.gif set=icons/small alt="_CREATE" title="_CREATE" value="create"} { gt text="Create Question"}
</form>
</div>