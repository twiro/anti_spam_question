# Anti Spam Question #

Protect your frontend forms by asking random questions.

This extension for [Symphony CMS](http://getsymphony.com) offers an interface to create a set of questions and answers that help protect your frontend forms against spam. It comes with an event **filter** and a **datasource** that let you include a random question in your form and return an error if the answer doesn't match.


## 1. Installation

1. Upload the `/anti_spam_question` folder in this archive to your Symphony `/extensions` folder.
2. Go to **System → Extensions** in your Symphony admin area.
3. Enable the extension by selecting '**Anti Spam Question**', choose '**Enable**' from the '**With Selected…**' menu, then click '**Apply**'.


## 2. Configuration & Usage ##

1. Go to **System → Anti Spam Question** and create a few questions and answers.
2. Create an event for your frontend form and include the **Anti Spam Question Filter**.
3. Attach your created event to your frontend page.
4. Also attach the **Anti Spam Question Datasource** to your frontend page.
5. Include the following markup in your frontend form.

**Example Frontend Form Markup:**

	<label>
		<xsl:value-of select="//anti-spam-question/question" />
		<input name="anti-spam-question[answer]" type="text" />
		<input name="anti-spam-question[id]" type="hidden" value="{//anti-spam-question/question/@id}" />
	</label>

Now your frontend form will be populated with a random question and will only succeed if the matching answer will be found in the submitted data. Otherwise the **Anti Spam Question Filter** will return an error.


## 3. Acknowledgements ##

This extension was originally developed as "[Answer Me](http://www.getsymphony.com/discuss/thread/391/)" by [Mark Lewis](https://github.com/lewiswharf) in 2008 and refactored as "Anti Spam Question" by Roman Klein in 2017.
