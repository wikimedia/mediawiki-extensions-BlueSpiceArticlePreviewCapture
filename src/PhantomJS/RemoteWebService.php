<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use MediaWiki\Http\HttpRequestFactory;
use MediaWiki\MediaWikiServices;

class RemoteWebService implements IPhantomJS {

	/**
	 * PhantomJS backend endpoint
	 * @var string
	 */
	private $phantomJSServiceURL;

	/** @var HttpRequestFactory */
	private $httpRequestFactory;

	/**
	 * @return RemoteWebService
	 * @throws \ConfigException
	 */
	public static function factory() {
		$phantomJSServiceURL = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSServiceURL' );

		return new self( $phantomJSServiceURL, MediaWikiServices::getInstance()->getHttpRequestFactory() );
	}

	/**
	 * RemoteWebService constructor.
	 * @param string $phantomJSServiceURL
	 * @param HttpRequestFactory $httpRequestFactory
	 */
	public function __construct( $phantomJSServiceURL, HttpRequestFactory $httpRequestFactory ) {
		$this->phantomJSServiceURL = $phantomJSServiceURL;
		$this->httpRequestFactory = $httpRequestFactory;
	}

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return bool|string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies ) {
		$this->logger->debug( "Creating preview image for $url" );
		$es = htmlspecialchars_decode( $url );
		$queryParams = [
			'url' => $baseUrl . $es,
			'cookies' => $cookies
		];

		$requestUrl = $this->phantomJSServiceURL . '/?' . http_build_query( $queryParams );

		$this->logger->debug( "Calling '$requestUrl'" );
		$fileData = $this->httpRequestFactory->get(
			$requestUrl, [ 'sslVerifyHost' => false, 'sslVerifyCert' => false ]
		);
		$this->logger->debug( "Response: '$fileData'" );

		return $fileData ?? false;
	}
}
