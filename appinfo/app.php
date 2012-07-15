<?php
/**
* @package shorty-tracking an ownCloud url shortener plugin addition
* @category internet
* @author Christian Reiner
* @copyright 2012-2012 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information 
* @link repository https://svn.christian-reiner.info/svn/app/oc/shorty-tracking
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
 * @file appinfo/app.php
 * @brief Basic registration of plugin at ownCloud
 * @author Christian Reiner
 */

OC::$CLASSPATH['OC_Shorty_Exception']     = 'apps/shorty/lib/exception.php';
OC::$CLASSPATH['OC_Shorty_L10n']          = 'apps/shorty/lib/l10n.php';
OC::$CLASSPATH['OC_Shorty_Tools']         = 'apps/shorty/lib/tools.php';
OC::$CLASSPATH['OC_Shorty_Type']          = 'apps/shorty/lib/type.php';
OC::$CLASSPATH['OC_Shorty_Query']         = 'apps/shorty/lib/query.php';
OC::$CLASSPATH['OC_ShortyTracking_L10n']  = 'apps/shorty-tracking/lib/l10n.php';
OC::$CLASSPATH['OC_ShortyTracking_Hooks'] = 'apps/shorty-tracking/lib/hooks.php';
OC::$CLASSPATH['OC_ShortyTracking_Query'] = 'apps/shorty-tracking/lib/query.php';

// only plug into the mother app 'Shorty' if that one is installed AND has the minimum required version:
// minimim requirement currently is shorty-0.3.0
if ( OCP\App::isEnabled('shorty') )
{
  $shortyVersion = explode ( '.', OCP\App::getAppVersion('shorty') );
  if (  (3==sizeof($shortyVersion))
      &&( (0<=$shortyVersion[0])&&(3<=$shortyVersion[1])&&(0<=$shortyVersion[2])) )
  {
    OCP\Util::connectHook ( 'OC_Shorty', 'post_deleteShorty', 'OC_ShortyTracking_Hooks', 'deleteShortyClicks');
    OCP\Util::connectHook ( 'OC_Shorty', 'registerClick',     'OC_ShortyTracking_Hooks', 'registerClick');
    OCP\Util::connectHook ( 'OC_Shorty', 'registerActions',   'OC_ShortyTracking_Hooks', 'registerActions');
    OCP\Util::connectHook ( 'OC_Shorty', 'registerIncludes',  'OC_ShortyTracking_Hooks', 'registerIncludes');
  }
}
else
{
  // set global flag to be evaluated in hook 'registerActions'
  OCP\Util::connectHook ( 'OC_Shorty', 'registerActions',   'OC_ShortyTracking_Hooks', 'registerActions');
  OCP\Util::connectHook ( 'OC_Shorty', 'registerIncludes',  'OC_ShortyTracking_Hooks', 'registerIncludes');
}

?>
