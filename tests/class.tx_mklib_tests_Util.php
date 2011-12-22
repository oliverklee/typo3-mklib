<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_tests
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2010 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
tx_rnbase::load('tx_rnbase_cache_Manager');
tx_rnbase::load('tx_rnbase_util_TYPO3');
tx_rnbase::load('tx_rnbase_util_Spyc');

/**
 * Statische Hilfsmethoden für Tests
 *
 * @package tx_mklib
 * @subpackage tx_mklib_tests
 */
class tx_mklib_tests_Util {

	private static $aExtConf = array();
	private static $sCacheFile;

	/**
	 * Sichert eine Extension Konfiguration.
	 * Wurde bereits eine Extension Konfiguration gesichert,
	 * wird diese nur überschrieben wenn bOverwrite wahr ist!
	 *
	 * @param string 	$sExtKey
	 * @param boolean 	$bOverwrite
	 */
	public static function storeExtConf($sExtKey='mklib', $bOverwrite = false){
		if(!isset(self::$aExtConf[$sExtKey]) || $bOverwrite){
			self::$aExtConf[$sExtKey] = $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey];
		}
	}
	/**
	 * Setzt eine gesicherte Extension Konfiguration zurück.
	 *
	 * @param string $sExtKey
	 * @return boolean 		wurde die Konfiguration zurückgesetzt?
	 */
	public static function restoreExtConf($sExtKey='mklib'){
		if(isset(self::$aExtConf[$sExtKey])) {
			$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = self::$aExtConf[$sExtKey];
			return true;
		} return false;
	}

	/**
	 * Setzt eine Vaiable in die Extension Konfiguration.
	 * Achtung im setUp sollte storeExtConf und im tearDown restoreExtConf aufgerufen werden.
	 * @param string 	$sCfgKey
	 * @param string 	$sCfgValue
	 * @param string 	$sExtKey
	 */
	public static function setExtConfVar($sCfgKey, $sCfgValue, $sExtKey='mklib'){
		// aktuelle Konfiguration auslesen
		$extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey]);
		// wenn keine Konfiguration existiert, legen wir eine an.
		if(!is_array($extConfig)) {
			$extConfig = array();
		}
		// neuen Wert setzen
		$extConfig[$sCfgKey] = $sCfgValue;
		// neue Konfiguration zurückschreiben
		$GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$sExtKey] = serialize($extConfig);
	}

	/**
	 * Liefert eine DateiNamen
	 * @param $filename
	 * @param $dir
	 * @param $extKey
	 * @return string
	 */
	public static function getFixturePath($filename, $dir = 'tests/fixtures/', $extKey = 'mklib') {
		return t3lib_extMgm::extPath($extKey).$dir.$filename;
	}

	/**
	 * Disabled das Logging über die Devlog Extension für die
	 * gegebene Extension
	 *
	 * @param 	string 	$extKey
	 * @param 	boolean 	$bDisable
	 */
	public static function disableDevlog($extKey = 'devlog', $bDisable = true) {
		$GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$extKey]['nolog'] = $bDisable;
	}

	/**
	 * Führt eine beliebige DB-Query aus
	 * @param string $sqlFile
	 */
	public static function queryDB($sqlFile, $statementType = false, $bIgnoreStatementType = false) {
//		$sql = file_get_contents($sqlFile);
		$sql = t3lib_div::getUrl($sqlFile);
		if(empty($sql))
			throw new Exception('SQL-Datei nicht gefunden');
		if($statementType || $bIgnoreStatementType) {
			$statements = t3lib_install::getStatementArray($sql, 1);
			foreach($statements as $statement){
				if(!$bIgnoreStatementType && t3lib_div::isFirstPartOfStr($statement, $statementType)) {
					$GLOBALS['TYPO3_DB']->admin_query($statement);
				}elseif($bIgnoreStatementType){//alle gefundenen statements ausführen
					$GLOBALS['TYPO3_DB']->admin_query($statement);
				}
			}
		} else {
			$GLOBALS['TYPO3_DB']->admin_query($sql);
		}
	}
	
	/**
	 * Simuliert ein einfaches FE zur testausführung
	 * Wurde tx_phpunit_module1 entnommen da die Methode protected ist
	 * und nicht bei der Ausführung auf dem CLI aufgerufen wird. Das
	 * kann in manchen Fällen aber notwendig sein
	 * @see tx_phpunit_module1::simulateFrontendEnviroment
	 * @todo in eigene Klasse auslagern, die von tx_phpunit_module1 erbt und simulateFrontendEnviroment public macht
	 */
	public static function simulateFrontendEnviroment() {
		if (isset($GLOBALS['TSFE']) && is_object($GLOBALS['TSFE'])) {
			// avoids some memory leaks
			unset(
				$GLOBALS['TSFE']->tmpl, $GLOBALS['TSFE']->sys_page, $GLOBALS['TSFE']->fe_user,
				$GLOBALS['TSFE']->TYPO3_CONF_VARS, $GLOBALS['TSFE']->config, $GLOBALS['TSFE']->TCAcachedExtras,
				$GLOBALS['TSFE']->imagesOnPage, $GLOBALS['TSFE']->cObj, $GLOBALS['TSFE']->csConvObj,
				$GLOBALS['TSFE']->pagesection_lockObj, $GLOBALS['TSFE']->pages_lockObj
			);
			$GLOBALS['TSFE'] = NULL;
			$GLOBALS['TT'] = NULL;
		}

		$GLOBALS['TT'] = t3lib_div::makeInstance('t3lib_TimeTrackNull');
		$frontEnd = t3lib_div::makeInstance('tslib_fe', $GLOBALS['TYPO3_CONF_VARS'], 0, 0);

		// simulates a normal FE without any logged-in FE or BE user
		$frontEnd->beUserLogin = FALSE;
		$frontEnd->workspacePreview = '';
		$frontEnd->gr_list = '0,-1';

		$frontEnd->sys_page = t3lib_div::makeInstance('t3lib_pageSelect');
		$frontEnd->sys_page->init(TRUE);
		$frontEnd->initTemplate();

		// $frontEnd->getConfigArray() doesn't work here because the dummy FE
		// is not required to have a template.
		$frontEnd->config = array();

		$GLOBALS['TSFE'] = $frontEnd;
	}
	
 	/**
   	 * Lädt den Inhalt einer Datei
   	 * @param string $filename
   	 * @param array $options
     */
  	public function loadTemplate($filename,$configurations,$extKey = 'mklib',$subpart=null, $dir = 'tests/fixtures/'){
    	$path = self::getFixturePath($filename,$dir,$extKey);

	    $cObj =& $configurations->getCObj();
	    $templateCode = file_get_contents($path);
		if($subpart)
			$templateCode = $cObj->getSubpart($templateCode,$subpart);
	
		return $templateCode;
	}
	
	/**
	 * Setzt das fe_user objekt, falls es noch nicht gesetzt wurde
	 *
	 * @param 	tslib_feuserauth 	$oFeUser 	Erzeugt das tslib_feuserauth Objekt wenn nix übergeben wurde
	 * @param 	boolean 			$bForce		Setzt das fe_user Objekt auch, wenn es bereits gesetzt ist.
	 * @return 	void
	 */
	public static function setFeUserObject($oFeUser=null, $bForce=false) {
		if(!($GLOBALS['TSFE']->fe_user instanceof tslib_feuserauth) || $bForce) {
			$GLOBALS['TSFE']->fe_user = is_object($oFeUser) ?
						$oFeUser : tx_rnbase::makeInstance('tslib_feuserauth');
		}
	}
	
	/**
	 * Speichert den Cache
	 */
	public static function storeCacheFile() {
		//aktuelle Konfiguration sichern
		self::$sCacheFile = $GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'];
	}
	
	/**
	 * Reaktiviert den Cache
	 */
	public static function restoreCacheFile() {
		//aktuelle Konfiguration sichern
		$GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'] = self::$sCacheFile;
	}
	
	/**
	 * Deaktiviert den Cache
	 * damit nicht 'A cache with identifier "tx_extbase_cache_reflection" has already been registered.' kommt
	 */
	public static function deactivateCacheFile() {
		//aktuelle Konfiguration sichern
		$GLOBALS['TYPO3_LOADED_EXT']['_CACHEFILE'] = null;
	}
	
	/**
	 *
	 * @param 	string			$sActionName
	 * @param	array			$aConfig
	 * @param	string			$sExtKey
	 * @param	array			$aParams
	 * @param 	boolean 		$execute
	 * @return tx_mkforms_action_FormBase
	 */
	public static function &getAction($sActionName, $aConfig, $sExtKey, $aParams = array(), $execute = true) {
		$action = tx_rnbase::makeInstance($sActionName);
		
		if($execute) {
			$configurations = tx_rnbase::makeInstance('tx_rnbase_configurations');
			$parameters = tx_rnbase::makeInstance('tx_rnbase_parameters');
			
			//@TODO: warum wird die klasse tslib_cObj nicht gefunden!? (mw: eternit local)
			require_once(t3lib_extMgm::extPath('cms', 'tslib/class.tslib_content.php'));
			$configurations->init(
					$aConfig,
					$configurations->getCObj(1),
					$sExtKey,$sExtKey
				);
				
			//noch extra params?
			if(!empty($aParams))
				foreach ($aParams as $sName => $mValue)
					$parameters->offsetSet($sName,$mValue);
				
			$configurations->setParameters($parameters);
			$action->setConfigurations($configurations);
			
//			$action->execute($parameters, $configurations);
			$out = $action->handleRequest($parameters, $configurations, $configurations->getViewData());
		}
		return $action;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/tests/class.tx_mklib_tests_Util.php']);
}