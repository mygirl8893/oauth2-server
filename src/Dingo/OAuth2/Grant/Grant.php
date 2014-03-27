<?php namespace Dingo\OAuth2\Grant;

use Dingo\OAuth2\Token;
use Dingo\OAuth2\ScopeValidator;
use Dingo\OAuth2\Storage\Adapter;
use Dingo\OAuth2\Entity\Token as TokenEntity;
use Symfony\Component\HttpFoundation\Request;

abstract class Grant implements GrantInterface {

	/**
	 * Storage adapter instance.
	 * 
	 * @var \Dingo\OAuth2\Storage\Adapter
	 */
	protected $storage;

	/**
	 * Symfony request instance.
	 * 
	 * @var \Symfony\Component\HttpFoundation\Request
	 */
	protected $request;

	/**
	 * Scope validator instance.
	 * 
	 * @var \Dingo\OAuth2\ScopeValidator
	 */
	protected $scopeValidator;

	/**
	 * Token expiration time.
	 * 
	 * @var int
	 */
	protected $tokenExpiration;

	/**
	 * Validate a confidential client by checking the client ID, secret, and any
	 * redirection URI that was given.
	 * 
	 * @return \Dingo\OAuth2\Entity\Client
	 */ 
	protected function validateConfidentialClient()
	{
		// Grab the redirection URI from the post data if there is one. This is
		// sent along when validating a client for some grant types. It doesn't
		// matter if we send along a "null" value though.
		$redirectUri = $this->request->request->get('redirect_uri');

		// If the "Authorization" header exists within the request then we will
		// attempt to pull the clients ID and secret from there.
		if ($this->request->headers->has('authorization'))
		{
			$id = $this->request->getUser();

			$secret = $this->request->getPassword();
		}

		// Otherwise we'll default to pulling the clients ID and secret from the
		// requests post data. It's preferred if clients use HTTP basic.
		else
		{
			$id = $this->request->request->get('client_id');

			$secret = $this->request->request->get('client_secret');
		}

		// If we have a client ID and secret we'll attempt to verify the client by
		// grabbing its details from the storage adapter.
		if (($id and $secret) and $client = $this->storage->get('client')->get($id, $secret, $redirectUri))
		{
			return $client;
		}

		throw new \Exception('invalid_client');
	}

	/**
	 * Validate the requested scopes.
	 * 
	 * @return array
	 */
	protected function validateScopes()
	{
		return $this->scopeValidator->validate();
	}

	/**
	 * Generate a new token.
	 * 
	 * @return string
	 */
	protected function generateToken()
	{
		return Token::make();
	}

	/**
	 * Build and return the token response.
	 * 
	 * @param  \Dingo\OAuth2\Entity\Token  $token
	 * @return array
	 */
	protected function response(TokenEntity $token)
	{
		return [
			'access_token' => $token->getToken(),
			'token_type'   => 'Bearer',
			'expires'      => $token->getExpires(),
			'expires_in'   => $this->getTokenExpiration()
		];
	}

	/**
	 * Set the storage adapter instance.
	 * 
	 * @param  \Dingo\OAuth2\Storage\Adapter  $storage
	 * @return \Dingo\OAuth2\Grant\Grant
	 */
	public function setStorage(Adapter $storage)
	{
		$this->storage = $storage;

		return $this;
	}

	/**
	 * Set the symfony request instance.
	 * 
	 * @param  \Symfony\Component\HttpFoundation\Request  $request
	 * @return \Dingo\OAuth2\Grant\Grant
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;

		return $this;
	}

	/**
	 * Set the scope validator instance.
	 * 
	 * @param  \Dingo\OAuth2\ScopeValidator  $scopeValidator
	 * @return \Dingo\OAuth2\Grant\Grant
	 */
	public function setScopeValidator(ScopeValidator $scopeValidator)
	{
		$this->scopeValidator = $scopeValidator;

		return $this;
	}

	/**
	 * Set the token expiration time in seconds.
	 * 
	 * @param  int  $expires
	 * @return \Dingo\OAuth2\Grant\Grant
	 */
	public function setTokenExpiration($expires)
	{
		$this->tokenExpiration = $expires;

		return $this;
	}

	/**
	 * Get the token expiration time in seconds.
	 * 
	 * @return int
	 */
	public function getTokenExpiration()
	{
		return $this->tokenExpiration;
	}

}