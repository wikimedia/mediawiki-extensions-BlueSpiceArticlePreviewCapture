<?php
namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use Symfony\Component\Process\Process;

class SymfonyProcess extends NativeShellExec {

	/**
	 * @param string $url
	 * @param string $baseUrl
	 * @param string $cookies
	 * @return bool|string
	 */
	public function getScreenshotByUrl( $url, $baseUrl, $cookies ) {
		$this->logger->debug( "Creating preview image for $url" );
		$command = $this->getCommandArguments( $url, $baseUrl, $cookies );

		$process = new Process( $command );

		$output = '';
		$this->logger->debug( "Running command '$command'" );
		$process->run( function ( $type, $capturedOutput ) use ( &$output ) {
			$output .= $capturedOutput;
		} );
		$this->logger->debug( "Response: '$output'" );

		return base64_decode( $output );
	}
}
