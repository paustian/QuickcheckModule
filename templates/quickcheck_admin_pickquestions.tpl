<h3>Quickcheck</h3>
<form action="{modurl modname="quickcheck" type="admin" func="new_exam"}" method="post"
      enctype="multipart/form-data">
<input type="hidden" name="ret_url" value="{$ret_url}">
<input type="hidden" name="art_id" value="{$art_id}">
{if $hasexam==1}
{button src=xedit.gif set=icons/extrasmall alt="Modify" title="Modify" value="modify"} {gt text="Modify a Quick Check Quiz"}<br />
{button src=edit_remove.gif set=icons/extrasmall alt="Remove" title="Remove" value="remove"}{gt text="Remove a Quick Check Quiz"}<br>
{else}
{button src=edit_add.gif set=icons/extrasmall alt="Create" title="Create" value="create"} {gt text="Create a Quick Check Quiz"}<br />
{/if}
{button src=attach.gif set=icons/extrasmall alt="Attach" title="Attach" value="attach"}{gt text="Attach a Quick Check Quiz"}
</form>