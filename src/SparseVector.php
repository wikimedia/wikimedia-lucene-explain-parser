<?php

namespace LuceneExplain;

class SparseVector {

	/** @var array<string,float> */
	private $vec = [];

	/**
	 * @var string|null
	 */
	private $asStr;

	private function setDirty() {
		$this->asStr = null;
	}

	/**
	 * @param string $key
	 * @param float $value
	 */
	public function set( $key, $value ) {
		$this->vec[$key] = $value;
		$this->setDirty();
	}

	/**
	 * @param string $key
	 * @return float|null
	 */
	public function get( $key ) {
		return $this->vec[$key] ?? null;
	}

	/**
	 * @return string
	 */
	public function __toString() {
		if ( $this->asStr === null ) {
			$sorted = $this->vec;
			asort( $sorted );
			foreach ( $sorted as $k => $v ) {
				$this->asStr .= "$k $v\n";
			}
		}
		return $this->asStr;
	}

	/**
	 * @return array<string,float>
	 */
	public function values() {
		return $this->vec;
	}

}
