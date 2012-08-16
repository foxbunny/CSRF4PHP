<?php
/**
 * CsrfToken.php
 *
 * This fine contains the CsrfToken class that handles genration and checking 
 * of Synchronization tokens (http://bit.ly/owasp_synctoken).
 *
 * The basic usage involves initializing an instance at some point, calling 
 * either the getHiddenField() or generateToken() methods. The former produces 
 * an XHTML-compliant input element, whereas the latter produces a raw 
 * Base64-encoded string. In another request, the request can be tested for 
 * authenticity (to the best of this script's author's knowledge) by calling 
 * the checkToken() method.
 *
 * The generateHiddenField() and generateToken() create a $_SESSION['csrf'] 
 * array, which contains the material for token creation. This data is 
 * preserved so that the token can be checked later.
 *
 * DISCLAIMER: This script has not been widely tested (actually, it's been only 
 * tested on a local host), so I do not recommend using it without sufficient 
 * testing. That said, I do think it will work as expected.
 *
 * TODO: Write unit tests.
 *
 * @author Branko Vukelic <studio@brankovukelic.com>
 * @version 0.1.2
 * @package Csrf 
 */
namespace Csrf;
use Exception;


/**
 * Token generation and checking class
 *
 * This class encapsulates all of the functionality of the Csrf package. On
 * initialization, it checks for session ID, and it will throw an exception is
 * one is not found, so it is best you initialize right after session_start().
 *
 * Since the time used to generate the token is not the time when
 * initialization takes place, you can initialize at any time before token
 * generation.
 *
 * @package Csrf
 * @subpackage classes
 */
class CsrfToken {

    /**
     *  Flag to determine whether GET HTTP verb is checked for a token
     *
     *  Otherwise, only POST will be checked (default).
     *
     *  @access protected
     *  @var boolean
     */
    protected $acceptGet = FALSE;

    /**
     *  Default timeout for token check
     *
     *  If the request is made outside of this time frame, it will be
     *  considered invalid. This parameter can be manually overriden at check
     *  time by supplying the appropriate arugment to the {@link checkToken()}
     *  method.
     *
     *  @access protected
     *  @var integer
     */
    protected $timeout = 300;

    /**
     *  Class constructor
     *
     *  While initializing this class, it is possible to specify the {@link
     *  $timeout} parameter. The timeout is 300 seconds (5 minutes) by default.
     *  The {@link acceptGet} argument can be set to TRUE if you wish to
     *  include GET requests in the check. Otherwise, all GET requests will be
     *  considered invalid (default).
     */
    public function __construct($timeout=300, $acceptGet=FALSE){
        $this->timeout = $timeout;
        if (session_id()) {
            $this->acceptGet = (bool) $acceptGet;
        } else {
            throw new Exception('Could not find session id', 1);
        }
    }

    /**
     *  Random string gnerator
     *
     *  Utility function for random string generation of the $len length.
     *
     *  @param integer $len (defaults to 10) length of the generated string
     *  @return string
     */
    public function randomString($len = 10) {
        // Characters that may look like other characters in different fonts
        // have been omitted.
        $rString = '';
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
        $charsTotal  = strlen($chars);
        for ($i = 0; $i < $len; $i++) {
            $rInt = (integer) mt_rand(0, $charsTotal);
            $rString .= substr($chars, $rInt, 1);
        }
        return $rString;
    }

    /**
     *  Calculates the SHA1 hash from the csrf token material
     *
     *  The token material is found in $_SESSION['csrf'] array. This function
     *  is not used directly. It is called by other public CsrfToken method.
     *
     *  @see generateToken(), generateHiddenField(), checkToken()
     *  @visibility protected
     *  @return string
     */
    protected function calculateHash() {
        return sha1(implode('', $_SESSION['csrf']));
    }

