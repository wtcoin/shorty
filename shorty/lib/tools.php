<?php
/**
* @package shorty an ownCloud url shortener plugin
* @category internet
* @author Christian Reiner
* @copyright 2011-2015 Christian Reiner <foss@christian-reiner.info>
* @license GNU Affero General Public license (AGPL)
* @link information http://apps.owncloud.com/content/show.php/Shorty?content=150401
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
 * @file lib/tools.php
 * A collection of general utility routines
 * @author Christian Reiner
 */

/**
 * @class OC_Shorty_Tools
 * @brief Collection of a few practical routines, a tool box
 * @access public
 * @author Christian Reiner
 */
class OC_Shorty_Tools
{
	// length of a quasi random alphabet to be created
	const RANDOM_ALPHABET_LENGTH = 62;
	// internal flag indicating if output buffering should be used to prevent accidentially output during ajax requests
	static $ob_usage  = TRUE;
	// internal flag indicating if there is currently an output buffer active
	static $ob_active = FALSE;

	/**
	 * @method OC_Shorty_Tools::ob_control
	 * @param bool $switch Whether to activate or deactivate the buffer
	 * @return NULL|string: NULL when starting buffering, buffered content when stopping buffering
	 * @access public
	 * @author Christian Reiner
	 */
	static function ob_control ( $switch=TRUE )
	{
		$output = NULL;
		@ob_implicit_flush ( FALSE );
		@ob_start ( );
		self::$ob_active = TRUE;

		if ( self::$ob_usage )  {
			// attempt to use outpout buffering
			if ( TRUE===$switch )  {
				// start buffering if possible and not yet started before
				if (   function_exists('ob_start')       // output buffers installed at all ?
					&& ! self::$ob_active ) { // don't stack buffers (create buffer only, if not yet started)
					@ob_implicit_flush ( FALSE );
					@ob_start ( );
					self::$ob_active = TRUE;
				}
			} else {
				// end buffering _if_ it has been started before
				if (self::$ob_active) {
					$output = @ob_get_contents();
					@ob_end_clean();
					self::$ob_active = FALSE;
				}
			}
		} // if ob_usage
		return $output;
	} // function ob_control

	/**
	 * @method OC_Shorty_Tools::db_escape
	 * @brief Escape a value for incusion in db statements
	 * @param string value: Value to be escaped
	 * @return string: Escaped string value
	 * @throws OC_Shorty_Exception In case of an unknown database engine
	 * @access public
	 * @author Christian Reiner
	 * @todo use mdb2::quote() / mdb2:.escape() instead ?
	 */
	static function db_escape ( $value )
	{
		$type = OCP\Config::getSystemValue ( 'dbtype', 'sqlite' );
		switch ( $type )
		{
			case 'sqlite':
			case 'sqlite3':
				return sqlite_escape_string     ( $value );

			case 'pgsql':
				return pg_escape_string         ( $value );

			case 'mysql':
				if (get_magic_quotes_gpc())
					return mysql_real_escape_string ( stripslashes($value) );
				else return mysql_real_escape_string ( $value );
		} // switch
		throw new OC_Shorty_Exception ( "unknown database backend type '%1'", array($type) );
	} // function db_escape

	/**
	 * @method OC_Shorty_Tools::db_timestamp
	 * @brief Current timestamp as required by db engine
	 * @return string: Current timestamp as required by db engine
	 * @throws OC_Shorty_Exception In case of an unknown database engine
	 * @access public
	 * @author Christian Reiner
	 * @todo not really required any more, we rely on CURRENT_TIMESTAMP instead
	 */
	static function db_timestamp ( )
	{
		$type = OCP\Config::getSystemValue( "dbtype", "sqlite" );
		switch ( $type )
		{
			case 'sqlite':
			case 'sqlite3':
				return "strftime('%s','now')";

			case 'mysql':
				return 'UNIX_TIMESTAMP()';

			case 'pgsql':
				return "date_part('epoch',now())::integer";
		}
		throw new OC_Shorty_Exception ( "unknown database backend type '%1'", array($type) );
	} // function db_timestamp

	/**
	 * @method OC_Shorty_Tools::shorty_id
	 * @brief Creates a unique id to be used for a new shorty entry
	 * @return string: Valid and unique id
	 * @access public
	 * @author Christian Reiner
	 */
	static function shorty_id ( )
	{
		// use pseudo random alphabet to generate a id being unique over time
		return self::convertToAlphabet ( str_replace(array(' ','.'),'',microtime()), self::randomAlphabet() );
	} // function shorty_id

