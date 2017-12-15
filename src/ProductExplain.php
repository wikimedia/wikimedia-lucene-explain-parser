<?php
namespace LuceneExplain;

class ProductExplain extends Explain
{

	public function __construct( array $explJson, ExplainFactory $explFactory ) {
		parent::__construct( $explJson, $explFactory );
		$this->simplify();
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
		$rVal = VectorService::create();
		$infl = $this->influencers();
		$inflFactors = array_pad( [], count( $infl ), 1 );

		$numInfl = count( $infl );
		for ( $factorInfl = 0; $factorInfl < $numInfl; $factorInfl++ ) {
			for ( $currMult = 0; $currMult < $numInfl; $currMult++ ) {
				if ( $currMult !== $factorInfl ) {
					$inflFactors[$factorInfl] *= $infl[$currMult]->contribution();
				}
			}
		}

		for ( $currInfl = 0; $currInfl < $numInfl; $currInfl++ ) {
			$i = $infl[$currInfl];
			$thisVec = $i->vectorize();
			$thisScaledByOthers = VectorService::scale( $thisVec, $inflFactors[$currInfl] );
			$rVal = VectorService::add( $rVal, $thisScaledByOthers );
		}

		return $rVal;
	}

	private function simplify() {
		foreach ( $this->children as $k => $child ) {
			// Only simplify expressions that don't seem to add value
			if ( $child->contribution() === 1.0 && in_array( $child->explanation(), [
					'*:*',
					'match filter: *:*',
					'primaryWeight',
					'secondaryWeight',
				] ) ) {
				unset( $this->children[$k] );
			}
		}
	}

}
