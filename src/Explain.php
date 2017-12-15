<?php

namespace LuceneExplain;

class Explain
{

	protected $asJson;
	protected $realContribution;
	protected $score;
	protected $realExplanation;
	protected $description;
	protected $children = [];

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

	public function vectorize() {
		$rval = VectorService::create();
		$rval->set( $this->explanation(), $this->contribution() );
		return $rval;
	}

	public function matchDetails() {
		$rval = [];
		foreach ( $this->children as $child ) {
			$rval = $child->matchDetails() + $rval;
		}
		return $rval;
	}

	private $asStr;
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
