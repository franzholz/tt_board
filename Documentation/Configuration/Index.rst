.. include:: ../Includes.txt


.. _configuration:

Configuration Reference
=======================

Technical information: Installation, Reference of TypoScript options,
configuration options on system level, how to extend it, the technical
details, how to debug it and so on.

Language should be technical, assuming developer knowledge of TYPO3.
Small examples/visuals are always encouraged.

Target group: **Developers**


.. _configuration-typoscript:

TypoScript Reference
--------------------

You must either use the `plugin.tt_board_list` TypoScript prefix or the `plugin.tt_board_tree`. This depends if you want to configure the list or the tree plugins. In the reference only the `plugin.tt_board_list` is mentioned for simplification.

View Properties
^^^^^^^^^^^^^^^

.. container:: ts-properties

	=========================== ===================================== ======================= ====================
	Property                    Data type                             :ref:`t3tsref:stdwrap`  Default
	=========================== ===================================== ======================= ====================
	templateFile _              :ref:`t3tsref:data-type-resource`     no                      :code:`<div>|</div>`
	pid\_list_                  :ref:`t3tsref:data-type-string`       yes                                          
	PIDforum_                   :ref:`t3tsref:data-type-positive-integer`  no
	PIDprivacyPolicy_           :ref:`t3tsref:data-type-positive-integer`  no
	code_                       :ref:`t3tsref:data-type-string`       yes                                          
	defaultCode_                :ref:`t3tsref:data-type-string`       no                                                     
	alternatingLayouts_         :ref:`t3tsref:data-type-integer`      no                      2                           
	date\_stdWrap_              :ref:`t3tsref:stdwrap`                yes                                              
	time\_stdWrap_              :ref:`t3tsref:stdwrap`                yes                                              
	age\_stdWrap_               :ref:`t3tsref:stdwrap`                yes                                              
	dontParseContent_           :ref:`t3tsref:data-type-boolean`      no                                                 
	typolink_                   :ref:`t3tsref:typolink`               no                                                 
	tree_                       :ref:`t3tsref:data-type-boolean`      no                                                 
	iconCode_                   :ref:`t3tsref:data-type-boolean`      no                                                 
	iconCode.joinBottom_        :ref:`t3tsref:data-type-string`       yes                                                 
	iconCode.join_              :ref:`t3tsref:data-type-string`       yes                                                 
	iconCode.line_              :ref:`t3tsref:data-type-string`       yes                                                 
	iconCode.blank_             :ref:`t3tsref:data-type-string`       yes                                                 
	iconCode.thread_            :ref:`t3tsref:data-type-string`       yes                                                 
	iconCode.end_               :ref:`t3tsref:data-type-string`       yes                                                 
	emoticons_                  :ref:`t3tsref:data-type-boolean`      no                      1                
	allowCaching_               :ref:`t3tsref:data-type-boolean`      no                                      
	displayCurrentRecord_       :ref:`t3tsref:data-type-boolean`      no                                      
	wrap1_                      :ref:`t3tsref:stdwrap`                yes                                              
	wrap2_                      :ref:`t3tsref:stdwrap`                yes                                              
	color1_                     :ref:`t3tsref:data-type-string`       yes                                              
	color2_                     :ref:`t3tsref:data-type-string`       yes                                              
	color3_                     :ref:`t3tsref:data-type-string`       yes                                              
	=========================== ===================================== ======================= ====================



View Property Details
^^^^^^^^^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


.. _ts-plugin-tt-board-list-templateFile:

templateFile
""""""""""""

:typoscript:`plugin.tt_board_list.templateFile =` :ref:`t3tsref:data-type-resource`

The template-file. | See example in 'template/board_template1.tmpl'


.. _ts-plugin-tt-board-list-pidList:

pid_list
""""""""

:typoscript:`plugin.tt_board_list.pid_list =` :ref:`t3tsref:data-type-string`

The pid's from where to fetch categories, forums and so on. Default is the current page. Accepts multiple pid's commaseparated!


.. _ts-plugin-tt-board-list-pidForum:

PIDforum
""""""""

:typoscript:`plugin.tt_board_list.PIDforum =` :ref:`t3tsref:data-type-positive-integer`

PID for the forum page. |
You can set this to change the page where the BACKLINK leeds to.


.. _ts-plugin-tt-board-list-pidPrivacyPolicy:

