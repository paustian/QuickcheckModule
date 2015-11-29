{include file="Admin/quickcheck_admin_menu.tpl"}
<div class="z-adminbox">
<h3>{gt text="Edit or Delete Questions"}</h3>
<p>{gt text="Select a question to edit or delete. You can also select a series of questions to delete, by clicking on the check box and then choosing the delete button"}</p>
<form action="{modurl modname="quickcheck" type="admin" func="modifydeletequestions"}" method="post"
      enctype="multipart/form-data">
      <ul id="treemenu2" class="treeview">
       {$questions}
         </ul>
      <p><input type="checkbox" name="delete_all" id="delete_all"> {gt text="Delete All Questions"}</p>
      <p>{button src=editdelete.png set=icons/medium alt="Delete Checked" title="Delete Checked" value="delete_checked"}
      {button src=edit.png set=icons/medium alt="Modify Checked" title="Modify Checked" value="modify_checked"}</p>
</form>
<script type="text/javascript">

//ddtreemenu.createTree(treeid, enablepersist, opt_persist_in_days (default is 1))
ddtreemenu.createTree("treemenu2", true, 5)

</script>
</div>