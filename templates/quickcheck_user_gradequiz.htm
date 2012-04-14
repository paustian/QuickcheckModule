<!--This template displays the graded quiz and a score, plus a return URL-->
<!--The reader also gets to see the answers this time-->
<h3>{$exam.name}</h3>
<p>{gt text="Your score was"} {$score} {gt text="out of a possible"} {$num_quest} {gt text="points. That is "}
    {$score_percent} {gt text="percent correct."}

    {section loop=$questions name=i}
<p>{math equation="x + 1" x=$smarty.section.i.index}. {$questions[i].q_text}</p>
{if $questions[i].q_type == 0}
<!-- text question -->
<h4>{gt text="Your answer"}</h4>
<p>{$questions[i].ur_answer}</p>
<h4>{gt text="Correct answer"}</h4>
<p>{$questions[i].q_answer}</p>
<p>{$questions[i].q_explan}</p>

{elseif $questions[i].q_type == 1}
<!-- multiple guess question -->
{section loop=$questions[i].q_answer name=k}
{$letters[k]}. {$questions[i].q_answer[k]}<br />
{/section}
<h4>{gt text="Your answer"}</h4>
{assign var=the_index value=$questions[i].ur_answer[0]}
<p>{$letters[$the_index]}</p>
<h4>{gt text="Correct answer"}</h4>
<p>{section loop=$questions[i].q_param name=w}
    {if $questions[i].q_param[w] > 0}
    {$letters[$smarty.section.w.index]},
    {/if}
    {/section}
    {$questions[i].q_explan}</p>

{elseif $questions[i].q_type == 2}
<!-- true/false question -->
<h4>{gt text="Your answer"}</h4>
{if $questions[i].ur_answer == 1}
{assign var='the_tf' value='True'}
{if $questions[i].correct}
{assign var='correct_tf' value='True'}
{else}
{assign var='correct_tf' value='False'}
{/if}
{else}
{assign var='the_tf' value='False'}
{if $questions[i].correct}
{assign var='correct_tf' value='False'}
{else}
{assign var='correct_tf' value='True'}
{/if}
{/if}
<p>{$the_tf}</p>
<h4>{gt text="Correct answer"}</h4>
<p>{$correct_tf}</p>
<p>{$questions[i].q_explan}</p>

{elseif $questions[i].q_type == 3}
<!-- matching question -->
<table border="1">
    <tr>
        <td>{gt text="Concept"}</td>
        <td>{gt text="Your Match"}</td>
        <td>{gt text="Correct Match"}</td>
    </tr>
    {section loop=$questions[i].q_param name=j}
    <tr>
        <td>{$questions[i].q_param[j]}</td>
        <td>{$questions[i].ur_answer[j]}</td>
        <td>{$questions[i].q_answer[j]}</td>
    </tr>
    {/section}
</table>

{elseif $questions[i].q_type == 4}
<!-- multiple answer -->
{section loop=$questions[i].q_answer name=k}
{$letters[k]}. {$questions[i].q_answer[k]}<br />
{/section}
<h4>{gt text="Your answer"}</h4>
<p>
    {section loop=$questions[i].ur_answer name=p}
    {assign var=the_index value=$questions[i].ur_answer[p]}
    {$letters[$the_index]} 
    {/section}
</p>
<h4>{gt text="Correct answer"}</h4>
<p>
    {section loop=$questions[i].q_param name=w}
    {if $questions[i].q_param[w] > 0}
    {$letters[$smarty.section.w.index]} 
    {/if}
    {/section}
</p>
<p>{$questions[i].q_explan}</p>
{/if}
<br />
{/section}