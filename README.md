Quickcheck is a module that creates quizzes. It is designed to attach to other modules and
from there you create a quiz and attach it to the content of another module. 

Version >4.2.0 is compatible with Zikula 3.1

Version 4.0.0 to 4.1.3 is compatible with Zikula 3.0

Version 3.x is compatible with Zikula 2

##User performance
Version 4.1 and above add the ability to monitor your users performance on exams. Whenever a user uses an exam, it tracks 
the created exam and the questions answered. Users can review their exam performance and administrators can view all exam
performance. 

##Quickcheck functionality

You can create 5 different types of questions and all use a similar interface. There is a question prompt box where you 
enter the question that the students is to answer. Html formatting is allowed in all boxes. Then there is an answer box
where suitable answers are entered. The formatting of this box varies depending upon the quesiton. Finally, every question
requires an explanation of the correct answer and why. Both the question prompt and the question explanation are hookable 
to Scribite to allow a sophisticated text editor. You can also categorize your questions using Zikula's built in categories
These are created in the Zikula Categories module and you select them from the menu. This is a convenient way of organizing
your content. If you are an admin, you can also set the state of the question: public, where anyone can view it; moderation, 
where questions written by editors can be viewed by the admin; and hidden, which is useful if using the database to create 
exams and you want to limit student access to them. Questions written by editors are automatically placed into the moderation
state until an admin approves them and shifts them to the public state.

###Questions
####Text Question
A text question has a prompt and the ansswer is written out by text. At the current time this is not gradable and these
questions are ignored when gradded by the module.

####True/False Question
A prompt and a choice betewen true or false. Pretty obvious.

####Multiple-choice Question
The prompt asks a question and the answer box provides a list of posisble answers with only one being corect. Most 
quiz making applications will create separate text boxes for the answers and for how much they are worth. This makes creating
these questions tedious and instead I use the powerful search capabilities of php to do much of the work. Specific answers
are separated by line and the correctness of an answer is a percentage (from 0 to 100) and is put at the end, separated 
by the pipe (|) character. For example

``This is choice A, it is incorrect|0``<br/>
``This is choice B, it is incorrect|0``<br/>
``This is choice C, it is correct|100``<br/>
``This is choice D, it is incorrect|0``<br/>
``This is choice E, it is incorrect|0``<br/>

You can have up to 10 choices.

####Multi-Answer Questions
Multi-answer questions are similar to mulitple-choice questions except that the percent correct can be spread across
mutiple answers. In the answer box it looks nearly identical, but the percent correct has to add up to 100

``This is choice A, it is incorrect|0``<br/>
``This is choice B, it is incorrect|30``<br/>
``This is choice C, it is correct|30``<br/>
``This is choice D, it is incorrect|0``<br/>
``This is choice E, it is incorrect|40``<br/>

####Matching Questions
Matching questions match two concepts or statements together. In this case each concept appears on one line and the two
matching phrases are separated by a pipe character. Here is an example:
Question prompt: Match the color to the emotion:
Answers

``Red|Angry``<br/>
``Blue|Sad``<br/>
``Yellow|Happy``<br/>
``Green|Content``<br/>

###Exams
####Creating an Exam
To create an exam, click on Create Exam, give the exam a title, and then choose the questions you want to add by, clicking
their checkboxes. Questions are organized into categories to make it easier to find them. If you are looking for a particular 
question, or one with certain text, check out the powerful find features of the module (see below).

####Modifying an Exam
Created exams can be modified by choosing from this menu. In the modfiy interface you can
modify or delete an exam. You can also make a printable copy, suitable for using in a classroom, or view the exam in an 
online format, where it can also be graded. Finally, you can create an rmd export of the exam. The export is usable
in the [R studio and R statistics program](https://rstudio.com/) with the exams package library installed to create 
files that can be imported into learning management systems such as Canvas. When you do the export from the 
QuickcheckModule it provides the commands you need to run in R to create a canvas qti file.

###Procesing menu
####Moderation
As part of my classes I often allow students to write exam questions. Questions initially are in the moderation state, 
which are evalutaed by the admin before being changed to public. This allows editorial control over what ends up in the
quesiton database. 
####Hidden Questions
When it comes time to create an exam, I can either write or choose questions from the database to add to the exam. It
is possible to hide these questions from the public (mainly students) to prevent them from seeing them before the exam.
*Examine all hidden questions for exam* presents an interface where questions can be viewed enmass, be edited, deleted, 
or have their state changed. *Create exam from hidden questions* grabs all currently hidden questions and creates an exam.
Finally, *Move hidden questions to public* takes all hidden questions and changes their state to public. This is useful after
and exam to change the state of hidden quesitons enmass.
####Search for questions
The Quickcheck Module has a powerful interface for searching for questions. You can put in any search text that you want
and limit to specfiic categories. This interface is especially useful because after the search it will present a table 
of question, that match your search text. You can further refine your search by using the search capabilities of the
table.
####Further menu items
It is also possible to export an xml file of questions to work on offline. A similarly formatted xml file can also be 
imported after editing or to create new questions. Questions can be recategorized in batch using *Recatogize questions*. 
Finally, *Find unexplained questions* lists any questions that may not have an explanation. This only happens upon import
of an xml file. The question writing interface in the app will mark unexplianed questions as invalid and will not allow them
 to be saved.
 
 ###Hooks
 The Quickcheck module can subscribe to form aware hook providers such as Scribite. Quickcheck is also a display provider.
 It is possible to attach a Quickcheck quiz to any module that implements the display subscriber interface. When hooked
 to a display subscriber, an interface for choosing an exam will display at the bottom of the subcribing modules pages.
 You can search for the exam you want and link or unlink it to the page. If an exam is linked to the page, it will display
 whenever the page is rendered. 
 
 ###Practice exams
 The Quickcheck module also provides an exam practice interface which is the only user interface 
 (https://your.site.com/quickcheck/). From this interface, a user can choose how many questions they want from
 each category. When the Create Exam button is clicked, the number of questions specified from each category is randomly
 chosen from the data base and displayed to the user for answering. This random quiz can also be graded, which allows
 students to check their understanding of the material.
  



   
 