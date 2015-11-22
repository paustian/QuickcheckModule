{*  $Id: quickcheck_admin_new.htm 19361 2006-07-03 14:57:57Z timpaustian $  *}
{include file="quickcheck_admin_menu.htm"}
<div class="z-adminbox">
<h3>{gt text="New True/False Question"}</h3>
<p>{gt text="Write your new T/F question in the text box, choose whether it is true or false, and explain the correct answer"}</p>
<form action="{modurl modname="quickcheck" type="admin" func="createTFQuest"}" method="post"
      enctype="multipart/form-data">
      {if isset($id)}
      <input type="hidden" name="id" value="{$id}" />
    {/if}
    <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Quickcheck"}" />

           <p>{gt text="True/False Question"}: </p>
    <p>{gt text="Enter the question"}: </p>
    <p><textarea name="q_text" id="q_text" wrap="soft" rows="3" cols="60">
{if isset($q_text)}
{$q_text}
{/if}</textarea></p>
    <p>{gt text="Answer to True/False Question"}: </p>
    <!--radio buttons for True or False -->
    {if isset($q_answer)}
    <table>
        <tr>
            <td>
                <input type="radio" name="q_answer" id="q_answer" value="true" >{gt text="True"}
               </td>
        </tr>
        <tr>
            <td><input type="radio" name="q_answer" id="q_answer" value="false" checked>{gt text="False"}</td>
        </tr>
    </table>
    {else}
    <table>
        <tr>
            <td>
                <input type="radio" name="q_answer" id="q_answer" value="true" checked>{gt text="True"}
               </td>
        </tr>
        <tr>
            <td><input type="radio" name="q_answer" id="q_answer" value="false">{gt text="False"}</td>
        </tr>
    </table>
    {/if}
    <p>{gt text="Write the explanation for the correct answer:"} </p>
    <textarea name="q_explan" id="q_explan" wrap="soft" rows="3" cols="60">
{if isset($q_explan)}
{$q_explan}
{/if}</textarea>
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
    {button src=button_ok.gif set=icons/small alt="_CREATE" title="Update" value="update"}
    {button src=button_cancel.gif set=icons/small alt="_CANCEL" title="Cancel" value="cancel"}
</form>
</div>
