{assign var='curr_cat' value=''}
{section loop=$questions name="i"}
{if $curr_cat != $questions[i].cat_id}
{if $curr_cat !=''}
</ul></li>
{/if}
{assign var='curr_cat' value=$questions[i].cat_id}
<li><b>{$questions[i].name}</b>
    <ul>
        {/if}
        <li><input type="checkbox" name="questions[]" value="{$questions[i].value}"> {$questions[i].text}</li>
 {/section}
    </ul></li>