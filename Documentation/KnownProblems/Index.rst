.. include:: ../Includes.rst.txt


.. _known-problems:

Known Problems
==============

There is a
`bug tracker <https://github.com/franzholz/tt_board/issues>`_,
where you can find current and former issues. This is the place to report new errors or
to comment to those already listed.

You must insert the 'include static template from extensions' tt_ board static template in the Typoscript module in the backend.

If you do not get any output, then further investigation is needed.
Try to debug the extension where it does not continue.

:file:`tt_board/Classes/Controller/RegisterPluginController.php` :

Insert debug commands.

..  code-block:: php

    public function main($content, $conf)


Try to find out if this PHP code is executed .

..  code-block:: php

    debug ($content, 'HERE $content');
    return $content

The result of `$content` is the output on the screen.


If the PHP code of tt_board is not executed, then the Default Setup has not been inserted into the TypoScript. Check the TypoScript backend module under 'Active TypoScript'.

You can also insert the TypoScript manually from the following extension folders:

:file:`tt_board/Configuration/TypoScript/Default/`

One of the following TypoScript must be there:

List Forum:

..  code-block:: typoscript

    temp.userFuncList = JambageCom\TtBoard\Controller\ListPluginController->main

    plugin.tt_board_list {
       userFunc = JambageCom\TtBoard\Controller\ListPluginController->main
    }


Tree Forum:

..  code-block:: typoscript

    temp.userFuncTree = JambageCom\TtBoard\Controller\TreePluginController->main

    plugin.tt_board_tree {
       userFunc = JambageCom\TtBoard\Controller\TreePluginController->main
    }

Clear all the caches and the typo3temp/var/cache folder.

Try to add the Content Wizard in the TSConfig of the top page:

Page TSconfig
**Include static Page TSconfig (from extensions) [tsconfig_includes]**

Check if it works after using the content wizard. Check if another extension plugin works.


Check if the following TypoScript is there:

..  code-block:: typoscript

    tt_content.list.20.2 = CASE
    tt_content.list.20.2 {
        key.field = layout
        0 = < plugin.tt_board_tree
    }

    tt_content.list.20.4 = CASE
    tt_content.list.20.4 {
        key.field = layout
        0 = < plugin.tt_board_list
        1 = < plugin.tt_board_tree
    }

