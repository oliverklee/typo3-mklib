<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@das-medienkombinat.de>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 */

/**
 * benötigte Klassen einbinden
 */
require_once(t3lib_extMgm::extPath('rn_base', 'class.tx_rnbase.php'));
tx_rnbase::load('tx_rnbase_util_Templates');

/**
 * Testklasse für Marker Klassen, die ihre Ausgabe über
 * tx_rnbase_util_Templates::substituteMarkerArrayCached erstellen.
 * 
 * Bei den Tests sollte der Cache NIE genutzt werden!
 * 
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_MarkerTestcase extends Tx_Phpunit_TestCase {
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		tx_rnbase_util_Templates::disableSubstCache();
	}
	
	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		tx_rnbase_util_Templates::enableSubstCache();
	}

	/**
	 * @group dummytest
	 */
	public function testDummy() {
	}
}