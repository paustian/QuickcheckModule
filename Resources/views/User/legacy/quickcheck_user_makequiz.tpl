<h3>{gt text="Exam creator"}</h3>
<form action="{modurl modname="quickcheck" type="user" func="renderquiz"}" method="post">
<table>
    {section loop=$cats name=i}
    <tr>
        <td class="quiz-table"><input type="checkbox" name="catagory[{$cats[i].id}]" /> {$cats[i].name}</td>
        <td class="quiz-table">{gt text="Number of questions"}</td>
        <td class="quiz-table"><input type="text" name="num_questions[{$cats[i].id}]" value="4"/></td>
    </tr>
    {/section}

</table>
      <button type="submit" name="create_quiz" >Create Quiz</button>
</form>