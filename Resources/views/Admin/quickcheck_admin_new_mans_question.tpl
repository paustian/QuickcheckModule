{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="Admin/quickcheck_admin_menu.tpl"}
<h3>{pnml name="_QUICKCHECK_NEW_MANS_QUESTION"}</h3>
<p>{pnml name="_QUICKCHECK_NEW_QUEST_DESC"}</p>
{pnform}
{pnformvalidationsummary}
<input type="hidden" name="type" value="{$q_type}">
<p>{pnml name="_QUICKCHECK_NEW_TEXT_QUEST"}: </p>
<p>{pnformtextinput id="q_text" textMode="multiline" cols="60" rows="5" maxLength="16000"}</p>
<p>{pnml name="_QUICKCHECK_NEW_MANS_QUEST_ANSWER"}: </p>
<p>{pnformtextinput id="q_answer" textMode="multiline" cols="60" rows="5" maxLength="16000"}</p>
<div class="pn-formrow">
    <label for="pages_categories">{pnml name="_CATEGORY"}</label>
    { pnml assign="lblDef" name="_CHOOSECATEGORY" }
    {nocache}
    <ul id="pages_categories" class="selector_category">
        {foreach from=$catregistry key=property item=category}
        <li>{selector_category category=$category name="quickcheck_quest[__CATEGORIES__][$property]" field="id" selectedValue=$selectedValue defaultValue="0" defaultText=$lblDef}</li>
        {/foreach}
    </ul>
    {/nocache}
</div>
{pnformbutton commandName="update" text="Create"}
{pnformbutton commandName="cancel" text="Cancel"}
{/pnform}
