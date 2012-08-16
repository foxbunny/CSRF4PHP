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

To use in pre-5.3 PHP version try removing the namespace declaration and the
followed use statement.

Basic usage scenario
--------------------

The basic usage involves initializing an instance at some point, calling 
either the `generateHiddenField()` or `generateToken()` methods. The former produces 
an XHTML-compliant input element, whereas the latter produces a raw 
Base64-encoded string. In another request, the request can be tested for 
authenticity (to the best of this script's author's knowledge) by calling 
the `checkToken()` method.

The `generateHiddenField()` and `generateToken()` create a `$_SESSION['csrf']`
array, which contains the material for token creation. This data is 
preserved so that the token can be checked later.

License
-------

Copyright (c)2010 by Branko Vukelic. All rights reserved.

This program is free software: you can redistribute it and/or modify it under
the terms of the GNU General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version. (See ``LICENSE`` file for the exact text of the GPL license.)

At your option, you may redistribute and/or modify this program under the terms
of GNU Lesser General Public License as published by the Free Software
Foundation, either version 3 of the License, or (at your option) any later
version. (See ``LGPL`` file for the exact text of LGPL license.)

This program is distributed in the hope that it will be useful, but WITHOUT ANY
WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
PARTICULAR PURPOSE. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with
this program. If not, see <http://www.gnu.org/licenses/>.

Disclaimer
----------

This script has not been widely tested (actually, it's been only tested on 
a local host), so I do not recommend using it without sufficient testing. 
That said, I do think it will work as expected.

TODO
----

* Write unit tests for the CsrfToken class.
* Implement a helper function or class for checking the HTTP Referrer header.
