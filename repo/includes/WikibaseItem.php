<?php

/**
 * Represents a single Wikibase item.
 * See https://meta.wikimedia.org/wiki/Wikidata/Data_model#Items
 *
 * @since 0.1
 *
 * @file WikibaseItem.php
 * @ingroup Wikibase
 *
 * @licence GNU GPL v2+
 * @author Daniel Kinzler
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 */
class WikibaseItem {

	const TYPE_TEXT = 'text';
	const TYPE_SCALAR = 'scalar'; # unit, precision, point-in-time
	const TYPE_DATE = 'date';
	const TYPE_TERM = 'term'; # lang, pronunciation
	const TYPE_ENTITY_REF = 'ref';

	const PROP_LABEL = 'label';
	const PROP_DESCRIPTION = 'description';
	const PROP_ALIAS = 'alias';

	protected $data;

	/**
	 * @since 0.1
	 *
	 * @param array $data
	 */
	protected function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * @since 0.1
	 *
	 * @param array $data
	 *
	 * @return WikibaseItem
	 */
	public static function newFromArray( array $data ) {
		return new static( $data );
	}

	public function getPropertyNames() {
		//TODO: implement
	}

	public function getSystemPropertyNames() {
		//TODO: implement
	}

	public function getEditorialPropertyNames() {
		//TODO: implement
	}

	public function getStatementPropertyNames() {
		//TODO: implement
	}

	public function getPropertyMultilang( $name, $languages = null ) {
		//TODO: implement
	}

	public function getProperty( $name, $lang = null ) {
		//TODO: implement
	}

	public function getPropertyType( $name ) {
		//TODO: implement
	}

	public function isStatementProperty( $name ) {
		//TODO: implement
	}

	/**
	 * @param Language $lang
	 * @return String|null description
	 */
	public function getDescription( Language $lang ) {
		$data = $this->data;
		if ( !isset( $data['description'][$lang->getCode()] ) ) {
			return null;
		} else {
			return $data['description'][$lang->getCode()]['value'];
		}
	}

	/**
	 * @param Language $lang
	 * @return String|null label
	 */
	public function getLabel( Language $lang ) {
		$data = $this->data;
		if ( !isset( $data['label'][$lang->getCode()] ) ) {
			return null;
		} else {
			return $data['label'][$lang->getCode()]['value'];
		}
	}

	/**
	 * @param Language $lang
	 * @return array titles (languageCode => value)
	 */
	public function getTitles( Language $lang ) {
		$data = $this->data;
		$titles = array();
		foreach ( $data['titles'] as $langCode => $title ) {
			$titles[$langCode] = $title['value'];
		}
		return $titles;
	}

}