PIDprivacyPolicy
""""""""""""""""

:typoscript:`plugin.tt_board_list.PIDprivacyPolicy =` :ref:`t3tsref:data-type-positive-integer`

PID for the privacy policy page in the TYPO3 page tree. On this page you must publish the privacy policy according to DSGVO and GDPR. If you have set this page and no memberOfGroups is set, then an additional privacy confirmation checkbox will appear.


.. _ts-plugin-tt-board-list-code:

code
""""

:typoscript:`plugin.tt_board_list.code =` :ref:`t3tsref:data-type-string`

Code to define, what the script does. Case sensitive.

    +-----------------+------------------------+------------------------------------------------------------+
    | Code            | Label                  | Description                                                |
    +=================+========================+============================================================+
    | LIST_CATEGORIES | Category, Forum: List  | List the first level of pages as categories and the second |
    |                 |                        | level as forums.                                           |
    +-----------------+------------------------+------------------------------------------------------------+
    | LIST_FORUMS     | Forum: List            | Lists the first level of pages as forums.                  |
    +-----------------+------------------------+------------------------------------------------------------+
    | POSTFORM        | Entry Form: General    | Creates a form from which to post to the forum.            |
    +-----------------+------------------------+------------------------------------------------------------+
    | POSTFORM_REPLY  | Entry Form: Reply      | as above, but ONLY if "tt_board_uid" is set for a reply!   |
    +-----------------+------------------------+------------------------------------------------------------+
    | POSTFORM_THREAD | Entry Form: New Thread | as above, but ONLY if "tt_board_uid" is NOT set, which     |
    |                 |                        | means "New Thread".                                        |
    +-----------------+------------------------+------------------------------------------------------------+
    | FORUM           | Forum: Single          | Shows the content of the current forum. If the GLOBAL-var  |
    |                 |                        | "tt_board_uid" is set with a uid of a board-item, either   |
    |                 |                        | this item is shown or the thread. Depends on config.       |
    +-----------------+------------------------+------------------------------------------------------------+
    | THREAD_TREE     | Forum: Tree            | (tree only) tree of threads                                |
    +-----------------+------------------------+------------------------------------------------------------+
    | HELP            | General: Help          | Help                                                       |
    +-----------------+------------------------+------------------------------------------------------------+


.. _ts-plugin-tt-board-list-defaultCode:

defaultCode
"""""""""""

:typoscript:`plugin.tt_board_list.defaultCode =` :ref:`t3tsref:data-type-string`

The default code (see above) if the value is empty. By default it's not set and a help screen will appear.


.. _ts-plugin-tt-board-list-alternatingLayouts:

alternatingLayouts
""""""""""""""""""

:typoscript:`plugin.tt_board_list.alternatingLayouts =` :ref:`t3tsref:data-type-integer`

Defines number of alternatingLayouts to look for.
This script has the ability to alternate between the use of template-subparts. It goes like this:
If you define a subpart like :html:`<!--###POST###--> ... <!--###POST###-->` this is used all the time.
If you define a similar subpart :html:`<!--###POST_1###--> ... <!--###POST_1###-->` which might show another set of colors, this is used every second time instead of the default! This is because "alternateLayouts" is set to 2
If you define a similar subpart :html:`<!--###POST_2###--> ... <!--###POST_2###-->` ... this will be used every third time IF (!) "alternateLayouts" is set to 3. If you do now set it to three, the first two will be used only.


.. _ts-plugin-tt-board-list-dateStdWrap:

date_stdWrap
""""""""""""

:typoscript:`plugin.tt_board_list.date_stdWrap =` :ref:`t3tsref:stdwrap`

stdWrap for the display of a date. 
Suggestion: :typoscript:`date_stdWrap.strftime= %e-%m-%y`


.. _ts-plugin-tt-board-list-timeStdWrap:

time_stdWrap
""""""""""""

:typoscript:`plugin.tt_board_list.time_stdWrap =` :ref:`t3tsref:stdwrap`

stdWrap for the display of a time.
Suggestion: :typoscript:`time_stdWrap.strftime= %H:%M:%S`


.. _ts-plugin-tt-board-list-ageStdWrap:

age_stdWrap
"""""""""""

:typoscript:`plugin.tt_board_list.age_stdWrap =` :ref:`t3tsref:stdwrap`

stdWrap for the display of an age. 
Suggestion: :typoscript:`age_stdWrap.strftime= 1`


.. _ts-plugin-tt-board-list-dontParseContent:

