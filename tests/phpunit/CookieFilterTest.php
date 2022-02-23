<?php

namespace BlueSpice\ArticlePreviewCapture\Tests;

use BlueSpice\ArticlePreviewCapture\CookieFilter;
use PHPUnit\Framework\TestCase;

class CookieFilterTest extends TestCase {

	/**
	 * @covers BlueSpice\ArticlePreviewCapture\CookieFilter::filter
	 */
	public function testFilter() {
		$cookies = json_decode( file_get_contents( __DIR__ . '/data/cookies.json' ), true );
		$cookieFilter = new CookieFilter( 'sfr_60366cab343d' );

		$expectedCookies = [
			'sfr_60366cab343dUserName' => 'JDoe',
			'sfr_60366cab343dUserID' => '2',
			'sfr_60366cab343dToken' => '47fae40ba5293346d2c6d1a14df9cf1b',
			'sfr_60366cab343d_session' => 'qmsoruqlnkr3346d2jnl8luk5tkd9qcm'
		];

		$this->assertEquals( $expectedCookies, $cookieFilter->filter( $cookies ) );
	}
}
