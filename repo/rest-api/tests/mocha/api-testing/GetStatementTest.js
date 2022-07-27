'use strict';

const { assert, action, clientFactory } = require( 'api-testing' );
const entityHelper = require( '../helpers/entityHelper' );
const { requireExtensions } = require( '../../../../../tests/api-testing/utils' );
const { RequestBuilder } = require( '../helpers/RequestBuilder' );

const basePath = 'rest.php/wikibase/v0';

function newGetStatementRequestBuilder( statementId ) {
	return new RequestBuilder()
		.withRoute( 'GET', '/statements/{statement_id}' )
		.withPathParam( 'statement_id', statementId );
}

function makeEtag( ...revisionIds ) {
	return revisionIds.map( ( revId ) => `"${revId}"` ).join( ',' );
}

describe( 'GET /statements/{statement_id}', () => {
	let testItemId;
	let testStatement;
	let testLastModified;
	let testRevisionId;

	function assertValid200Response( response ) {
		assert.equal( response.status, 200 );
		assert.equal( response.body.id, testStatement.id );
		assert.equal( response.header[ 'last-modified' ], testLastModified );
		assert.equal( response.header.etag, makeEtag( testRevisionId ) );
	}

	before( async () => {
		const createSingleItemResponse = await entityHelper.createSingleItem();
		testItemId = createSingleItemResponse.entity.id;
		const claims = createSingleItemResponse.entity.claims;
		testStatement = Object.values( claims )[ 0 ][ 0 ];

		const itemMetadata = await action.getAnon().action( 'wbgetentities', {
			ids: testItemId
		} );

		testLastModified = new Date( itemMetadata.entities[ testItemId ].modified ).toUTCString();
		testRevisionId = itemMetadata.entities[ testItemId ].lastrevid;

	} );

	it( 'can GET a statement with metadata', async () => {
		const response = await newGetStatementRequestBuilder( testStatement.id )
			.assertValidRequest()
			.makeRequest();

		assertValid200Response( response );
	} );

	describe( '400 error response', () => {
		it( 'statement ID contains invalid entity ID', async () => {
			const statementId = 'X123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newGetStatementRequestBuilder( statementId )
				.assertInvalidRequest()
				.makeRequest();

			assert.equal( response.status, 400 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'invalid-statement-id' );
			assert.include( response.body.message, statementId );
		} );

		it( 'statement ID is invalid format', async () => {
			const statementId = 'not-a-valid-format';
			const response = await newGetStatementRequestBuilder( statementId )
				.assertInvalidRequest()
				.makeRequest();

			assert.equal( response.status, 400 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'invalid-statement-id' );
			assert.include( response.body.message, statementId );
		} );

		it( 'statement is not on an item', async () => {
			const statementId = 'P123$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newGetStatementRequestBuilder( statementId )
				.assertValidRequest()
				.makeRequest();

			assert.equal( response.status, 400 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'invalid-statement-id' );
			assert.include( response.body.message, statementId );
		} );
	} );

	describe( '404 error response', () => {
		it( 'statement not found on item', async () => {
			const statementId = testItemId + '$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newGetStatementRequestBuilder( statementId )
				.assertValidRequest()
				.makeRequest();

			assert.equal( response.status, 404 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'statement-not-found' );
			assert.include( response.body.message, statementId );
		} );
		it( 'item not found', async () => {
			const statementId = 'Q321$AAAAAAAA-BBBB-CCCC-DDDD-EEEEEEEEEEEE';
			const response = await newGetStatementRequestBuilder( statementId )
				.assertValidRequest()
				.makeRequest();

			assert.equal( response.status, 404 );
			assert.header( response, 'Content-Language', 'en' );
			assert.equal( response.body.code, 'statement-not-found' );
			assert.include( response.body.message, statementId );
		} );
	} );

	describe( 'authentication', () => {

		it( 'has an X-Authenticated-User header with the logged in user', async () => {
			const mindy = await action.mindy();

			const response = await clientFactory.getRESTClient( basePath, mindy )
				.get( `/statements/${testStatement.id}` );

			assertValid200Response( response );
			assert.header( response, 'X-Authenticated-User', mindy.username );
		} );

		describe.skip( 'OAuth', () => { // Skipping due to apache auth header issues. See T305709
			before( requireExtensions( [ 'OAuth' ] ) );

			it( 'responds with an error given an invalid bearer token', async () => {
				const response = newGetStatementRequestBuilder( testStatement.id )
					.withHeader( 'Authorization', 'Bearer this-is-an-invalid-token' )
					.makeRequest();

				assert.equal( response.status, 403 );
			} );

		} );

	} );

} );