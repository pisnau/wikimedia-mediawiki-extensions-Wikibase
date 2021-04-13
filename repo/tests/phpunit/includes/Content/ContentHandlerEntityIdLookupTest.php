<?php

namespace Wikibase\Repo\Tests\Content;

use MediaWiki\MediaWikiServices;
use MediaWikiIntegrationTestCase;
use Title;
use Wikibase\DataAccess\EntitySource;
use Wikibase\DataAccess\EntitySourceDefinitions;
use Wikibase\DataModel\Entity\Item;
use Wikibase\Lib\EntityTypeDefinitions;
use Wikibase\Repo\Content\ContentHandlerEntityIdLookup;
use Wikibase\Repo\Content\EntityContentFactory;
use Wikibase\Repo\Content\ItemContent;
use Wikibase\Repo\Content\PropertyContent;
use Wikibase\Repo\WikibaseRepo;

/**
 * @covers \Wikibase\Repo\Content\ContentHandlerEntityIdLookup
 *
 * @group Wikibase
 * @group WikibaseEntity
 * @group WikibaseContent
 *
 * @group Database
 *        ^--- just because we use the Title class
 *
 * @license GPL-2.0-or-later
 */
class ContentHandlerEntityIdLookupTest extends MediaWikiIntegrationTestCase {
	private function getItemSource() {
		return new EntitySource(
			'itemwiki',
			'itemdb',
			[ 'item' => [ 'namespaceId' => 5000, 'slot' => 'main' ] ],
			'',
			'',
			'',
			''
		);
	}

	protected function newFactory() {
		$itemSource = $this->getItemSource();
		$propertySource = new EntitySource(
			'propertywiki',
			'propertydb',
			[ 'property' => [ 'namespaceId' => 6000, 'slot' => 'main' ] ],
			'',
			'p',
			'p',
			'propertywiki'
		);

		$wikibaseRepo = WikibaseRepo::getDefaultInstance();

		return new EntityContentFactory(
			[
				'item' => ItemContent::CONTENT_MODEL_ID,
				'property' => PropertyContent::CONTENT_MODEL_ID
			],
			[
				'item' => function() use ( $wikibaseRepo ) {
					return $wikibaseRepo->newItemHandler();
				},
				'property' => function() use ( $wikibaseRepo ) {
					return $wikibaseRepo->newPropertyHandler();
				}
			],
			new EntitySourceDefinitions( [ $itemSource, $propertySource ], new EntityTypeDefinitions( [] ) ),
			$itemSource,
			MediaWikiServices::getInstance()->getInterwikiLookup()
		);
	}

	public function testGetEntityIdForTitle() {
		$factory = $this->newFactory();
		$entityIdLookup = new ContentHandlerEntityIdLookup( $factory );

		$title = Title::makeTitle( $factory->getNamespaceForType( Item::ENTITY_TYPE ), 'Q42' );
		$title->resetArticleID( 42 );

		$entityId = $entityIdLookup->getEntityIdForTitle( $title );
		$this->assertEquals( 'Q42', $entityId->getSerialization() );
	}

	public function testGetEntityIds() {
		$factory = $this->newFactory();
		$entityIdLookup = new ContentHandlerEntityIdLookup( $factory );

		/** @var Title[] $titles */
		$titles = [
			0 => Title::makeTitle( $factory->getNamespaceForType( Item::ENTITY_TYPE ), 'Q17' ),
			10 => Title::makeTitle( $factory->getNamespaceForType( Item::ENTITY_TYPE ), 'Q42' ),
			20 => Title::makeTitle( NS_HELP, 'Q42' ),
			30 => Title::makeTitle( $factory->getNamespaceForType( Item::ENTITY_TYPE ), 'XXX' ),
			40 => Title::makeTitle( $factory->getNamespaceForType( Item::ENTITY_TYPE ), 'Q144' ),
		];

		foreach ( $titles as $id => $title ) {
			$title->resetArticleID( $id );
		}

		$entityIds = $entityIdLookup->getEntityIds( array_values( $titles ) );

		$this->assertArrayNotHasKey( 0, $entityIds );
		$this->assertArrayHasKey( 10, $entityIds );
		$this->assertArrayNotHasKey( 20, $entityIds );
		$this->assertArrayNotHasKey( 30, $entityIds );
		$this->assertArrayHasKey( 40, $entityIds );

		$this->assertEquals( 'Q42', $entityIds[10]->getSerialization() );
		$this->assertEquals( 'Q144', $entityIds[40]->getSerialization() );
	}

}