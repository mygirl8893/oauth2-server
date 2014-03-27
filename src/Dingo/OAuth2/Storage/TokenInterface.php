<?php namespace Dingo\OAuth2\Storage;

interface TokenInterface {

	/**
	 * Insert a token into storage. The expires time is a UNIX timestamp and should
	 * be saved in a compatible format. When it's pulled from storage is should
	 * be returned as a UNIX timestamp.
	 * 
	 * Example MySQL query:
	 * 
	 * INSERT INTO oauth_tokens (token, type, client_id, user_id, expires) 
	 * VALUES (:token, :type, :client_id, :user_id, :expires)
	 * 
	 * @param  string  $token
	 * @param  string  $type
	 * @param  string  $clientId
	 * @param  mixed  $userId
	 * @param  int  $expires
	 * @return \Dingo\OAuth2\Entity\Token
	 */
	public function create($token, $type, $clientId, $userId, $expires);

}