<?php

namespace Wikibase\Lib\Reporting;

use Exception;

/**
 * RethrowingExceptionHandler handles exceptions by re-throwing them.
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class RethrowingExceptionHandler implements ExceptionHandler {

	/**
	 * Rethrows the given exception;
	 *
	 * @see ExceptionHandler::handleException()
	 *
	 * @param Exception $exception
	 * @param string $errorCode
	 * @param string $explanation
	 * @return never
	 *
	 * @throws Exception
	 */
	public function handleException( Exception $exception, $errorCode, $explanation ) {
		throw $exception;
	}

}
