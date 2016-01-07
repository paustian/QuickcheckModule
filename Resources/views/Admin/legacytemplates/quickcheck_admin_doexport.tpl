{*  quickcheck_admin_doexport.htm,v 1.0  January 27, 2011 Timothy Paustian *}

{include file="Admin/quickcheck_admin_menu.tpl"}
<p>{gt text="In the text area you will see the exported xml. Copy and paste this into any file to save it"}</p>

<form class="form" action="" method="post" enctype="application/x-www-form-urlencoded">
<textarea name="quest_to_import" cols="120" rows="30">{$questions}</textarea>
</form>