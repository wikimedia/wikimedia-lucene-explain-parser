<?php

namespace LuceneExplain;

class ExplainFactory {

	/**
	 * Counter for how many explains were created.
	 * @var int
	 */
	private $counter = 0;

	private function meOrOnlyChild( Explain $explain ) {
		$infl = $explain->influencers();
		if ( count( $infl ) === 1 ) {
			return reset( $infl );
		} else {
			return $explain;
		}
	}

	/**
	 * Create new Explain from JSON data.
	 * @param array $explJson
	 * @return Explain|null
	 */
	public function createExplain( array $explJson ) {
		$description = $explJson['description'];
		$details = [];
		$tieMatch = preg_match( '/max plus ([0-9.]+) times/', $description, $tieMatches );
		$prefixMatch = preg_match( '/\:.*?\*(\^.+?)?, product of/', $description );
		if ( isset( $explJson['details'] ) ) {
			$details = $explJson['details'];
		}

		if ( str_starts_with( $description, 'score(' ) ) {
			return new ScoreExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'tf(' ) ) {
			return null; // new DefaultSimTfExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'idf(' ) ) {
			return new DefaultSimIdfExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'fieldWeight' ) ) {
			return null; // new FieldWeightExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'queryWeight' ) ) {
			return null; // new QueryWeightExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'ConstantScore' ) ) {
			return new ConstantScoreExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'MatchAllDocsQuery' ) ) {
			return new MatchAllDocsExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'weight(' ) ) {
			return new WeightExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'FunctionQuery' ) ) {
			return new FunctionQueryExplain( $explJson, $this );
		} elseif ( str_starts_with( $description, 'Function for field' ) ) {
			return new FieldFunctionQueryExplain( $explJson, $this );
		} elseif ( $prefixMatch ) {
			return new WeightExplain( $explJson, $this );
		} elseif ( $explJson['value'] === 0.0 && (
				str_starts_with( $description, 'match on required clause' ) ||
				str_starts_with( $description, 'match filter' )
			) ) {
			// Because ElasticSearch function queries filter when they apply
			// boosts (this doesn't matter in scoring)
			return null;
		} elseif ( str_starts_with( $description, 'queryBoost' ) ) {
			if ( $explJson['value'] === 1.0 ) {
				// ElasticSearch function queries always add 'queryBoost' of 1,
				// even when boost not specified
				return null;
			}
		} elseif ( str_starts_with( $description, 'script score function, computed with script:' ) ) {
			return new ScriptScoreFunctionExplain( $explJson, $this );
		} elseif ( str_contains( $description, 'constant score' ) &&
			str_contains( $description, 'no function provided' )
		) {
			return null;
		} elseif ( $description === 'weight' ) {
			return new FuncWeightExplain( $explJson, $this );
		} elseif ( $tieMatch ) {
			return new DismaxTieExplain( $explJson, $this, (float)$tieMatches[1] );
		} elseif ( str_contains( $description, 'max of' ) ) {
			return $this->meOrOnlyChild( new DismaxExplain( $explJson, $this ) );
		} elseif ( str_contains( $description, 'sum of' )
				|| str_contains( $description, 'score mode [sum]' ) ) {
			return $this->meOrOnlyChild( new SumExplain( $explJson, $this ) );
		} elseif ( str_contains( $description, 'Math.min of' ) || $description === 'min of:' ) {
			return $this->meOrOnlyChild( new MinExplain( $explJson, $this ) );
		} elseif ( str_contains( $description, 'score mode [multiply]' ) ) {
			return $this->meOrOnlyChild( new ProductExplain( $explJson, $this ) );
		} elseif ( str_contains( $description, 'product of' ) ) {
			if ( count( $details ) === 2 ) {
				foreach ( $details as $detail ) {
					if ( str_starts_with( $detail['description'], 'coord(' ) ) {
						return new CoordExplain( $explJson, $this, (float)$detail['value'] );
					}
				}
			}
			return $this->meOrOnlyChild( new ProductExplain( $explJson, $this ) );
		}

		return new Explain( $explJson, $this );
	}

	public function getCounter() {
		return $this->counter++;
	}
}
