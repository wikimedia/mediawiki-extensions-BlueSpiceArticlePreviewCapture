<?php

namespace BlueSpice\ArticlePreviewCapture\PhantomJS;

use BlueSpice\ExtensionAttributeBasedRegistry;
use MWException;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PhantomJSFactory {

	/** @var ExtensionAttributeBasedRegistry */
	private $phantomJSBackendRegistry;
	/** @var string */
	private $phantomJSBackend;
	/** @var LoggerInterface */
	private $logger;

	/**
	 * PhantomJSFactory constructor.
	 * @param ExtensionAttributeBasedRegistry $phantomJSBackendRegistry
	 * @param string $phantomJSBackend
	 * @param LoggerInterface|null $logger
	 */
	public function __construct( ExtensionAttributeBasedRegistry $phantomJSBackendRegistry,
								 $phantomJSBackend, $logger = null ) {
		$this->phantomJSBackendRegistry = $phantomJSBackendRegistry->getAllValues();
		$this->phantomJSBackend = $phantomJSBackend;
		$this->logger = $logger;
		if ( $this->logger === null ) {
			$this->logger = new NullLogger();
		}
	}

	/**
	 * @return IPhantomJS
	 * @throws MWException
	 */
	public function getPhantomJS() {
		if ( array_key_exists( $this->phantomJSBackend, $this->phantomJSBackendRegistry ) ) {
			$phantomJSBackend = call_user_func( $this->phantomJSBackendRegistry[ $this->phantomJSBackend ] );
			if ( $phantomJSBackend instanceof LoggerAwareInterface ) {
				$phantomJSBackend->setLogger( $this->logger );
			}
			if ( $phantomJSBackend instanceof IPhantomJS ) {
				$this->logger->debug( 'Using backend class ' . get_class( $phantomJSBackend ) );
				return $phantomJSBackend;
			}
		}

		throw new MWException(
			"There is no " . $this->phantomJSBackend . " phantomjs backend in registry. 
			Please, check your extension.json" );
	}
}
