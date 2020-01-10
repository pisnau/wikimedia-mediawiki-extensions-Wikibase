<?php

namespace Wikibase\DataAccess;

use InvalidArgumentException;
use Wikibase\DataModel\Entity\Int32EntityId;

/**
 * Provides access to settings relevant for services in the data access component.
 *
 * @license GPL-2.0-or-later
 */
class DataAccessSettings {

	const USE_ENTITY_SOURCE_BASED_FEDERATION = true;
	const USE_REPOSITORY_PREFIX_BASED_FEDERATION = false;

	const PROPERTY_TERMS_NORMALIZED = true;
	const PROPERTY_TERMS_UNNORMALIZED = false;

	const ITEM_TERMS_UNNORMALIZED_STAGE_ONLY = [ 'max' => MIGRATION_OLD ];

	/**
	 * @var int
	 */
	private $maxSerializedEntitySizeInBytes;

	/**
	 * @var bool
	 */
	private $useSearchFields;

	/**
	 * @var bool
	 */
	private $forceWriteSearchFields;

	/**
	 * @var bool
	 */
	private $useEntitySourceBasedFederation;

	/**
	 * @var bool
	 */
	private $useNormalizedPropertyTerms;

	/**
	 * @var array
	 */
	private $itemTermsMigrationStages;

	/**
	 * @param int $maxSerializedEntitySizeInKiloBytes
	 * @param bool $useSearchFields
	 * @param bool $forceWriteSearchFields
	 * @param bool $useEntitySourceBasedFederation TODO: Temporary. Remove once it is the only federation implementation
	 * @param bool $useNormalizedPropertyTerms TODO: Temporary until we get rid of wb_terms
	 * @param array $itemTermsMigrationStages TODO: Temporary until we get rid of wb_terms
	 */
	public function __construct(
		int $maxSerializedEntitySizeInKiloBytes,
		bool $useSearchFields,
		bool $forceWriteSearchFields,
		bool $useEntitySourceBasedFederation,
		bool $useNormalizedPropertyTerms,
		array $itemTermsMigrationStages
	) {
		$this->maxSerializedEntitySizeInBytes = $maxSerializedEntitySizeInKiloBytes * 1024;
		$this->useSearchFields = $useSearchFields;
		$this->forceWriteSearchFields = $forceWriteSearchFields;
		$this->useEntitySourceBasedFederation = $useEntitySourceBasedFederation;
		$this->useNormalizedPropertyTerms = $useNormalizedPropertyTerms;
		$this->itemTermsMigrationStages = $itemTermsMigrationStages;
	}

	/**
	 * @return int
	 */
	public function maxSerializedEntitySizeInBytes() {
		return $this->maxSerializedEntitySizeInBytes;
	}

	/**
	 * @return bool
	 */
	public function useSearchFields() {
		return $this->useSearchFields;
	}

	/**
	 * @return bool
	 */
	public function forceWriteSearchFields() {
		return $this->forceWriteSearchFields;
	}

	public function useEntitySourceBasedFederation() {
		return $this->useEntitySourceBasedFederation;
	}

	/**
	 * Whether to read property terms from the new, normalized schema,
	 * rather than from the old wb_terms table.
	 *
	 * The new schema is accessed through classes like
	 * {@link DatabasePropertyTermStore} and {@link PrefetchingPropertyTermLookup},
	 * the old one through classes like {@link TermSqlIndex}
	 * and {@link BufferingTermIndexTermLookup}.
	 *
	 * This is a temporary setting used during the transition period.
	 * Eventually, the normalized schema will be the only one supported.
	 *
	 * @return bool
	 */
	public function useNormalizedPropertyTerms() {
		return $this->useNormalizedPropertyTerms;
	}

	/**
	 * Whether to read item terms from the new, normalized schema,
	 * rather than from the old wb_terms table.
	 *
	 * The new schema is accessed through classes like
	 * {@link DatabaseItemTermStore} and {@link PrefetchingItemTermLookup},
	 * the old one through classes like {@link TermSqlIndex}
	 * and {@link BufferingTermIndexTermLookup}.
	 *
	 * During migraiton of item terms, we have different stages on for different, subsequent ranges
	 * of item ids. That is why this method takes a numeric entity id.
	 */
	public function useNormalizedItemTerms( int $numericItemId ): bool {
		foreach ( $this->itemTermsMigrationStages as $maxId => $migrationStage ) {
			if ( $maxId === 'max' ) {
				$maxId = Int32EntityId::MAX;
			} elseif ( !is_int( $maxId ) ) {
				throw new InvalidArgumentException( "'{$maxId}' in tmpItemTermsMigrationStages is not integer" );
			}

			if ( $numericItemId > $maxId ) {
				continue;
			}

			return $migrationStage >= MIGRATION_WRITE_NEW;
		}

		return false;
	}

	/**
	 * @return array The mapping of maxId to migration stages of item terms
	 */
	public function getItemTermsMigrationStages(): array {
		return $this->itemTermsMigrationStages;
	}

}
