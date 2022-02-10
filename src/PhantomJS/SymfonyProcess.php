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
		$command = $this->getCommandArguments( $url, $baseUrl, $cookies );

		$process = new Process( $command );

		$output = '';
		$process->run( static function ( $type, $capturedOutput ) use ( &$output ) {
			$output .= $capturedOutput;
		} );

		return base64_decode( $output );
	}
}