    /**
     *  Generates the token string encoded using Base64 algorythm
     *
     *  When this method is called, it also resets any data in the
     *  $_SESSION['csrf'] array, so it can be called multiple times. It is not
     *  wise to call this method just before performing a chek for an earlier
     *  request, as it will overwrite any token material it finds.
     *
     *  @see generateHiddenField()
     *  @visibility public
     *  @return string
     */
    public function generateToken() {
        // Create or overwrite the csrf entry in the seesion
        $_SESSION['csrf'] = array();
        $_SESSION['csrf']['time'] = time();
        $_SESSION['csrf']['salt'] = $this->randomString(32);
        $_SESSION['csrf']['sessid'] = session_id();
        $_SESSION['csrf']['ip'] = $_SERVER['REMOTE_ADDR'];
        // Generate the SHA1 hash
        $hash = $this->calculateHash();
        // Generate and return the token
        return base64_encode($hash);
    }

    /**
     *  Generates the entire hiddent form element containing the token
     *
     *  Since Sychronize Token CSRF protection is most effective with POST
     *  requests, this convenience method allows you to generate a
     *  prefabricated hidden element that you will insert into your forms. The
     *  markup is XHTML compliant. Since it will not break regular HTML or
     *  HTML5 markup, there are no options for customization. You can use the
     *  {@link generateToken()} method if you want a custom markup, or just
     *  want the raw token string.
     *
     *  @see generateToken()
     *  @visibility public
     *  @return string
     */
    public function generateHiddenField() {
        // Shortcut method to generate the entire form
        // element containing the CSRF protection token
        $token = $this->generateToken();
        return "<input type=\"hidden\" name=\"csrf\" value=\"$token\" />";
    }

    /**
     *  Checks the timeliness of the request
     *
     *  This method is not meant to be called directly, but is called by the
     *  {@link checkToken()} method. It checks the time recorded in the session
     *  against the time of request, and returns TRUE if the request was just
     *  in time, or FALSE if the request broke the time limit.
     *
     *  @see checkToken()
     *  @visibility protected
     *  @param integer $timeout request timeout in seconds
     *  @return boolean
     */
    protected function checkTimeout($timeout=NULL) {
        if (!$timeout) {
            $timeout = $this->timeout;
        }
        return ($_SERVER['REQUEST_TIME'] - $_SESSION['csrf']['time']) < $timeout;
    }

    /**
     *  Checks the token to authenticate the request
     *
     *  The check will fail if the session wasn't started (or the session id
     *  got lost somehow), if the $_SESSION['csrf'] wasn't set (probably the
     *  form page didn't do its part in generating and using the token), if
     *  the request did not contain the 'csrf' parameter, or if the 'csrf'
     *  parameter does not match the generated from the information in the
     *  $_SESSION['csrf']. The check will also fail if the request was made
     *  outside of the time limit specified by the optional $timeout parameter
     *  or took longer than the default 5 minutes. For multi-page scenarios,
     *  or for longer forms (like blog posts and user comments) it is
     *  recommended that you manually extend the time limit to a more
     *  reasonable time frame.
     *
     *  @visibility public
     *  @param integer $timeout
     *  @return boolean
     */
    public function checkToken($timeout=NULL) {
        // Default timeout is 300 seconds (5 minutes)

        // First check if csrf information is present in the session
        if (isset($_SESSION['csrf'])) {

            // Check the timeliness of the request
            if (!$this->checkTimeout($timeout)) {
                return FALSE;
            }

            // Check if there is a session id
            if (session_id()) {
                // Check if response contains a usable csrf token
                $isCsrfGet = isset($_GET['csrf']);
                $isCsrfPost = isset($_POST['csrf']);
                if (($this->acceptGet and $isCsrfGet) or $isCsrfPost) {
                    // Decode the received token hash
                    $tokenHash = base64_decode($_REQUEST['csrf']);
                    // Generate a new hash from the data we have
                    $generatedHash = $this->calculateHash();
                    // Compare and return the result
                    if ($tokenHash and $generatedHash) {
                        return $tokenHash == $generatedHash;
                    }
                }
            }
        }

        // In all other cases return FALSE
        return FALSE;
    }

}

