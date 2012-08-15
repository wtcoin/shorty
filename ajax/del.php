<?php
/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2012 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401 
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
 * @file ajax/del.php
 * @brief Ajax method to delete an existing shorty
 * @param string id: Internal id of a referenced shorty
 * @return json: success/error state indicator
 * @return json: Key of shorty that was deleted
 * @author Christian Reiner
 */

// swallow any accidential output generated by php notices and stuff to preserve a clean JSON reply structure
OC_Shorty_Tools::ob_control ( TRUE );

//no apps or filesystem
$RUNTIME_NOSETUPFS = true;

// Sanity checks
OCP\JSON::callCheck ( );
OCP\JSON::checkLoggedIn ( );
OCP\JSON::checkAppEnabled ( 'shorty' );

try
{
	$p_id  = OC_Shorty_Type::req_argument ( 'id', OC_Shorty_Type::ID, TRUE );
	$param = array
	(
		'user' => OCP\User::getUser(),
		'id'   => $p_id,
	);
	$query = OCP\DB::prepare ( OC_Shorty_Query::URL_DELETE );
	$query->execute($param);

	// swallow any accidential output generated by php notices and stuff to preserve a clean JSON reply structure
	OC_Shorty_Tools::ob_control ( FALSE );
	OCP\Util::writeLog( 'shorty', sprintf("Deleted Shorty with id '%s'",$p_id), OC_Log::INFO );
	OCP\JSON::success ( array ( 'data'    => array('id'=>$p_id),
								'message' => OC_Shorty_L10n::t("Shorty with id '%s' deleted",$p_id) ) );
} catch ( Exception $e ) { OC_Shorty_Exception::JSONerror($e); }
?>
