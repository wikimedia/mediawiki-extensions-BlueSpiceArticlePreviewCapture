<?php

use BlueSpice\ExtensionAttributeBasedRegistry;
use MediaWiki\Logger\LoggerFactory;
use MediaWiki\MediaWikiServices;

// PHP unit does not understand code coverage for this file
// as the @covers annotation cannot cover a specific file
// This is fully tested in ServiceWiringTest.php
// @codeCoverageIgnoreStart

return [
	'BSArticlePreviewCapturePhantomJSFactory' => function ( MediaWikiServices $services ) {
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

// @codeCoverageIgnoreEnd
