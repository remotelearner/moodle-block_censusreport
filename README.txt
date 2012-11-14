Block:   Census Report
Date:    March 28th - 2011
Version: 2012051802
Release: 2.3.3.1

The new version of the census report includes a number of different display options, which will
be explained below.  In general each display option can be set in two locations.  There are local
settings which can be configured on the individual blocks and global settings which are inherited
from the main block configuration.  The main block configuration also includes an "Override Local
Settings" option which will force all blocks to use only the global configuration and ignore their
local settings.  When the blocks have been overridden this is indicated by a message at the top of
the block.

New settings:
Display Course Name - This option turns on display of the course name in the summary block in the
selected report.  It can be turned on individually for HTML (View), CSV and PDF reports.

Display Course Code - This option turns display of the course code on in the summary block in the
selected report.  It can be turned on individually for HTML (View), CSV and PDF reports.
 
Display Course Id - This option turns display of the course id on in the summary block in the
selected report.  It can be turned on individually for HTML (View), CSV and PDF reports.
 
Display Student Id - This option turns display of the student id on in the body of the selected
report.  It can turned on individually for HTLM (View), CSV and PDF reports.

Display Signature Line - This option turns display of the signature line on in the PDF report.

Display Date Line - This option turns display of the date line on in the PDF report.

PDF Footer Message - This option embeds a section of text into the footer section of the PDF report.


Release notes:

Release 2.3.3:
HOSSUP-6 - Add handling of assignment submissions separately from grade_history checking.

Release 2.3.3.1:
HOSSUP-614 - Removed concatenation from language string for AMOS compatibility
