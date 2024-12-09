<?php

namespace LuceneExplain;

/**
 * @covers \LuceneExplain\ConstantScoreExplain
 * @covers \LuceneExplain\DismaxExplain
 * @covers \LuceneExplain\DismaxTieExplain
 * @covers \LuceneExplain\Explain
 * @covers \LuceneExplain\ExplainFactory
 * @covers \LuceneExplain\FunctionQueryExplain
 * @covers \LuceneExplain\MinExplain
 * @covers \LuceneExplain\ProductExplain
 * @covers \LuceneExplain\ScoreExplain
 * @covers \LuceneExplain\ScriptScoreFunctionExplain
 * @covers \LuceneExplain\SumExplain
 * @covers \LuceneExplain\WeightExplain
 */
class ExplainPrinterTest extends \PHPUnit\Framework\TestCase {

	public function formatProvider() {
		foreach ( glob( __DIR__ . "/fixtures/*.explain" ) as $explainFile ) {
			$testBase = substr( $explainFile, 0, -8 );
			$testName = basename( $testBase );
			$explain = json_decode( file_get_contents( $explainFile ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$this->fail( "Failed parsing $explainFile: " . json_last_error() );
			}

			yield $testName => [ $testBase, $explain ];
		}
	}

	/**
	 * @dataProvider formatProvider
	 */
	public function testFormat( string $explainFile, array $explanation ) {
		$factory = new ExplainFactory();
		$explain = $factory->createExplain( $explanation );
		$result = (string)$explain;

		$hotName = "$explainFile.pretty";
		if ( file_exists( $hotName ) ) {
			$this->assertStringEqualsFile( $hotName, $result );
		} else {
			file_put_contents( $hotName, $result );
			$this->markTestSkipped();
		}
	}

}
