<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use MediaWiki\Shell\Shell;

class MediaWikiShellCommand extends NativeShellExec {

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return bool|string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies ) {
		$this->logger->debug( "Creating preview image for $url" );
		$command = $this->getCommandArguments( $url, $baseUrl, $cookies );

		$this->logger->debug( "Running command '$command'" );
		$output = Shell::command( $command )
			->restrict( Shell::RESTRICT_DEFAULT | Shell::NO_NETWORK )
			->execute()->getStdout();
		$this->logger->debug( "Response: '$output'" );

		return base64_decode( $output );
	}
}
