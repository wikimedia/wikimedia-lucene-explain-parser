<?php

namespace LuceneExplain;

class Explain
{

	/**
	 * @var array
	 */
	protected $asJson;
	/**
	 * @var float
	 */
	protected $realContribution;
	/**
	 * @var float
	 */
	protected $score;
	/**
	 * @var string
	 */
	protected $realExplanation;
	/**
	 * @var string
	 */
	protected $description;
	/**
	 * @var Explain[]
	 */
	protected $children = [];

	/**
	 * @param array $explJson JSON data
	 * @param ExplainFactory $explFactory
	 */
	public function __construct( $explJson, ExplainFactory $explFactory ) {
		$this->asJson = $explJson;
		$this->realContribution = $this->score = (float)$explJson['value'];
		$this->realExplanation = $this->description = $explJson['description'];

		if ( isset( $explJson['details'] ) ) {
			foreach ( $explJson['details'] as $detail ) {
				$expl = $explFactory->createExplain( $detail );
				if ( $expl ) {
					$this->children[] = $expl;
				}
			}
		}
	}

	/**
	 * @return Explain[]
	 */
	public function influencers() {
		return [];
	}

	public function contribution() {
		return $this->realContribution;
	}

	public function explanation() {
		return $this->realExplanation;
	}

	public function hasMatch() {
		return false;
	}

	/**
	 * @return SparseVector
	 */
	public function vectorize() {
		$rval = VectorService::create();
		$rval->set( $this->explanation(), $this->contribution() );
		return $rval;
	}

	/**
	 * @return array
	 */
	public function matchDetails() {
		$rval = [];
		foreach ( $this->children as $child ) {
			$rval = $child->matchDetails() + $rval;
		}
		return $rval;
	}

	/**
	 * @var string
	 */
	private $asStr;
	/**
	 * @var string
	 */
	private $asRawStr;

	public function __toString() {
		return $this->toString( 0 );
	}

	/**
	 * @param int $depth
	 * @return string
	 */
	public function toString( $depth = 0 ) {
		if ( $this->asStr === null ) {
			$prefix = str_repeat( '  ', $depth );
			$this->asStr = $prefix . $this->contribution() . ' ' . $this->explanation() . "\n";
			foreach ( $this->influencers() as $child ) {
				$this->asStr .= $child->toString( $depth + 1 );
			}
		}
		return $this->asStr;
	}

	public function toRawString() {
		if ( $this->asRawStr === null ) {
			$this->asRawStr = json_encode( $this->asJson );
		}
		return $this->asRawStr;
	}

}
