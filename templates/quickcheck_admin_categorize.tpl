{include file="quickcheck_admin_menu.htm"}

<div class="z-adminbox">
<h3>{gt text="Assign categories to questions"}</h3>
<p>{gt text="Select question(s) to categorize. Choose the category and then hit the categorize button."}</p>
<form action="{modurl modname="quickcheck" type="admin" func="addtocategory"}" method="post"
      enctype="multipart/form-data">
      <input type="hidden" name="authid" value="{insert name="generateauthkey" module="Quickcheck"}" />
       <ul id="treemenu2" class="treeview">
        {$questions}
       </ul>
    
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
    <p>{button src=folder_documents.png set=icons/small alt="Categorize" title="Categorize" value="categorize_checked"}{gt text="Categorize"}</p>
</form>
<script type="text/javascript">

    //ddtreemenu.createTree(treeid, enablepersist, opt_persist_in_days (default is 1))
    ddtreemenu.createTree("treemenu2", true, 5)

</script>
</div>