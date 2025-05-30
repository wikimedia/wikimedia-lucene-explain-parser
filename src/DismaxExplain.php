<?php

namespace LuceneExplain;

class DismaxExplain extends Explain {

	public function __construct( array $explJson, ExplainFactory $explFactory ) {
		parent::__construct( $explJson, $explFactory );
		$this->realExplanation = 'Dismax (take winner of below)';
	}

	/**
	 * @return Explain[]
	 */
	public function influencers() {
		return $this->scoreSort( $this->children );
	}

	/**
	 * @return SparseVector
	 */
	public function vectorize() {
		$infl = $this->influencers();
		return reset( $infl )->vectorize();
	}
}
