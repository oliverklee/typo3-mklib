<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests_util
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
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
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');
tx_rnbase::load('tx_mklib_util_TCA');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * Generic form view test
 * @package tx_mklib
 * @subpackage tx_mklib_tests_util
 */
class tx_mklib_tests_util_TCA_testcase extends tx_phpunit_testcase {

	/**
	 * @var string
	 */
	private $returnUrlBackup;

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::setUp()
	 */
	protected function setUp() {
		//es kann sein dass die TCA von der wordlist nicht geladen wurde.
		//also stellen wir die TCA hier bereit
		tx_rnbase::load('tx_mklib_srv_Wordlist');
		global $TCA;
		$TCA['tx_mklib_wordlist'] = tx_mklib_srv_Wordlist::getTca();

		$this->returnUrlBackup = $_GET['returnUrl'];
	}

	/**
	 * (non-PHPdoc)
	 * @see PHPUnit_Framework_TestCase::tearDown()
	 */
	protected function tearDown() {
		$_GET['returnUrl'] = $this->returnUrlBackup;
		unset($GLOBALS['TCA']['tt_mktest_table']);
	}

	/**
	 *
	 */
	public function testEleminateNonTcaColumns(){
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadNewTcaColumnsConfigFiles();
		}
		$model = tx_rnbase::makeInstance('tx_mklib_model_WordlistEntry', array());
		$data = array(
	  		'blacklisted' => true,
	  		'whitelisted' => 0,
	  		'ich-muss-raus' => true,
	  		'ich-auch' => false,
		);
		$res = tx_mklib_util_TCA::eleminateNonTcaColumns($model,$data);
		$this->assertEquals(2,count($res),'falsche array größe');
		$this->assertTrue($res['blacklisted'],'blacklsited Feld ist nicht korrekt!');
		$this->assertEquals(0,$res['whitelisted'],'whitelisted Feld ist nicht korrekt!');
		$this->assertTrue(empty($res['ich-muss-raus']),'ich-muss-raus Feld wurde nicht entfernt!');
		$this->assertTrue(empty($res['ich-auch']),'ich-auch Feld wurde nicht entfernt!');
	}
	/**
	 *
	 */
	public function testEleminateNonTcaColumnsByTable(){
		if(tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::loadNewTcaColumnsConfigFiles();
		}
		$data = array(
	  		'blacklisted' => true,
	  		'whitelisted' => 0,
	  		'ich-muss-raus' => true,
	  		'ich-auch' => false,
		);
		$res = tx_mklib_util_TCA::eleminateNonTcaColumnsByTable('tx_mklib_wordlist',$data);
		$this->assertEquals(2,count($res),'falsche array größe');
		$this->assertTrue($res['blacklisted'],'blacklsited Feld ist nicht korrekt!');
		$this->assertEquals(0,$res['whitelisted'],'whitelisted Feld ist nicht korrekt!');
		$this->assertFalse(isset($res['ich-muss-raus']),'ich-muss-raus Feld wurde nicht entfernt!');
		$this->assertFalse(isset($res['ich-auch']),'ich-auch Feld wurde nicht entfernt!');
	}

	/**
	 * @group unit
	 */
	public function testGetEnableColumnReturnsDeletedForDisabled() {
		$expected = 'deleted';
		$GLOBALS['TCA']['tt_mktest_table']['ctrl']['enablecolumns']['disabled'] = $expected;
		$actual = tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled');
		$this->assertEquals($expected, $actual);
	}
	/**
	 * @group unit
	 * @expectedException LogicException
     * @expectedExceptionCode 4003001
	 */
	public function testGetEnableColumnThrowsExceptionForNonExcitingTable() {
		tx_mklib_util_TCA::getEnableColumn('tt_mktest_table_does_not_exists', 'disabled');
	}
	/**
	 * @group unit
	 * @expectedException LogicException
     * @expectedExceptionCode 4003002
	 */
	public function testGetEnableColumnThrowsExceptionForNonExcitingColumn() {
		$GLOBALS['TCA']['tt_mktest_table']['ctrl']['enablecolumns'] = array();
		tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled');
	}
	/**
	 * @group unit
	 */
	public function testGetEnableColumnReturnsDefaultValueForDisabled() {
		$expected = 'removed';
		$actual = tx_mklib_util_TCA::getEnableColumn('tt_mktest_table', 'disabled', $expected);
		$this->assertEquals($expected, $actual);
	}
	/**
	 * @group unit
	 */
	public function testGetLanguageFieldReturnsRightValue() {
		$expected = 'sys_language_identifier';
		$GLOBALS['TCA']['tt_mktest_table']['ctrl']['languageField'] = $expected;
		$actual = tx_mklib_util_TCA::getLanguageField('tt_mktest_table');
		$this->assertEquals($expected, $actual);
	}
	/**
	 * @group unit
	 * @expectedException LogicException
     * @expectedExceptionCode 4003001
	 */
	public function testGetLanguageThrowsExceptionForNonExcitingTable() {
		tx_mklib_util_TCA::getLanguageField('tt_mktest_table_does_not_exists');
	}
	/**
	 * @group unit
	 */
	public function testGetParentUidFromReturnUrlReturnsNullIfNoReturnUrl(){
		$this->assertNull(
			tx_mklib_util_TCA::getParentUidFromReturnUrl(), 'parent uid zu Beginn nicht leer'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetParentUidFromReturnUrlReturnsNullIfParentUidNotExistentInReturnUrl(){
		$_GET['returnUrl'] = 'typo3/wizard_add.php';

		$this->assertNull(
			tx_mklib_util_TCA::getParentUidFromReturnUrl(), 'parent uid zu Beginn nicht leer'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetParentUidFromReturnUrlReturnsNullIfParentUidNotSetInReturnUrl(){
		$_GET['returnUrl'] = 'typo3/wizard_add.php?&P[uid]=';

		$this->assertNull(
			tx_mklib_util_TCA::getParentUidFromReturnUrl(), 'parent uid zu Beginn nicht leer'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetParentUidFromReturnUrlReturnsCorrectParentUid(){
		$_GET['returnUrl'] = 'typo3/wizard_add.php?&P[uid]=2';

		$this->assertEquals(
			2, tx_mklib_util_TCA::getParentUidFromReturnUrl(), 'parent uid nicht korrekt'
		);
	}

	/**
	 * @group unit
	 */
	public function testCropLabelsWithDefaultLengthOf80CharsCorrect() {
		$labelWith81Chars = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmodss';
		$tcaTableInformation = array('items' => array(0 => array(0 => $labelWith81Chars)));

		tx_mklib_util_TCA::cropLabels($tcaTableInformation);

		$labelWith80CharsAnd3Dots = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmods...';
		$this->assertEquals(
			$labelWith80CharsAnd3Dots,
			$tcaTableInformation['items'][0][0],
			'Label nicht richtig gekürzt'
		);
	}

	/**
	 * @group unit
	 */
	public function testCropLabelsWithEmptyItems() {
		$tcaTableInformation = array('items' => array());
		tx_mklib_util_TCA::cropLabels($tcaTableInformation);
		unset($tcaTableInformation['items']);
		tx_mklib_util_TCA::cropLabels($tcaTableInformation);
	}

	/**
	 * @group unit
	 */
	public function testCropLabelsWithConfiguredLengthOf40Chars() {
		$labelWith81Chars = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmodss';
		$tcaTableInformation = array(
			'items' => array(0 => array(0 => $labelWith81Chars)),
			'config' => array('labelLength' => 40)
		);

		tx_mklib_util_TCA::cropLabels($tcaTableInformation);

		$labelWith40CharsAnd3Dots = 'Lorem ipsum dolor sit amet, consetetur s...';
		$this->assertEquals(
			$labelWith40CharsAnd3Dots,
			$tcaTableInformation['items'][0][0],
			'Label nicht richtig gekürzt'
		);
	}

	/**
	 * @group unit
	 */
	public function testCropLabelsUsesDefaultLengthIfConfiguredLengthIsNoIntegerGreaterThan0() {
		$labelWith81Chars = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmodss';
		$tcaTableInformation = array(
			'items' => array(0 => array(0 => $labelWith81Chars)),
			'config' => array('labelLength' => 'test')
		);

		tx_mklib_util_TCA::cropLabels($tcaTableInformation);

		$labelWith80CharsAnd3Dots = 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmods...';
		$this->assertEquals(
			$labelWith80CharsAnd3Dots,
			$tcaTableInformation['items'][0][0],
			'Label nicht richtig gekürzt'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetGermanStatesFieldWithoutRequired() {
		$expectedGermanStatesField = array (
			'exclude'	=> 1,
			'label'		=> 'LLL:EXT:mklib/locallang_db.xml:tt_address.region',
			'config'	=> array (
				'type'	=> 'select',
				'items'	=> array (
					array('LLL:EXT:mklib/locallang_db.xml:please_choose', ''),
				),
				'foreign_table'			=> 'static_country_zones',
				'foreign_table_where' 	=> ' AND static_country_zones.zn_country_iso_nr = 276 ORDER BY static_country_zones.zn_name_local',
				'size' 					=> 1,
			)
		);

		$germanStatesField = tx_mklib_util_TCA::getGermanStatesField();

		$this->assertEquals(
			$expectedGermanStatesField,
			$germanStatesField,
			'TCA Feld falsch'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetGermanStatesFieldWithRequired() {
		$expectedGermanStatesField = array (
			'exclude'	=> 1,
			'label'		=> 'LLL:EXT:mklib/locallang_db.xml:tt_address.region',
			'config'	=> array (
				'type'	=> 'select',
				'items'	=> array (
					array('LLL:EXT:mklib/locallang_db.xml:please_choose', ''),
				),
				'foreign_table'			=> 'static_country_zones',
				'foreign_table_where' 	=> ' AND static_country_zones.zn_country_iso_nr = 276 ORDER BY static_country_zones.zn_name_local',
				'size' 					=> 1,
				'minitems' => 1,
				'maxitems' => 1,
				'eval' => 'required'
			)
		);

		$germanStatesField = tx_mklib_util_TCA::getGermanStatesField(true);

		$this->assertEquals(
			$expectedGermanStatesField,
			$germanStatesField,
			'TCA Feld falsch'
		);
	}

	/**
	 * @group unit
	 */
	public function testGetWizardsReturnsLinkWizardCorrect() {
		$linkWizard = tx_mklib_util_TCA::getWizards(
			'', array(
				'link' => array(
					'params' => Array(
						'blindLinkOptions' => 'file,page,mail,spec,folder',
					)
				)
			)
		);

		$expectedLinkWizard = array(
			'_PADDING' => 2,
			'_VERTICAL' => 1,
			'link' => array(
				'type' => 'popup',
				'title' => 'LLL:EXT:cms/locallang_ttc.xml:header_link_formlabel',
				'icon' => 'link_popup.gif',
				'script' => 'browse_links.php?mode=wizard',
				'JSopenParams' => 'height=300,width=500,status=0,menubar=0,scrollbars=1',
				'params' => Array(
						'blindLinkOptions' => 'file,page,mail,spec,folder',
				)
			)
		);

		$this->assertEquals($expectedLinkWizard, $linkWizard, 'link wizard nicht korrekt');
	}

	/**
	 * @group unit
	 */
	public function testGetWizardsReturnsWizardsWithCorrectScriptOrModuleKey() {
		$wizards = tx_mklib_util_TCA::getWizards(
			'', array(
				'add' => 1,
				'edit' => 1,
				'list' => 1,
				'RTE' => 1
			)
		);

		if (tx_rnbase_util_TYPO3::isTYPO60OrHigher()) {
			$this->assertArrayNotHasKey('script', $wizards['add']);
			$this->assertArrayNotHasKey('script', $wizards['edit']);
			$this->assertArrayNotHasKey('script', $wizards['list']);
			$this->assertArrayNotHasKey('script', $wizards['RTE']);
			$this->assertEquals('wizard_add', $wizards['add']['module']['name']);
			$this->assertEquals('wizard_edit', $wizards['edit']['module']['name']);
			$this->assertEquals('wizard_list', $wizards['list']['module']['name']);
			$this->assertEquals('wizard_rte', $wizards['RTE']['module']['name']);
		} else {
			$this->assertArrayNotHasKey('module', $wizards['add']);
			$this->assertArrayNotHasKey('module', $wizards['edit']);
			$this->assertArrayNotHasKey('module', $wizards['list']);
			$this->assertArrayNotHasKey('module', $wizards['RTE']);
			$this->assertEquals('wizard_add.php', $wizards['add']['script']);
			$this->assertEquals('wizard_edit.php', $wizards['edit']['script']);
			$this->assertEquals('wizard_list.php', $wizards['list']['script']);
			$this->assertEquals('wizard_rte.php', $wizards['RTE']['script']);
		}
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_TCA_testcase.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/tests/util/class.tx_mklib_tests_util_TCA_testcase.php']);
}