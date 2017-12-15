<?php
namespace LuceneExplain;

class DismaxExplain extends Explain
{

	public function __construct( array $explJson, ExplainFactory $explFactory ) {
		parent::__construct( $explJson, $explFactory );
		$this->realExplanation = 'Dismax (take winner of below)';
	}

	public function influencers() {
		$infl = $this->children;
		usort( $infl, function ( $a, $b ) {
			if ( $a->score == $b->score ) {
				return 0;
			}
			return ( $a->score < $b->score ) ? 1 : -1;
		} );
		return $infl;
	}

	public function vectorize() {
		$infl = $this->influencers();
		return reset( $infl )->vectorize();
	}
}