dontParseContent
""""""""""""""""

:typoscript:`plugin.tt_board_list.dontParseContent =` :ref:`t3tsref:data-type-boolean`

Normally the content which is output it htmlspecialchar'ed and nl2br'ed. This flag prevents that.


.. _ts-plugin-tt-board-list-typolink:

typolink
""""""""

:typoscript:`plugin.tt_board_list.typolink =` :ref:`t3tsref:typolink`

Used to generate the links.


.. _ts-plugin-tt-board-list-tree:

tree
""""

:typoscript:`plugin.tt_board_list.tree =` :ref:`t3tsref:data-type-boolean`

If set the items in the threads are accepted to be a tree and not just a list to the same parent. This means that replys, will get the current tt_board_uid as parent no matter what. This is only desirable, if your board has a genuine tree-structure.


.. _ts-plugin-tt-board-list-iconCode:

iconCode
""""""""

:typoscript:`plugin.tt_board_list.iconCode =` :ref:`t3tsref:data-type-boolean`

Enables the four icons below. The default without this is the examples you see below.


.. _ts-plugin-tt-board-list-iconCode-joinBottom:

iconCode.joinBottom
"""""""""""""""""""

:typoscript:`plugin.tt_board_list.iconCode.joinBottom =` :ref:`t3tsref:data-type-string`

HTML-Code for a "joinBottom" element in a tree-structure. Eg "\-"


.. _ts-plugin-tt-board-list-iconCode-join:

iconCode.join
"""""""""""""

:typoscript:`plugin.tt_board_list.iconCode.join =` :ref:`t3tsref:data-type-string`

as above, Eg. "|-"


.. _ts-plugin-tt-board-list-iconCode-line:

iconCode.line
"""""""""""""

:typoscript:`plugin.tt_board_list.iconCode.line =` :ref:`t3tsref:data-type-string`

as above, Eg. "| "


.. _ts-plugin-tt-board-list-iconCode-blank:

iconCode.blank
"""""""""""""

:typoscript:`plugin.tt_board_list.iconCode.blank =` :ref:`t3tsref:data-type-string`

as above, Eg. "  "


.. _ts-plugin-tt-board-list-iconCode-thread:

iconCode.thread
"""""""""""""""

:typoscript:`plugin.tt_board_list.iconCode.thread =` :ref:`t3tsref:data-type-string`

The icon for an element with reply-elements (called a thread)


.. _ts-plugin-tt-board-list-iconCode-end:

iconCode.end
""""""""""""

:typoscript:`plugin.tt_board_list.iconCode.end =` :ref:`t3tsref:data-type-string`

The icon for an element without any replys (and "end")


.. _ts-plugin-tt-board-list-emoticons:

emoticons
"""""""""

:typoscript:`plugin.tt_board_list.emoticons =` :ref:`t3tsref:data-type-boolean`

Enables emotion icons: :-)  :) etc.


.. _ts-plugin-tt-board-list-allowCaching:

allowCaching
""""""""""""

:typoscript:`plugin.tt_board_list.allowCaching =` :ref:`t3tsref:data-type-boolean`

If set, caching of the each page represented with a tt_board_uid is allowed to be cached.


.. _ts-plugin-tt-board-list-displayCurrentRecord:

displayCurrentRecord
""""""""""""""""""""

:typoscript:`plugin.tt_board_list.displayCurrentRecord =` :ref:`t3tsref:data-type-boolean`

If set, certain settings are manipulated in order to let the script render a single item - the $cObj->data.


.. _ts-plugin-tt-board-list-wrap1:

wrap1
"""""

:typoscript:`plugin.tt_board_list.wrap1 =` :ref:`t3tsref:stdwrap`

Global Wrap 1. This will be splitted into the markers ###GW1B### and ###GW1E###. Don't change the input value by the settings, only wrap it in something.

:aspect:`Example:`
   Code::

   wrap1.wrap = <strong> |</strong>


.. _ts-plugin-tt-board-list-wrap2:

wrap2
"""""

:typoscript:`plugin.tt_board_list.wrap2 =` :ref:`t3tsref:stdwrap`

Global Wrap 2 (see above)


.. _ts-plugin-tt-board-list-color1:

color1
""""""

:typoscript:`plugin.tt_board_list.color1 =` :ref:`t3tsref:data-type-string`

Value for ###GC1### marker (Global colour 1)


.. _ts-plugin-tt-board-list-color2:

color2
""""""

:typoscript:`plugin.tt_board_list.color2 =` :ref:`t3tsref:data-type-string`

