.. include:: ../Includes.rst.txt


.. _user-manual:

Users Manual
============

Installation
------------

..  rst-class:: bignums

1.  Install tt_board from the Extension Manager. Maybe the extension must be fetched from TYPO3 TER in the
    Extension Manager or from https://extensions.typo3.org/ by downloading a ZIP file and uploading it into your TYPO3 system.

    .. figure:: /Images/UserManual/ActivateExtensionManager.png
       :width: 700px
       :alt: tt_board in the Extension Manager

       list tt_board in the Extension Manager

   Activate tt_board in the Extension Manager.

..  rst-class:: bignums

2.  Check if tt_board has been activated.

    .. figure:: /Images/UserManual/CheckExtensionManager.png
       :width: 700px
       :alt: tt_board activated in the Extension Manager

       list tt_board in the Extension Manager after activation

   tt_board must have been activated in the Extension Manager.

..  rst-class:: bignums

3.  The Extension Manager should have activated all the dependant extensions automatically.

    .. figure:: /Images/UserManual/ActivateDependantExtensionManager.png
       :width: 700px
       :alt: tt_board's depending extensions activated

       list tt_board's depending extensions in the Extension Manager after activation

   tt_board depends on some extensions which are automatically activated from the Extension Manager when tt_board is activated.

..  rst-class:: bignums

4.  Choose or add a starting page for the forum.

    .. figure:: /Images/UserManual/ForumStartingPage.png
       :width: 700px
       :alt: add a starting page

       Each forum needs a starting page.

   Use the TYPO3 backend page module to add a starting page.

..  rst-class:: bignums

5.  Add a new content element and choose the Board plugin.

    .. figure:: /Images/UserManual/ForumFirstPlugin.png
       :width: 700px
       :alt: choose the first forum plugin

       The forum needs "Discussion Forum" or a "Message Board" plugin.

   Choose the "Message Board" plugin for this example.

..  rst-class:: bignums

6.  Insert the Plugin on your Topics page

    .. figure:: /Images/UserManual/ForumInsertFirstPlugin.png
       :width: 700px
       :alt: insert the first forum plugin

       plugin addition

   After the previous step you will see the "General" tab of the content element of the plugin.

..  rst-class:: bignums

7.  Select the Forum: List flexform of the Plugin “Board, List”

    .. figure:: /Images/UserManual/ForumFirstPluginForumList.png
       :width: 700px
       :alt: insert the first forum list plugin

       forum list plugin addition

   From the list of the available items you shall choose "Forum: List" for this example. Press the save button.

..  rst-class:: bignums

8.  Verify the code in the page module. It must be ‘LIST_FORUMS’.

    .. figure:: /Images/UserManual/ForumCodeForForumList.png
       :width: 700px
       :alt: verify the code of the first forum list plugin

       forum list code verification

   Just to be sure that everything has been accomplished correctly, have a look at the code of the plugin.

..  rst-class:: bignums

9.  Add two subpages underneath the Topics page.
    Add 2 subpages "Forum 1" and "Forum 2"
    Two forum pages are created below the forum starting page.

..  rst-class:: bignums

10. Add a discussion forum to the page “Forum 1”.
    Add plugin for message board 1.
    Use the "New content element wizard".
    Add a new content element of the type plugin and subtype "Message Board" from the plugin tab.

..  rst-class:: bignums

11. Insert the flexforms “Forum: Single” and “Entry Form: General” .

    .. figure:: /Images/UserManual/Forum1InsertDisplayModes.png
       :width: 700px
       :alt: select the message board 1 display modes

       Click 2 message board plugins from "Available items" into "Selected items"

    Choose from the available display modes for the "Message Board" (Board / List).

..  rst-class:: bignums

12. Add the Message board to the “Forum 2” page.

    .. figure:: /Images/UserManual/Forum2InsertPlugin.png
       :width: 700px
       :alt: add plugin for message board 2

        New content element wizard with plugin tab

    Add a new content element of the type plugin and subtype "Message Board" from the plugin tab.

