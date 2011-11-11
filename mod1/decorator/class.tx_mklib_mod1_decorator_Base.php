<?php
/**
 * 	@package tx_mklib
 *  @subpackage tx_mklib_mod1
 *  @author Hannes Bochmann
 *
 *  Copyright notice
 *
 *  (c) 2011 Hannes Bochmann <hannes.bochmann@das-medienkombinat.de>
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
tx_rnbase::load('tx_rnbase_mod_IDecorator');

/**
 * Diese Klasse ist für die Darstellung von Elementen im Backend verantwortlich.
 * 
 * @package tx_mklib
 * @subpackage tx_mklib_mod1
 */
class tx_mklib_mod1_decorator_Base implements tx_rnbase_mod_IDecorator{
	
	/**
	 * 
	 * @param 	tx_rnbase_mod_IModule 	$mod
	 */
	public function __construct(tx_rnbase_mod_IModule $mod) {
		$this->mod = $mod;
	}
	
	
	/**
	 * 
	 * @param 	string 					$value
	 * @param 	string 					$colName
	 * @param 	array 					$record
	 * @param 	tx_rnbase_model_base 	$item
	 */
	public function format($value, $colName, $record, tx_rnbase_model_base $item) {
		$ret = $value;
		
		if($colName == 'uid') {
			$sHiddenColumn = tx_mklib_util_TCA::getEnableColumn($item->getTableName(),  'disabled', 'hidden');
			//fallback
			$mRecordValue = $item->record[$sHiddenColumn] ?
								$item->record[$sHiddenColumn] : 
								$record[$sHiddenColumn];
			// @TODO: hier nicht lieber <del></del> nutzen?
			$wrap = $mRecordValue ? array('<strike>','</strike>') : array('','');
			$ret = $wrap[0].$value.$wrap[1];
		} 
		elseif($colName == 'actions') {
			$ret .= $this->getActions($item, $this->getActionOptions());
		}
		
		return $ret;
	}
	
	/**
	 * Liefert die möglichen Optionen für die actions
	 * @param array
	 */
	protected function getActionOptions() {
		return array(
			'edit' => '',
			'hide' => '',
			'remove' => '',
		);
	}
	
	/**
	 * @TODO: weitere links integrieren!
	 * $options = array('hide'=>'ausblenden,'edit'=>'bearbeiten,'remove'=>'löschen','history'='history','info'=>'info','move'=>'verschieben');
	 * 
	 * @param 	tx_rnbase_model_base 	$item
	 * @param 	array 					$options
	 * @return 	string
	 */
	protected function getActions(tx_rnbase_model_base $item, array $options) {
		$ret = '';
		foreach($options as $sLinkId => $bTitle){
			switch($sLinkId) {
				case 'edit':
					$ret .= $this->getFormTool()->createEditLink($item->getTableName(), $item->getUid(), $bTitle);
					break; 
				case 'hide':
					$sHiddenColumn = tx_mklib_util_TCA::getEnableColumn($item->getTableName(), 'disabled', 'hidden');
					$ret .= $this->getFormTool()->createHideLink($item->getTableName(), $item->getUid(), $item->record[$sHiddenColumn]);
					break;
				case 'remove':
					//Es wird immer ein Bestätigungsdialog ausgegeben!!! Dieser steht
					//in der BE-Modul locallang.xml der jeweiligen Extension im Schlüssel 
					//'confirmation_deletion'. (z.B. mkkvbb/mod1/locallang.xml) Soll kein 
					//Bestätigungsdialog ausgegeben werden, dann einfach 'confirmation_deletion' leer lassen
					$ret .= $this->getFormTool()->createDeleteLink($item->getTableName(), $item->getUid(), $bTitle,array('confirm' => $GLOBALS['LANG']->getLL('confirmation_deletion')));
					break; 
				default:
					break;
			}
		}
		return $ret;
	}
	


	/**
	 * Returns the module
	 * @return tx_rnbase_mod_IModule
	 */
	protected function getModule() {
		return $this->mod;
	}

	/**
	 * Returns an instance of tx_rnbase_mod_IModule
	 * 
	 * @return 	tx_rnbase_util_FormTool
	 */
	protected function getFormTool() {
		return $this->mod->getFormTool();
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/decorator/class.tx_mklib_mod1_decorator_Base.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/mklib/mod1/decorator/class.tx_mklib_mod1_decorator_Base.php']);
}
