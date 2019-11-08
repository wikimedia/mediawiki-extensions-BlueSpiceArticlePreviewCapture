<?php

use MediaWiki\MediaWikiServices;
use BlueSpice\ExtensionAttributeBasedRegistry;

return [
	'BSArticlePreviewCapturePhantomJSFactory' => function ( MediaWikiServices $services ) {
		$registry = new ExtensionAttributeBasedRegistry(
			'BlueSpiceFoundationPhantomJSBackendRegistry'
		);

		$backend = $services->getConfigFactory()->makeConfig( 'bsg' )
			->get( 'ArticlePreviewCapturePhantomJSBackend' );

		return new BlueSpice\ArticlePreviewCapture\PhantomJS\PhantomJSFactory(
			$registry,
			$backend
		);
	},
];
