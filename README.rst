The extension tt_board brings a forum in list or tree view to TYPO3.

You must have set this in the constants

::

   PAGE_TARGET = _top

The full documentation is still only in the file doc/manual.odt !

The entry form is only available for logged in front end users of group id=1 by default.
You can activate this in the constants for all users and even those without a login:

::

   plugin.tt_board {
      memberOfGroups = 0
   }
   


