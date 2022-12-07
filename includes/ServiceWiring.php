<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

return [
	'BSArticlePreviewCapturePhantomJSFactory' => static function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationPhantomJSBackendRegistry'
		);

		$backend = $services->getConfigFactory()->makeConfig( 'bsg' )
			->get( 'ArticlePreviewCapturePhantomJSBackend' );

		$logger = LoggerFactory::getInstance( 'ArticlePreviewCapture' );
		return new BlueSpice\ArticlePreviewCapture\PhantomJS\PhantomJSFactory(
			$registry,
			$backend,
			$logger
		);
	},
];
