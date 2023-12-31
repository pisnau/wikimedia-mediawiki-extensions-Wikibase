<?php declare( strict_types=1 );

namespace Wikibase\Repo\Tests\RestApi\Application\Serialization;

use Wikibase\DataModel\Services\Lookup\PropertyDataTypeLookup;
use Wikibase\Repo\RestApi\Application\Serialization\PropertyValuePairDeserializer;
use Wikibase\Repo\RestApi\Application\Serialization\ReferenceDeserializer;
use Wikibase\Repo\RestApi\Application\Serialization\StatementDeserializer;
use Wikibase\Repo\RestApi\Infrastructure\DataTypeFactoryValueTypeLookup;
use Wikibase\Repo\RestApi\Infrastructure\DataValuesValueDeserializer;
use Wikibase\Repo\WikibaseRepo;

/**
 * @license GPL-2.0-or-later
 */
class DeserializerFactory {

	public static function newStatementDeserializer( PropertyDataTypeLookup $dataTypeLookup ): StatementDeserializer {
		$entityIdParser = WikibaseRepo::getEntityIdParser();
		$propertyValuePairDeserializer = new PropertyValuePairDeserializer(
			$entityIdParser,
			$dataTypeLookup,
			new DataValuesValueDeserializer(
				new DataTypeFactoryValueTypeLookup( WikibaseRepo::getDataTypeFactory() ),
				$entityIdParser,
				WikibaseRepo::getDataValueDeserializer(),
				WikibaseRepo::getDataTypeValidatorFactory()
			)
		);

		return new StatementDeserializer(
			$propertyValuePairDeserializer,
			new ReferenceDeserializer( $propertyValuePairDeserializer )
		);
	}

}
