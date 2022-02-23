<?php

namespace BlueSpice\ArticlePreviewCapture;

class CookieFilter {

	/**
	 * @var string
	 */
	private $cookiePrefix = '';

	/**
	 * @var array
	 */
	private $includeList = [
		// Extension:BlueSpicePrivacy
		'_MWCookieConsent',

		// Extension:Auth_remoteuser
		'304f3058RemoteToken',
		'304f3058Token',
		'304f3058UserID',
		'304f3058UserName',
		'304f3058_session',

		// MW Core
		'_session',
		'Token',
		'UserID',
		'UserName',
		'mwuser-sessionId'
	];

	/**
	 * @param string $cookiePrefix
	 */
	public function __construct( $cookiePrefix ) {
		$this->cookiePrefix = $cookiePrefix;
	}

	/**
	 * @var array
	 */
	private $filteredCookies = [];

	/**
	 * @param array $cookies
	 * @return array
	 */
	public function filter( $cookies ) {
		$this->filteredCookies = [];

		$this->filterByPrefix( $cookies );
		$this->filterByIncludeList();

		return $this->filteredCookies;
	}

	/**
	 * @param array $cookies
	 * @return void
	 */
	private function filterByPrefix( $cookies ) {
		foreach ( $cookies as $cookieName => $cookieValue ) {
			if ( strpos( $cookieName, $this->cookiePrefix ) === 0 ) {
				$this->filteredCookies[$cookieName] = $cookieValue;
			}
		}
	}

	private function filterByIncludeList() {
		$includeList = [];
		foreach ( $this->includeList as $unprefixedCookieName ) {
			$includeList[] = $this->cookiePrefix . $unprefixedCookieName;
		}
		$filteredCookies = [];
		foreach ( $this->filteredCookies as $cookieName => $cookieValue ) {
			if ( in_array( $cookieName, $includeList ) ) {
				$filteredCookies[$cookieName] = $cookieValue;
			}
		}
		$this->filteredCookies = $filteredCookies;
	}
}
