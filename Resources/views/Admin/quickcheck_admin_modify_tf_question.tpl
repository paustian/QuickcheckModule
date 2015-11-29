{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="Admin/quickcheck_admin_menu.tpl"}
<h3>{gt text="New True/False Question"}</h3>
<p>{gt text="Write your new T/F question in the text box, choose whether it is true or false, and explain the correct answer"}</p>
{pnform}
{pnformvalidationsummary}
<input type="hidden" name="id" value="{$question.id}">
<p>{pnformtextinput id="q_text" name="q_text" textMode="multiline" cols="60" rows="5" maxLength="16000" mandatory="true" text="`$question.q_text`"}</p>
<p>{gt text="Answer to True/False Question"}: </p>
<!--radio buttons for True or False -->
<table>
    <tr>
        <td>
            {if $question.q_answer == 1}
            {pnformradiobutton id="isTrue" checked="1" dataField="q_answer" dataBased="0"} {pnformlabel text="True" for="isTrue"}
            {else}
            {pnformradiobutton id="isTrue" dataField="q_answer" dataBases="0"} {pnformlabel text="True" for="isTrue"}
            {/if}</td>
        <td>{if $question.q_answer == 0}
            {pnformradiobutton id="isFalse" checked="1" dataField="q_answer" dataBased="0"} {pnformlabel text="False" for="isFalse"}
            {else}
            {pnformradiobutton id="isFalse"  dataField="q_answer" dataBased="0"} {pnformlabel text="False" for="isFalse"}
            {/if}</td>
    </tr>
</table>
<p>{gt text="Explanation of True/False question"}: </p>
<p>{pnformtextinput id="q_explan" name="q_explan" textMode="multiline" cols="60" rows="5" maxLength="16000" text="`$question.q_explan`"}</p>
<div class="pn-formrow">
    <label for="pages_categories">{gt text="Category"}</label>
    {gt text="Choose Category" }
    {nocache}
    <ul id="pages_categories" class="selector_category">
        {foreach from=$catregistry key=property item=category}
        {if isset($selectedValue)}
        <li>{selector_category category=$category name="quickcheck_quest[__CATEGORIES__][$property]" field="id" selectedValue=$selectedValue}</li>
        {else}
        <li>{selector_category category=$category name="quickcheck_quest[__CATEGORIES__][$property]" field="id"}</li>
        {/if}
        {/foreach}
    </ul>
    {/nocache}
</div>
{pnformbutton commandName="update" text="Modify"}
{pnformbutton commandName="cancel" text="Cancel"}
{/pnform}
