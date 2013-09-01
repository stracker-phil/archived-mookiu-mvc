<?php

/**
 * Access to user-entries in wp_users table
 */

if ( $_SERVER[ 'SCRIPT_FILENAME' ] == __FILE__ ) die( 'Access denied.' );

if ( ! class_exists('MOK_ModelUsers') ) :
	/**
	 * The Users model provides interface to the WordPress users.
	 *
	 * @since 1.0
	 */
	class MOK_ModelUsers extends MOK_Model {

		/**
		 * Setup the users model
		 *
		 * @since 1.0
		 */
		protected function setup() {
			// Specifies, which Item is returned by the model (item = active record)
			$this->itemname = 'user';

			// This defines which database table is queried
			$this->tablename = self::get_db_connection()->users;

			// Link the users table with the usermeta table to easily fetch metainformation for users
			$this->metatable( self::get_db_connection()->usermeta, 'user_id', 'meta_key', 'meta_value' );

			parent::setup();
		}


		/**
		 * Validates the authentication credentials and returns "OK" or an error code
		 *
		 * Possible results
		 * "OK" .. Authentication is successfull, the user_id is valid
		 * "ERR:Invalid timestamp" .. The timestamp could not be parsed
		 * "ERR:Expired" .. The timestamp is more than 5 minutes from the current server timestamp
		 * "ERR:User unknown" .. The user_id does not exist
		 * "ERR:User not active" .. The user_id is not activated
		 * "ERR:Failed" .. The auth_hash does not match the users auth-hash
		 *
		 * @since 1.0
		 *
		 * @param integer $user_id The WordPress user_id (http header "mookiu_user")
		 * @param string $auth_hash The complete authentication hash (http header "mookiu_auth")
		 * @param string $timestmap The timestamp of the client (http header "mookiu_stamp"; GMT, format "2013-08-31 22:12:37")
		 * @return string OK or error code
		 */
		public function api_auth( $user_id, $auth_hash, $timestamp ) {
			// Timestamp needs to be in format "9999-99-99 99:99:99"
			if ( 1 !== preg_match( '/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $timestamp, $segments ) )
				return 'ERR:Invalid timestamp';

			// Parse the timestamp parts into a valid timestamp
			$ts_valid = mktime( $segments[4], $segments[5], $segments[6], $segments[2], $segments[3], $segments[1] );

			$now = time();
			$diff = abs( $ts_valid-$now );

			// Allow a 300-second margin between the client timestamp and the server timestamp
			if ( $diff > 300 )
				return 'ERR:Expired';

			// Get the user with the specified user_id
			$user = $this->load( $user_id );

			// Make sure we found a valid user
			if ( false === $user )
				return 'ERR:User unknown';

			// Confirm that the user is active. Otherwise bail out
			if ( '0' != $user->get('user_status') )
				return 'ERR:User not active';

			// Get the value for the auth-hash from the meta values
			$auth_key = $user->get_meta('auth_key');
			$plain_hash = $user_id . ':' . $auth_key . ':' . $timestamp;
			$check_hash = $plain_hash;  // TODO: CHange this to md5( $plain_hash )

			if ( $check_hash !== $auth_hash )
				return 'ERR:Failed';

			return 'OK';
		}

	};


endif;
