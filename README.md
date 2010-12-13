CSRF4PHP: Cross-Site Request Forgery protection kit for for PHP
===============================================================

This file contains the CsrfToken class that handles genration and checking 
of [Synchronization tokens](http://bit.ly/owasp_synctoken).

In future more features will be incorporated into this kit, but the CsrfToken
class is the most important part of the puzzle.

Note on compatibility
---------------------

This kit was written for PHP version 5.3 and upwards. It has not been, and will
not be tested on any previous version of PHP. I believe the code would work
provided you remove the namespace line from ``CsrfToken.php`` (or any other
piece of code that you may find in this package), and use CsrfToken without the
namespaces. 

If you feel support for pre-5.3 version is important to you, please test
CsrfToken as is in your code, and then try removing the namespace. I'm not sure
how it's supposed to work, so I'd appreciate a bug report on that.

Basic usage scenario
--------------------

The basic usage involves initializing an instance at some point, calling 
either the getHiddenField() or generateToken() methods. The former produces 
an XHTML-compliant input element, whereas the latter produces a raw 
Base64-encoded string. In another request, the request can be tested for 
authenticity (to the best of this script's author's knowledge) by calling 
the checkToken() method.

The generateHiddenField() and generateToken() create a $_SESSION['csrf'] 
array, which contains the material for token creation. This data is 
preserved so that the token can be checked later.

License
-------

Copyright (c)2010, by Branko Vukelic.
All rights reserved.
The code for CSRF4PHP is released under GPLv3. You will find the whole text of
the license in the file ``LICENSE.txt`` in this package. If you did not receive
the file with the package, you may 
[find it online](http://www.gnu.org/licenses/gpl-3.0.txt). Alternatively you
may also use CSRF4PHP under the terms of LGPLv3 or any later version of LGPL.
See the file named ``LGPL``. If you have not received this file, you may 
[find it online](http://www.gnu.org/licenses/lgpl-3.0.txt).

Disclaimer
----------

This script has not been widely tested (actually, it's been only tested on 
a local host), so I do not recommend using it without sufficient testing. 
That said, I do think it will work as expected.

TODO
----

* Write unit tests for the CsrfToken class.
* Implement a helper function or class for checking the HTTP Referrer header.
