<?php
namespace LuceneExplain;

class MinExplain extends Explain
{

	public function __construct( array $explJson, ExplainFactory $explFactory ) {
		parent::__construct( $explJson, $explFactory );
		$this->realExplanation = 'Minimum Of:';
	}

	public function influencers() {
		$infl = $this->children;
		usort( $infl, function ( $a, $b ) {
			if ( $a->score == $b->score ) {
				return 0;
			}
			return ( $a->score < $b->score ) ? -1 : 1;
		} );
		return $infl;
	}

	public function vectorize() {
		$infl = $this->influencers();
		$minInfl = $infl[0];
		return $minInfl->vectorize();
	}

}
