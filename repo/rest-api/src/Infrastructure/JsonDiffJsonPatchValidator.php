<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Infrastructure;

use Swaggest\JsonDiff\Exception;
use Swaggest\JsonDiff\JsonPatch;
use Swaggest\JsonDiff\MissingFieldException;
use Swaggest\JsonDiff\UnknownOperationException;
use Wikibase\Repo\RestApi\Domain\Services\JsonPatchValidator;
use Wikibase\Repo\RestApi\Validation\PatchInvalidFieldTypeValidationError;
use Wikibase\Repo\RestApi\Validation\PatchInvalidOpValidationError;
use Wikibase\Repo\RestApi\Validation\PatchMissingFieldValidationError;
use Wikibase\Repo\RestApi\Validation\ValidationError;

/**
 * @license GPL-2.0-or-later
 */
class JsonDiffJsonPatchValidator implements JsonPatchValidator {

	public function validate( array $patch, string $source ): ?ValidationError {
		// TODO: remove foreach checks when upstream PR merged
		// https://github.com/swaggest/json-diff/pull/60
		foreach ( $patch as $operation ) {
			if ( !is_array( $operation ) ) {
				return new ValidationError( $source );
			}
			if ( array_key_exists( 'op', $operation ) && !is_string( $operation['op'] ) ) {
				return new PatchInvalidFieldTypeValidationError(
					$source,
					[ self::ERROR_CONTEXT_OPERATION => $operation, self::ERROR_CONTEXT_FIELD => 'op' ]
				);
			}
			if ( array_key_exists( 'path', $operation ) && !is_string( $operation['path'] ) ) {
				return new PatchInvalidFieldTypeValidationError(
					$source,
					[ self::ERROR_CONTEXT_OPERATION => $operation, self::ERROR_CONTEXT_FIELD => 'path' ]
				);
			}
			if ( array_key_exists( 'from', $operation ) && !is_string( $operation['from'] ) ) {
				return new PatchInvalidFieldTypeValidationError(
					$source,
					[ self::ERROR_CONTEXT_OPERATION => $operation, self::ERROR_CONTEXT_FIELD => 'from' ]
				);
			}
		}

		try {
			JsonPatch::import( $patch );
		} catch ( MissingFieldException $e ) {
			return new PatchMissingFieldValidationError(
				$source,
				[ self::ERROR_CONTEXT_OPERATION => (array)$e->getOperation(), self::ERROR_CONTEXT_FIELD => $e->getMissingField() ]
			);
		} catch ( UnknownOperationException $e ) {
			return new PatchInvalidOpValidationError(
				$source,
				[ self::ERROR_CONTEXT_OPERATION => (array)$e->getOperation() ]
			);
		} catch ( Exception $e ) {
			return new ValidationError( $source );
		}

		return null;
	}

}
