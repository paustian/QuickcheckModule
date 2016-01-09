{include file="Admin/quickcheck_admin_menu.tpl"}
<h3>{gt text="Export Questions"}</h3>
<p>{gt text="Select the questions to export. You can also select all the questions by clicking on export all checkbox."}</p>
<form action="{modurl modname="quickcheck" type="admin" func="doexport"}" method="post"
      enctype="multipart/form-data">
       <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />         
      <ul id="treemenu2" class="treeview">
       {$questions}
         </ul>
      <p><input type="checkbox" name="export_all" id="export_all"> {gt text="Export All Questions"}</p>
      <p>{button src=button_ok.gif set=icons/small alt="Export Questions" title="Export Questions" value="create"} {gt text="Export Questions"}</p>
</form>
<script type="text/javascript">

//ddtreemenu.createTree(treeid, enablepersist, opt_persist_in_days (default is 1))
ddtreemenu.createTree("treemenu2", true, 5)

</script>