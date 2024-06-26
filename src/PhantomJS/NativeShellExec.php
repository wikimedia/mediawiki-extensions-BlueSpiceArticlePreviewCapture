<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use MediaWiki\MediaWikiServices;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class NativeShellExec implements IPhantomJS, LoggerAwareInterface {

	/**
	 * phantomjs local path
	 * @var string
	 */
	private $phantomJSExe;
	/** @var string */
	private $phantomJSOptions;
	/** @var string */
	private $captureFile;
	/** @var string */
	private $escapeShellCommand;
	/** @var string */
	private $scriptPath;
	/** @var LoggerInterface */
	protected $logger;

	/**
	 * @return NativeShellExec
	 * @throws \ConfigException
	 */
	public static function factory() {
		$phantomJSExe = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSExecutable' );

		$phantomJSOptions = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSOptions' );

		$captureFile = $GLOBALS[ 'wgExtensionDirectory' ]
			. "/BlueSpiceArticlePreviewCapture/webservices/render.js";

		$escapeShellCommand = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ArticlePreviewCapturePhantomJSEscapeShellCommand' );

		$scriptPath = MediaWikiServices::getInstance()->getConfigFactory()
			->makeConfig( 'bsg' )->get( 'ScriptPath' );

		return new static( $phantomJSExe, $phantomJSOptions, $captureFile,
			$escapeShellCommand, $scriptPath );
	}

	/**
	 * NativeShellExec constructor.
	 * @param string $phantomJSExe
	 * @param string $phantomJSOptions
	 * @param string $captureFile
	 * @param string $escapeShellCommand
	 * @param string $scriptPath
	 */
	public function __construct( $phantomJSExe, $phantomJSOptions, $captureFile,
								 $escapeShellCommand, $scriptPath ) {
		$this->phantomJSExe = $phantomJSExe;
		$this->phantomJSOptions = $phantomJSOptions;
		$this->captureFile = $captureFile;
		$this->escapeShellCommand = $escapeShellCommand;
		$this->scriptPath = $scriptPath;
		$this->logger = new NullLogger();
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
		$command = $this->getCommandArguments( $url, $baseUrl, $cookies );
		$escapedCommand = [];
		$i = 0;
		foreach ( $command as $commandPart ) {
			$i++;
			if ( $i === 1 && !$this->escapeShellCommand ) {
				$escapedCommand[] = $commandPart;
				continue;
			}
			$escapedCommand[] = escapeshellarg( $commandPart );
		}
		$out = [];
		$cmd = implode( ' ', $escapedCommand );
		$this->logger->debug( "Running command '$cmd'" );

		// wfShellExec will fail due to memory limits
		if ( wfIsWindows() ) {
			exec( $cmd, $out );
			$base64Data = implode( "\n", $out );
		} else {
			$base64Data = exec( $cmd );
		}

		$this->logger->debug( "Response: '$base64Data'" );
		return base64_decode( $base64Data );
	}

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return array
	 */
	protected function getCommandArguments( $url, $baseUrl, $cookies ) {
		$urlUtils = MediaWikiServices::getInstance()->getUrlUtils();
		$parsedBaseUrl = $urlUtils->parse( $baseUrl );

		$cookieDomain = $parsedBaseUrl[ 'host' ];
		$cookiePath = $this->scriptPath;

		$command = [];
		$command[] = $this->phantomJSExe;
		foreach ( $this->phantomJSOptions as $option ) {
			$command[] = $option;
		}

		$command = array_merge(
			$command,
			[ $this->captureFile, $baseUrl . $url, $cookies, $cookieDomain, $cookiePath ]
		);

		return $command;
	}
}