..  rst-class:: bignums

13. Insert the “Forum: Single” and “Entry Form: General” flexforms.

    .. figure:: /Images/UserManual/Forum2InsertDisplayModes.png
       :width: 700px
       :alt: select the message board 2 display modes

       Click 2 message board plugins from "Available items" into "Selected items"

    Choose from the available display modes for the "Message Board" (Board / List).

..  rst-class:: bignums

14. Create an extension template for tt_board in your Templates folder and call it “+ext: tt_board”

    .. figure:: /Images/UserManual/ForumExtensionTemplate.png
       :width: 700px
       :alt: create an extension template for tt_board

        Add a sysfolder "Templates" and create an empty extension template.

    The Constants will be filled in the next step.

..  rst-class:: bignums

15. Insert Constants and Setup for tt_board.

    .. figure:: /Images/UserManual/ForumExtensionTemplateConstantsSetup.png
       :width: 700px
       :alt: add the first constants and setup

        Edit the “+ext: tt_board” template and add important constants and setup.

    The forum  shall be allowed for any user. By default it is limited to logged in front end users. This is because it must be taken care of spammers.

    Insert the Constants.

    .. code-block:: typoscript

        plugin.tt_board {
            memberOfGroups = 0
        }

    Insert the Setup.

    .. code-block:: typoscript
        FEData.tt_board.processScript {
            sendToMailingList = 1
            sendToMailingList {
                email = franz@ttproducts.de
                reply = franz@ttproducts.de
                namePrefix = Typo3Forum
                altSubject = Post from my Forum
            }
        }

    Use your own e-mail adresses instead of the example email address.

..  rst-class:: bignums

16. Add the CSS styles (or use your own CSS file) and the Message Board Setup under “Include static from extensions”.

    .. figure:: /Images/UserManual/ForumExtensionTemplateIncludeStatic.png
       :width: 700px
       :alt: include static from extensions

       Edit the “+ext: tt_board” template and add the templates "Message Board CSS styles (tt_board)" and "Message Board Setup (tt_board)" on the include tab.

    You can forget about the CSS styles template if you include your own CSS file for tt_board.

..  rst-class:: bignums

17. Edit your master template.

    .. figure:: /Images/UserManual/MasterTemplate.png
       :width: 700px
       :alt: edit the master template in the Templates sysfolder

        Open your master template in the Templates sysfolder.

    The master template is the template which defines the whole TYPO3 website.

..  rst-class:: bignums

18. Include the “+ext: tt_board” from your master template under “Include Basis Template”.

    .. figure:: /Images/UserManual/MasterTemplateIncludeTtBoard.png
       :width: 700px
       :alt: edit the master template in the tab include

       The popup window output contains a page browser in order to navigate to the extension template setup and constants.

   You must choose the include tab from the master template and move to the “Include Basis Template” sysfolder symbol. If you click on it then a popup windows shows up. There you choose the “+ext: tt_board” template. This template must be included anywhere into your webpage's template.

..  rst-class:: bignums

19. Check the result.

    .. figure:: /Images/UserManual/MasterTemplateIncludeTtBoardResult.png
       :width: 700px
       :alt: included basis template "+ext tt_board"

       The master template shows the extension template "+ext tt_board" under “Include Basis Template” as included .

   If everything went fine you must also click on the save button.

..  rst-class:: bignums

20. The two forums should be visible in the front end now.

    .. figure:: /Images/UserManual/FrontEnd2Forums.png
       :width: 700px
       :alt: front end view with 2 forums

       The 2 pages of the 2 forums are shown. One forum page is open and it shows the empty forum list, the search entry form and the post entry form.

   You do not see any entries in the forum yet. These will be shown after the first users entered some posts.



.. important::

   The correct output of the forum in the front end will only show up if tt_board has been installed and if the static template of it has been assigned in the TYPO3 backend.

