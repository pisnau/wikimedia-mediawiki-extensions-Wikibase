<?php declare( strict_types=1 );

namespace Wikibase\Repo\RestApi\Application\UseCaseRequestValidation;

/**
 * @license GPL-2.0-or-later
 */
interface ItemLabelEditRequest extends ItemIdRequest, LanguageCodeRequest {
	public function getLabel(): string;
}
