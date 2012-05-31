<?php
/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2012 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information 
* @link repository https://svn.christian-reiner.info/svn/app/oc/shorty
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the license, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.
* If not, see <http://www.gnu.org/licenses/>.
*
*/

/**
 * @file lib/exception.php
 * Application specific exception class
 * @author Christian Reiner
 */

/**
 * @class OC_Shorty_Exception
 * @brief Application specific exception class
 * @access public
 * @author Christian Reiner
 */
class OC_Shorty_Exception extends Exception
{
  protected $phrase = '';
  protected $param  = array ( );

  /**
   * @method OC_Shorty_Exception::__construct
   * @brief: Constructs an exception based on a phrase and a set of parameters
   * @param phrase (string) Human readable message that should be translatable
   * @param param  (array) Set of parameters to be used as sprintf arguments to fill the phrase
   * @access public
   * @author Christian Reiner
   */
  public function __construct ( $phrase, $param=array() )
  {
    if ( is_array($param) )
         $this->param = $param;
    else $this->param = array($param);
    $this->phrase  = $phrase;
//    $this->message = vsprintf ( $phrase, $this->params );
    Exception::__construct ( vsprintf($phrase,$this->param), 1 );
  }

  /**
   * @method OC_Shorty_Exception::getTranslation
   * @brief: Returns the translated message of the exception
   * @returns (string) Translated message including the filled in set of arguments
   * @access public
   * @author Christian Reiner
   */
  public function getTranslation ( )
  {
    return OC_Shorty_L10n::t ( $this->phrase, $this->param );
  }

  /**
   * @method OC_Shorty_Exception::JSONerror
   * @brief Calls OCP\JSON::error with a pretty formated version of an exception
   * @param e (exception) an exception object holding information
   * @returns (json) OCP\JSON::error
   * @access public
   * @author Christian Reiner
   */
  static function JSONerror ( $e )
  {
    $title = OC_Shorty_L10n::t("Exception");
    switch ( get_class($e) )
    {
      case 'OC_Shorty_Exception':
        $message = $e->getTranslation();
        break;
      case 'PDOException':
        $message = sprintf ( OC_Shorty_L10n::t( "%s\nMessage(code): %s (%s)\nFile(line): %s (%s)\nInfo: %%s",
                                                OC_Shorty_L10n::t("Exception (%s)", get_class($e)),
                                                htmlspecialchars($e->getMessage()),
                                                htmlspecialchars($e->getCode()),
                                                htmlspecialchars($e->getFile()),
                                                htmlspecialchars($e->getLine()) ),
                             (method_exists($e,'errorInfo') ? trim($e->errorInfo()) : '-/-') );
        break;
      default:
        if ( is_a($e,'Exception') )
             $message = OC_Shorty_L10n::t("Unexpected type of exception caught: %s", get_class($e));
        else $message = OC_Shorty_L10n::t("Unknown object of type caught: %s", get_class($e));
    } // switch
    return OCP\JSON::error ( array ( 'title'   => $title,
                                     'message' => sprintf("%s: %s", $title, $message) ) );
  } // function error
} // class OC_Shorty_Exception

/**
 * @class OC_Shorty_HttpException
 * @brief Application specific exception class: protocol layer
 * @access public
 * @author Christian Reiner
 */
class OC_Shorty_HttpException extends OC_Shorty_Exception
{

  /**
   * @method OC_Shorty_HttpException::__construct
   * @brief: Constructs an exception based on a phrase and a set of parameters
   * @param status (integer) Http status code
   * @access public
   * @author Christian Reiner
   */
  public function __construct ( $status )
  {
    if (   is_numeric($status)
        && array_key_exists($status,OC_Shorty_Type::$HTTPCODE) )
    {
      $status = intval($status);
      $phrase = OC_Shorty_Type::$HTTPCODE[$status];
    }
    else
    {
      $status = 400;
      $phrase = OC_Shorty_Type::$HTTPCODE[400]; // "Bad Request"
    } // switch

    // return http status code to client (browser)
    if ( ! headers_sent() )
    {
      header ( sprintf("HTTP/1.0 %s %s",$status,$phrase) );
    }
    $tmpl = new OCP\Template("shorty", "tmpl_http_status", "guest");
    $tmpl->assign("explanation", OC_Shorty_L10n::t($phrase));
    $tmpl->printPage();
    exit;
  } // function __construct

} // class OC_Shorty_HttpException

?>
