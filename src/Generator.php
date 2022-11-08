<?php

namespace BlueSpice\ArticlePreviewCapture;

use BlueSpice\ArticlePreviewCapture\PhantomJS\IPhantomJS;
use File;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;

class Generator {
	public const FILE_PREFIX = 'revision_';
	public const PARAM_OVERWRITE = 'overwrite';
	public const PARAM_HEIGHT = 'height';
	public const PARAM_WIDTH = 'width';

	/**
	 *
	 * @var \Config
	 */
	protected $config = null;

	/**
	 * @var \WebRequest
	 */
	protected $request = null;

	/**
	 *
	 * @var string
	 */
	protected $lockFile = '';

	/**
	 *
	 * @param \Config $config
	 * @param \WebRequest $request
	 */
	public function __construct( $config, $request ) {
		$this->config = $config;
		$this->request = $request;
	}

	/**
	 *
	 * @param RevisionRecord $revision
	 * @param array $params
	 * @return \ThumbnailImage|\MediaTransformOutput|bool
	 */
	public function generate( RevisionRecord $revision, array $params = [] ) {
		$file = $this->getFileFromRevision( $revision );
		if ( !$file ) {
			throw new \MWException(
				'FATAL: ArticlePreviewCapture repo error!'
			);
		}

		if ( !$file->exists() || isset( $params[ static::PARAM_OVERWRITE ] ) ) {
			$file = $this->createCapture( $file, $revision );
			$this->waitForFileExists( $file );
		}

		if ( empty( $params[ static::PARAM_WIDTH ] ) ) {
			$params[ static::PARAM_WIDTH ] = 100;
		}
		if ( !empty( $params[ static::PARAM_HEIGHT ] ) ) {
			unset( $params[ static::PARAM_HEIGHT ] );
		}
		// Do this first or file transform only returns the path to the temp dir
		$thumb = $file->createThumb( $params[ static::PARAM_WIDTH ] );
		return $file->transform( $params );
	}

	/**
	 * Gets file from revision
	 * @param RevisionRecord $revision
	 * @return bool|File
	 */
	public function getFileFromRevision( RevisionRecord $revision ) {
		return \BsFileSystemHelper::getFileFromRepoName(
			static::FILE_PREFIX . $revision->getId() . ".png",
			'ArticlePreviewCapture'
		);
	}

	/**
	 * Captures a screenshot of a given revision from a page.
	 *
	 * @param File &$file
	 * @param RevisionRecord $revision
	 * @return bool|File
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	protected function createCapture( File &$file, RevisionRecord $revision ) {
		$captureFile = $GLOBALS[ 'wgExtensionDirectory' ]
			. "/BlueSpiceArticlePreviewCapture/webservices/render.js";

		$status = \BsFileSystemHelper::ensureDataDirectory(
			'ArticlePreviewCapture'
		);

		if ( !$status->isOK() ) {
			throw new \MWException(
				'FATAL: ArticlePreviewCapture repo folder could no be created!'
			);
		}

		$statusCache = \BsFileSystemHelper::ensureCacheDirectory(
			'ArticlePreviewCapture'
		);

		if ( !$statusCache->isOK() ) {
			throw new \MWException(
				'FATAL: ArticlePreviewCapture cache folder could no be created!'
			);
		}

		$fileName = $file->getName();
		$cacheDir = $statusCache->getValue();
		$this->lockFile = "$cacheDir/$fileName.lock";

		if ( !file_exists( $this->lockFile ) ) {
			// create lock file
			touch( $this->lockFile );

			return $this->execute( $revision, $captureFile, $fileName );
		}

		$file = null;

		if ( file_exists( $this->lockFile ) ) {
			unlink( $this->lockFile );
		}

		return \BsFileSystemHelper::getFileFromRepoName(
			$fileName,
			'ArticlePreviewCapture'
		);
	}

	/**
	 * @param RevisionRecord $revision
	 * @param string $captureFile
	 * @param string $fileName
	 * @return mixed
	 * @throws \ConfigException
	 * @throws \MWException
	 */
	public function execute( RevisionRecord $revision, $captureFile, $fileName ) {
		$url = \Title::newFromID( $revision->getPageId() )->getLocalURL( [
			'notemplatecache' => true,
			'screenshot' => true,
			'oldid' => $revision->getId()
		] );

		$baseUrl = $this->config->get( 'ArticlePreviewCapturePhantomJSBaseUrl' );

		if ( $baseUrl === null ) {
			$baseUrl = $this->config->get( 'Server' );
		}

		$cookieFilter = new CookieFilter( $this->config->get( 'CookiePrefix' ) );
		$filteredCookies = $cookieFilter->filter( $_COOKIE );

		$cookies = base64_encode( \FormatJson::encode( $filteredCookies ) );

		/** @var IPhantomJS $phantomJS */
		$phantomJS = MediaWikiServices::getInstance()
			->getService( 'BSArticlePreviewCapturePhantomJSFactory' )
			->getPhantomJS();

		$fileData = $phantomJS->getScreenshotByUrl( $url, $baseUrl, $cookies );

		$status = \BsFileSystemHelper::saveToDataDirectory(
			$fileName,
			$fileData,
			'ArticlePreviewCapture'
		);

		if ( !$status->isOK() ) {
			throw new \MWException( $status->getMessage() );
		}

		unlink( $this->lockFile );

		return $status->getValue();
	}

	/**
	 * @param File $file
	 */
	private function waitForFileExists( $file ) {
		if ( !$file instanceof File ) {
			return;
		}
		$count = 0;
		while ( !$file->exists() && $count < 80 ) {
			usleep( 250000 );
			$count++;
		}
	}
}
