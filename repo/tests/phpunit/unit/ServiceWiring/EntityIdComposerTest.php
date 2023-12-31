<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Tests\Unit\ServiceWiring;

use Wikibase\DataModel\Entity\ItemId;
use Wikibase\DataModel\Services\EntityId\EntityIdComposer;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Repo\Tests\Unit\ServiceWiringTestCase;

/**
 * @coversNothing
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class EntityIdComposerTest extends ServiceWiringTestCase {

	public function testConstruction() {
		$this->mockService( 'WikibaseRepo.EntityTypeDefinitions',
			new EntityTypeDefinitions( [
				'test' => [
					EntityTypeDefinitions::ENTITY_ID_COMPOSER_CALLBACK => function ( $uniquePart ) {
						return new ItemId( 'Q' . $uniquePart );
					},
				],
			] ) );

		/** @var EntityIdComposer $entityIdComposer */
		$entityIdComposer = $this->getService( 'WikibaseRepo.EntityIdComposer' );

		$this->assertInstanceOf( EntityIdComposer::class, $entityIdComposer );
		$this->assertEquals( new ItemId( 'Q123' ),
			$entityIdComposer->composeEntityId( 'test', 123 ) );
	}

}
