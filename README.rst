|TYPO3| |Monthly Downloads| |Latest Stable Version|

=========================
TYPO3 extension tt_board
=========================

:Repository:  https://github.com/franzholz/tt_board
:Read online: https://docs.typo3.org/p/jambagecom/tt-board/main/en-us/Index.html
:TER:         https://extensions.typo3.org/extension/tt_board/


The extension tt_board brings a forum in list or tree view to TYPO3.

You must have set this in the constants

::

   PAGE_TARGET = _top


The entry form is only available for logged in front end users of group id=1 by default.
You can activate this in the constants for all users and even those without a login:

::

   plugin.tt_board {
      memberOfGroups = 0
   }




.. |TYPO3| image:: https://img.shields.io/badge/TYPO3-Extension-orange?logo=TYPO3
   :target: https://extensions.typo3.org/extension/tt_board
.. |Monthly Downloads| image:: https://poser.pugx.org/jambagecom/tt-board/d/monthly
   :target: https://packagist.org/packages/jambagecom/tt-board
.. |Latest Stable Version| image:: http://poser.pugx.org/jambagecom/tt-board/v
   :target: https://packagist.org/packages/jambagecom/tt-board


