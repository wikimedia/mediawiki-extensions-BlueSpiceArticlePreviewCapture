<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class RemoteWebService implements IPhantomJS, LoggerAwareInterface {

	/**
	 * PhantomJS backend endpoint
	 * @var string
	 */
	private $phantomJSServiceURL;
	/** @var LoggerInterface */
	private $logger;

	/**
	 * @return RemoteWebService
	 * @throws \ConfigException
	 */
	public static function factory() {
		$phantomJSServiceURL = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSServiceURL' );

		return new self( $phantomJSServiceURL );
	}

	/**
	 * RemoteWebService constructor.
	 * @param string $phantomJSServiceURL
	 */
	public function __construct( $phantomJSServiceURL ) {
		$this->phantomJSServiceURL = $phantomJSServiceURL;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return void
	 */
	public function setLogger( LoggerInterface $logger ): void {
		$this->logger = $logger;
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
		$fileData = \Http::get( $requestUrl, [ 'sslVerifyHost' => false, 'sslVerifyCert' => false ] );
		$this->logger->debug( "Response: '$fileData'" );

		return $fileData;
	}
}