Value for ###GC2### marker (Global colour 2)


.. _ts-plugin-tt-board-list-color3:

color3
""""""

:typoscript:`plugin.tt_board_list.color3 =` :ref:`t3tsref:data-type-string`

Value for ###GC3### marker (Global colour 3)






Form Properties
^^^^^^^^^^^^^^^

  code= POSTFORM, POSTFORM_REPLY, POSTFORM_THREAD

.. container:: ts-properties

	=========================== ===================================== ======================= ======================
	Property                    Data type                             :ref:`t3tsref:stdwrap`  Default
	=========================== ===================================== ======================= ======================
	postform_                   FORM :ref:`t3tsref:data-type-cobject` no                      tt_content.mailform.20                    
	postform_newThread_         FORM :ref:`t3tsref:data-type-cobject` no                      tt_content.mailform.20
	moderatorEmail_             :ref:`t3tsref:data-type-string`       no                                                     
	moderatorEmail_newThread_   :ref:`t3tsref:data-type-string`       no                                                     
	memberOfGroups_             :ref:`t3tsref:data-type-string`       no                                                     
	=========================== ===================================== ======================= ======================



Form Property Details
^^^^^^^^^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


.. _ts-plugin-tt-board-list-postform:

postform
""""""""

Only the dataArray of this setting should be changed. You can add entries to the dataArray of the FORM cObject in order to change the view.

Configuration of the form for posting replies and possibly also new threads. 
Note, that two hidden-fields are forcibly added to the form: "parent" and "pid" (using .dataArray keys 9997-9999)!



.. _ts-plugin-tt-board-list-postform_newThread:

postform_newThread
""""""""""""""""""

This is the alternative configuration of the form for new threads. It defaults to postform_ .


.. _ts-plugin-tt-board-list-moderatorEmail:

moderatorEmail
""""""""""""""

If set, this email-address will receive a mail whenever a new reply is submitted.


.. _ts-plugin-tt-board-list-moderatorEmail_newThread:

moderatorEmail_newThread
""""""""""""""""""""""""

If set, this email-address will receive a mail when a new thread is created. If not set, defaults to moderatorEmail_ .


.. _ts-plugin-tt-board-list-memberOfGroups:

memberOfGroups
""""""""""""""

Comma separated list of FE groups which a user must belong to, so that the post forms are shown and the submission of a post is allowed.


+++++++++++++++++++++++++++++++++++++++++++++++++


Category List Properties
^^^^^^^^^^^^^^^^^^^^^^^^

  code= LIST_CATEGORIES

.. container:: ts-properties

	============================ ==================================== ======================= ======================
	Property                     Data type                            :ref:`t3tsref:stdwrap`  Default
	============================ ==================================== ======================= ======================
	noForums_                    :ref:`t3tsref:data-type-boolean`      no                      
	numberOfRecentPosts_         :ref:`t3tsref:data-type-integer`      no                      0
	title\_stdWrap_              :ref:`t3tsref:stdwrap`                yes      
	subtitle\_stdWrap_           :ref:`t3tsref:stdwrap`                yes      
	count\_stdWrap_              :ref:`t3tsref:stdwrap`                yes      
	forum\_title\_stdWrap_       :ref:`t3tsref:stdwrap`                yes      
	forum\_description\_stdWrap_ :ref:`t3tsref:stdwrap`                yes      
	
forum_posts_stdWrap
forum_threads_stdWrap
last_post_author_stdWrap
post_title_stdWrap
post_content_stdWrap
post_replies_stdWrap
post_author_stdWrap

+++++++++++
	title\_stdWrap_              :ref:`t3tsref:stdwrap`                yes      
	title\_stdWrap_              :ref:`t3tsref:stdwrap`                yes      
	title\_stdWrap_              :ref:`t3tsref:stdwrap`                yes      
	title\_stdWrap_              :ref:`t3tsref:stdwrap`                yes      
	title\_stdWrap_              :ref:`t3tsref:stdwrap`                yes      
	============================ ===================================== ======================= ======================



Form Property Details
^^^^^^^^^^^^^^^^^^^^^

.. only:: html

	.. contents::
		:local:
		:depth: 1


.. _ts-plugin-tt-board-list-postform:

postform
""""""""

Only the dataArray of this setting should be changed. You can add entries to the dataArray of the FORM cObject in order to change the view.




   
.. _configuration-faq:

FAQ
---

Possible subsection: FAQ
