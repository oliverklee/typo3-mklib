<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 das MedienKombinat GmbH <kontakt@das-medienkombinat.de>
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
 ***************************************************************/
require_once(t3lib_extMgm::extPath('rn_base') . 'class.tx_rnbase.php');

/**
 * Generischer Linker für eine Detailseite
 *
 * // Linker Instanz
 * $linker = tx_rnbase::makeInstance(
 *			'tx_mklib_mod1_linker_ShowDetails',
 *			'couponGroup'
 *		)
 * // einen Button, welcher auf die Detailseite zeigt erstellen.
 * $linker->makeLink($item, $mod->getFormTool());
 * // einen Button, welcher wieder auf die Übersichtsseite zeigt erstellen.
 * $linker->makeClearLink($item, $mod->getFormTool());
 *
 * // Liefert die Uid des Datensatzes für die Detailseite, falls vorhanden?
 * // Die ID wird aus den Parametern oder aud den Modul-Daten geholt
 * // Dabei wird gleichzeitig das Clear Event geprüft!
 * $linker->getCurrentUid($mod);
 *
 * @TODO: UnitTests!!!
 *
 * @author Michael Wagner <michael.wagner@das-medienkombinat.de>
 */
class tx_mklib_mod1_linker_ShowDetails {

	private $identifier = null;

	/**
	 *
	 * @throws InvalidArgumentException
	 * @param string $identifier (model or tablename)
	 * 		Wird zum Speichern in den Moduldaten,
	 * 		zum erzeugen der buttons und
	 * 		zum auslesend er Parameter verwendet
	 */
	public function __construct($identifier) {
		if (empty($identifier)) {
			throw new InvalidArgumentException(
				'Constructor needs a valid identifier'
			);
		}
		$this->identifier = $identifier;
	}

	/**
	 *
	 * @param tx_rnbase_model_base $item
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param array $options
	 * @return string
	 */
	public function makeLink(
			tx_rnbase_model_base $item,
			tx_rnbase_util_FormTool $formTool,
			$options=array()
	) {
		$out = $formTool->createSubmit(
				'showDetails['.$this->identifier.']['.$item->getUid().']',
				isset($options['label']) ? $options['label'] : '###LABEL_SHOW_DETAILS###',
				isset($options['confirm']) ? $options['confirm'] : '',
				$options
			);
		return $out;
	}

	/**
	 *
	 * @param tx_rnbase_util_FormTool $formTool
	 * @param array $options
	 * @return string
	 */
	public function makeClearLink(
			tx_rnbase_model_base $item, // wird eigentlich nicht benötigt.
			tx_rnbase_util_FormTool $formTool,
			$options=array()
	) {
		$out = $formTool->createSubmit(
				'showDetails['.$this->identifier.'][clear]',
				isset($options['label']) ? $options['label'] : '###LABEL_BTN_NEWSEARCH###',
				isset($options['confirm']) ? $options['confirm'] : '',
				$options
			);
		return $out;
	}

	/**
	 *
	 * @param tx_rnbase_mod_IModule $mod
	 */
	public function getCurrentUid(
		tx_rnbase_mod_IModule $mod
	) {

		$modSettings = array(
			$this->identifier => '0',
		);

		$params = t3lib_div::_GP('showDetails');
		$params = is_array($params) ? $params : array();
		list($model, $uid) = each($params);
		if (is_array($uid)) {
			list($uid, ) = each($uid);
		}

		if (
			!empty($uid)
			&& $uid === 'clear'
		){
			t3lib_BEfunc::getModuleData(
				$modSettings,
				$modSettings,
				$mod->getName()
			);
			return 0;
		}
		// else

		$uid = intval($uid);
		$data = t3lib_BEfunc::getModuleData(
			$modSettings,
			$uid
				? array(
					$this->identifier => $uid,
				) : array(),
			$mod->getName()
		);

		return intval($data[$this->identifier]);
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/linker/class.tx_mklib_mod1_linker_ShowDetails.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/linker/class.tx_mklib_mod1_linker_ShowDetails.php']);
}