	/**
	 * @method randomAlphabet
	 * @brief returns a quasi random alphabet, unique but static for an installation
	 * @access public
	 * @author Christian Reiner
	 */
	static function randomAlphabet ()
	{
		$alphabet = OCP\Config::getAppValue ( 'shorty', 'id-alphabet' );
		if ( empty($alphabet) )
		{
			$c = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxwz0123456789";
			$alphabet = substr ( str_shuffle($c), 0, self::RANDOM_ALPHABET_LENGTH );
			OCP\Config::setAppValue ( 'shorty', 'id-alphabet', $alphabet ) ;
		}
		return $alphabet;
	} // function randomAlphabet

	/**
	 * @method OC_Shorty_Tools::convertToAlphabet
	 * @brief Converts a given decimal number into an arbitrary base (alphabet)
	 * @param integer number: Decimal numeric value to be converted
	 * @return string: Converted value in string notation
	 * @access public
	 * @author Christian Reiner
	 */
	static function convertToAlphabet ( $number, $alphabet )
	{
		$alphabetLen = strlen($alphabet);
		if ( is_numeric($number) )
			$decVal = $number;
		else throw new OC_Shorty_Exception ( "non numerical timestamp value: '%1'", array($number) );
			$number = FALSE;
		$nslen = 0;
		$pos = 1;
		while ($decVal > 0)
		{
			$valPerChar = pow($alphabetLen, $pos);
			$curChar = floor($decVal / $valPerChar);
			if ($curChar >= $alphabetLen)
			{
				$pos++;
			} else {
				$decVal -= ($curChar * $valPerChar);
				if ($number === FALSE)
				{
				$number = str_repeat($alphabet{1}, $pos);
				$nslen = $pos;
				}
				$number = substr($number, 0, ($nslen - $pos)) . $alphabet{(int)$curChar} . substr($number, (($nslen - $pos) + 1));
				$pos--;
			} // else
		} // while
		if ($number === FALSE) $number = $alphabet{1};
			return $number;
	} // function convertToAlphabet

	/**
	 * @method OC_Shorty_Tools::getSubjectHash
	 * @brief Hashes a given string using the installation specific alphabet as salt
	 * @param $subject
	 * @return string The hashed subject
	 * @throws OC_Shorty_Exception
	 */
	static public function getSubjectHash ( $subject )
	{
		$alphabet = self::randomAlphabet();
		$salt = substr($alphabet, 0, CRYPT_SALT_LENGTH);
		$hash = crypt($subject, $salt);
		if( !$alphabet || !$hash ) {
			throw new OC_Shorty_Exception ( "failed to create a usable hash, check your system setup!" );
		}
		return $hash;
	} // function getSubjectHash

		/**
		 * @method OC_Shorty_Tools::checkSubjectHash
		 * @brief Checks if a given hashes matches a given subject
		 * @param $subject
		 * @return string The hashed subject
		 * @throws OC_Shorty_Exception
		 */
		static public function checkSubjectHash ( $subject, $hash )
	{
		return ( OC_Shorty_Tools::getSubjectHash($subject) === $hash );
	} // function checkSubjectHash

	/**
	 * @method OC_Shorty_Tools::proxifyReference
	 * @brief Creates a reference to the internal proxy feature
	 * @param string $subject: The subject to be handed over as reference query 'id'
	 * @param bool $hash: Whether to create an additional hash inside the created reference
	 * @return string
	 * @throws OC_Shorty_Exception
	 * @access public
	 * @author Christian Reiner
	 */
	static public function proxifyReference ( $mode, $subject, $hash=false )
	{
		if ( ! in_array($mode, array('favicon')))
			return false;
		if ($hash)  {
			return sprintf('%s?mode=%s&subject=%s&hash=%s', OCP\Util::linkToAbsolute('shorty', 'proxy.php'), $mode, urlencode($subject), self::getSubjectHash($subject));
		} else {
			return sprintf('%s?mode=%s&subject=%s', OCP\Util::linkToAbsolute('shorty', 'proxy.php'), $mode, urlencode($subject));
		}
	} // function proxifyReference

