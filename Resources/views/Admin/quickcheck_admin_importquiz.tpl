{*  quickcheck_admin_import.htm,v 1.1 2005/09/02 00:27:29 paustian Exp  *}
{include file="Admin/quickcheck_admin_menu.tpl"}
<div class="z-adminbox">
<p>{gt text="Paste text to import in the area below and click on import. An example of correct text formatting is shown below the text area."}</p>

<form class="form" action="{modurl modname="quickcheck" type="admin" func="doimport"}" method="post" enctype="application/x-www-form-urlencoded">
<input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />         
<textarea name="quest_to_import" cols="120" rows="30">
</textarea>
<p><input name="submit" type="submit" value="{gt text="Import"}" /></p>
</form>

<h3>{gt text="Correct Text Format"}</h3>
<pre>
 &lt;?xml version="1.0" encoding="UTF-8" ?&gt;
&lt;questiondoc&gt;
&lt;question&gt;
    &lt;qtype&gt;multichoice&lt;/qtype&gt;
    &lt;qtext&gt;Multiple choice questions are fun&lt;/qtext&gt;
    &lt;qanswer&gt;Yes they are|Now they are not|I don't know|I don't care&lt;/qanswer&gt;
    &lt;qexplanation&gt;An explanation of the answer goes here.&lt;/qexplanation&gt;
    &lt;qparam&gt;100|0|0|0&lt;/qparam&gt;
&lt;/question&gt;
&lt;question&gt;
    &lt;qtype&gt;multianswer&lt;/qtype&gt;
    &lt;qtext&gt;I like these kinds of pets&lt;/qtext&gt;
    &lt;qanswer&gt;Dogs|Cats|Rabbits|Ferrets&lt;/qanswer&gt;
    &lt;qexplanation&gt;An explanation of the answer goes here.&lt;/qexplanation&gt;
    &lt;qparam&gt;50|50|0|0&lt;/qparam&gt;
&lt;/question&gt;
&lt;question&gt;
    &lt;qtype&gt;truefalse&lt;/qtype&gt;
    &lt;qtext&gt;Life is a highway&lt;/qtext&gt;
    &lt;qanswer&gt;False&lt;/qanswer&gt;
    &lt;qexplanation&gt;An explanation of the answer goes here.&lt;/qexplanation&gt;
    &lt;qparam&gt;&lt;/qparam&gt;
&lt;/question&gt;
&lt;question&gt;
    &lt;qtype&gt;matching&lt;/qtype&gt;
    &lt;qtext&gt;Match the color with the emotion&lt;/qtext&gt;
    &lt;qanswer&gt;Blue|Red|Yellow|Green&lt;/qanswer&gt;
    &lt;qexplanation&gt;It is interesting how certain colors can represent specific emotions&lt;/qexplanation&gt;
    &lt;qparam&gt;Sad|Angry|Content|Greedy&lt;/qparam&gt;
&lt;/question&gt;
&lt;question&gt;
    &lt;qtype&gt;text&lt;/qtype&gt;
    &lt;qtext&gt;Why do you supposed people have different microbes in their intestines?&lt;/qtext&gt;
    &lt;qanswer&gt;Because we all have unique genetic make ups and it depends upon what we eat.&lt;/qanswer&gt;
    &lt;qexplanation&gt;We can be thought of islands where different sets of microbes can reside.&lt;/qexplanation&gt;
    &lt;qparam&gt;&lt;/qparam&gt;
&lt;/question&gt;
&lt;/questiondoc&gt;
</pre>
</div>