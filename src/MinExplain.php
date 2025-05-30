<?php

namespace LuceneExplain;

class MinExplain extends Explain {

	public function __construct( array $explJson, ExplainFactory $explFactory ) {
		parent::__construct( $explJson, $explFactory );
		$this->realExplanation = 'Minimum Of:';
	}

	/**
	 * @return Explain[]
	 */
	public function influencers() {
		return $this->scoreSort( $this->children, 1 );
	}

	/**
	 * @return SparseVector
	 */
	public function vectorize() {
		$infl = $this->influencers();
		$minInfl = $infl[0];
		return $minInfl->vectorize();
	}

}