	/**
	 * @method OC_Shorty_Tools::deproxifyReference
	 * @brief Extracts the target url from a reference to the internal proxy feature
	 * @param string $reference: The reference to the internal proxy feature
	 * @return string The extracted target url or false
	 * @access public
	 * @author Christian Reiner
	 */
	static public function deproxifyReference ( $reference )
	{;
		$pattern = sprintf( '/^%s%s(.+)%s(.+)$/',
			preg_quote(OCP\Util::linkToAbsolute('shorty', 'proxy.php'), '/'),
			preg_quote('?mode=favicon&subject=', '/'),
			preg_quote('&hash=', '/')
		);
		if ( ! preg_match($pattern, $reference, $token)) {
			return false;
		}
		$subject = &$token[1];
		$hash    = &$token[2];
		if ( ! self::checkSubjectHash($subject, $hash) ) {
			return false;
		}
		return $subject;
	} // function deproxifyReference

	/**
	 * @method OC_Shorty_Tools::relayUrl
	 * @brief Generates a relay url for a given id acting as a href target for all backends
	 * @param string id: Shorty id as shorty identification
	 * @return string: Generated absolute relay url
	 * @access public
	 * @author Christian Reiner
	 */
	static function relayUrl ($id)
	{
		return sprintf ( '%s?service=%s&id=%s', OCP\Util::linkToAbsolute("", "public.php"), 'shorty_relay', $id );
	} // function relayUrl

	/**
	 * @method OC_Shorty_Tools::countShortys
	 * @brief Returns the total number of entries and clicks from the database
	 * @return array: Two elements sum_shortys & sum_clicks holding an integer each
	 * @access public
	 * @author Christian Reiner
	 */
	static function countShortys ()
	{
		$param = array
		(
			':user'   => OCP\User::getUser ( ),
		);
		$query = OCP\DB::prepare ( OC_Shorty_Query::URL_COUNT );
		$result = $query->execute($param);
		$reply = $result->fetchAll();
		return $reply[0];
	} // function countShortys

	/**
	 * @method OC_Shorty_Tools::versionCompare
	 * @brief Compares a given version (string notation) with the running ownCloud version
	 * @return integer the major version number
	 * @access public
	 * @author Christian Reiner
	 * @description
	 * The major version of the OC framework is relevant for a few compatibility issues.
	 * It has to be checked against often when for example rendering templates, to add or suppres version dependant options.
	 */
	static function versionCompare ($operator,$cpVersion)
	{
		$ocVersion = implode('.',OCP\Util::getVersion());
		return (version_compare($ocVersion,$cpVersion,$operator));
	} // function versionCompare

	/**
	 * @method OC_Shorty_Tools::toBoolean
	 * @brief Propper conversion of a value to boolean
	 * @param value boolean some value to be casted to boolean
	 * @return boolean the casted boolean value or NULL
	 * @access public
	 * @author Christian Reiner
	 */
	static function toBoolean ( $value, $strict=FALSE )
	{
		if ( is_bool($value) )
			return $value;
		switch ( strtolower(trim($value)) )
		{
			case 1:
			case '1':
			case 'true':
				return TRUE;
			case 0:
			case '0':
			case 'false':
				return FALSE;
			default:
				if ( $strict)
					return NULL;
				else
					return FALSE;
		} // switch
	} // function toBoolean

	/**
	 * @method OC_Shorty_Tools::idnToASCII
	 * @brief Converts an idn url to its ascii idn notation
	 * @param $url string Some arbitrary url
	 * @return string The ascii idn notation of the url
	 * @access public
	 * @author Christian Reiner
	 */
	static function idnToASCII ( $url )
	{
		$url = parse_url($url);

		$scheme   = &$url['scheme'];
		$host     = &$url['host'];
		$port     = &$url['port'];
		$user     = &$url['user'];
		$pass     = &$url['pass'];
		$path     = &$url['path'];
		$query    = &$url['query'];
		$fragment = &$url['fragment'];

		if ( function_exists('idn_to_ascii') )
			$host = idn_to_ascii($host);
// TODO: add local implemented conversion function in case php module is missing

		return sprintf('%s://%s%s%s%s%s%s',
			$scheme,
			empty($user)&&empty($pass) ? '' : $user.':'.'@',
			$host,
			empty($port) ? '' : ':'.$port,
			$path,
			empty($query) ? '' : '?'.$query,
			empty($fragment) ? '' : '#'.$fragment);
	} // function idnToASCII

	/**
	 * @method OC_Shorty_Tools::idnToUTF8
	 * @brief Converts an idn url to its unicode notation
	 * @param $url string An idn url
	 * @return string The unicode notation of the url
	 * @access public
	 * @author Christian Reiner
	 */
	static function idnToUTF8 ( $url )
	{
		if ( function_exists('idn_to_utf8') )
			return idn_to_utf8($url);
		else return $url;
	} // function idnToUTF8

} // class OC_Shorty_Tools
