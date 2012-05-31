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
 * @file ajax/preferences.php
 * @brief Ajax method to store one or more personal preferences
 * @param backend-type (string) Identifier of chosen backend type
 * @param backend-static-base (string) Url to use as a base when the static backend is active
 * @param backend-google-key (string) Personal authentication key to use when the google backend is active
 * @param backend-bitly-key (string) Personal authentication key to use when the bit.li backend is active
 * @param backend-bitly-user (string) Personal authentication user to use when the bit.li backend is active
 * @param sms-control (string) Controls wether a 'send as sms' action should be offered is the sharing dialog
 * @param list-sort-code (string) Two character sorting key controlling the active sorting of shorty lists
 * @returns (json) success/error state indicator
 * @returns (json) Associative array holding the stored values by their key
 * @returns (json) Human readable message describing the result
 * @author Christian Reiner
 */

//no apps or filesystem
$RUNTIME_NOSETUPFS = true;

// Check if we are a user
OCP\JSON::checkLoggedIn ( );
OCP\JSON::checkAppEnabled ( 'shorty' );

try
{
  $data = array();
  switch ( $_SERVER['REQUEST_METHOD'] )
  {
    case 'POST':
      // detect provided preferences
      $data = array();
      foreach (array_keys($_POST) as $key)
        if ($type=OC_Shorty_Type::$PREFERENCE[$key])
          $data[$key] = OC_Shorty_Type::req_argument ( $key, $type, FALSE );
      // eliminate settings not explicitly set
      $data = array_diff ( $data, array(FALSE) );
      // store settings
      foreach ( $data as $key=>$val )
        OCP\Config::setUserValue( OCP\User::getUser(), 'shorty', $key, $val );
      // a friendly reply, in case someone is interested
      OCP\JSON::success ( array ( 'data'    => $data,
                                  'message' => OC_Shorty_L10n::t('Preference saved.') ) );
      break;
    case 'GET':
      // detect requested preferences
      foreach (array_keys($_GET) as $key)
      {
        if (  ('_'!=$key) // ignore ajax timestamp argument
            &&($type=OC_Shorty_Type::$PREFERENCE[$key]) )
        {
          $data[$key] = OCP\Config::getUserValue( OCP\User::getUser(), 'shorty', $key);
          // morph value into an explicit type
          switch ($type)
          {
            case OC_Shorty_Type::ID:
            case OC_Shorty_Type::STATUS:
            case OC_Shorty_Type::SORTKEY:
            case OC_Shorty_Type::SORTVAL:
            case OC_Shorty_Type::STRING:
            case OC_Shorty_Type::URL:
            case OC_Shorty_Type::DATE:
              settype ( $data[$key], 'string' );
              break;
            case OC_Shorty_Type::INTEGER:
            case OC_Shorty_Type::TIMESTAMP:
              settype ( $data[$key], 'integer' );
              break;
            case OC_Shorty_Type::FLOAT:
              settype ( $data[$key], 'float' );
              break;
            default:
          } // switch
        }
      } // foreach
      // a friendly reply, in case someone is interested
      OCP\JSON::success ( array ( 'data'    => $data,
                                  'message' => OC_Shorty_L10n::t('Preference(s) retrieved.') ) );
      break;
    default:
      throw new OC_Shorty_Exception ( "unexpected request method '%s'", $_SERVER['REQUEST_METHOD'] );
  } // switch
} catch ( Exception $e ) { OC_Shorty_Exception::JSONerror($e); }
?>
