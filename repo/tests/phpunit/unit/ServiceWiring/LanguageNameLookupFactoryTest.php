<?php

declare( strict_types = 1 );

namespace Wikibase\Repo\Tests\Unit\ServiceWiring;

use Wikibase\Lib\LanguageNameLookupFactory;
use Wikibase\Lib\MediaWikiMessageInLanguageProvider;
use Wikibase\Repo\Tests\Unit\ServiceWiringTestCase;

/**
 * @coversNothing
 *
 * @group Wikibase
 *
 * @license GPL-2.0-or-later
 */
class LanguageNameLookupFactoryTest extends ServiceWiringTestCase {

	public function testConstruction(): void {
		$this->serviceContainer->expects( $this->once() )
			->method( 'getLanguageNameUtils' );
		$this->mockService( 'WikibaseRepo.MessageInLanguageProvider',
			new MediaWikiMessageInLanguageProvider() );
		$this->assertInstanceOf(
			LanguageNameLookupFactory::class,
			$this->getService( 'WikibaseRepo.LanguageNameLookupFactory' )
		);
	}

}
