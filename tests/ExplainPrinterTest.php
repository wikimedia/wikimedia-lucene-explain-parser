<?php

namespace LuceneExplain;
require __DIR__.'/../vendor/autoload.php';

use PHPUnit_Framework_TestCase;

class ExplainPrinterTest extends PHPUnit_Framework_TestCase
{

	public function formatProvider() {
		$tests = [];

		foreach ( glob( __DIR__ . "/fixtures/*.explain" ) as $explainFile ) {
			$testBase = substr( $explainFile, 0, -8 );
			$testName = basename( $testBase );
			$explain = json_decode( file_get_contents( $explainFile ), true );
			if ( json_last_error() !== JSON_ERROR_NONE ) {
				$this->fail( "Failed parsing $explainFile: " . json_last_error() );
			}

			$tests[$testName] = [$testBase, $explain];
		}

		return $tests;
	}

	/**
	 * @dataProvider formatProvider
	 * @param string $explainFile
	 * @param array $explanation
	 */
	public function testFormat( $explainFile, $explanation ) {
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
