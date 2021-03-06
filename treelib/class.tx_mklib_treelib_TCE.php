<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_treelib
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 Michael Wagner <michael.wagner@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_parameters');

/**
 * Basisklasse, um eine Baumstruktur abzubilden.
 *
 * @package tx_mklib
 * @subpackage tx_mklib_treelib
 * @author Michael Wagner
 * @deprecated since TYPO3 7.6. use core feature for tree views instead since TYPO3 7.6
 * @todo remove when support for TYPO3 6.2 is dropped
 */
class tx_mklib_treelib_TCE
{
    /**
     *
     * @var \TYPO3\CMS\Backend\Form\FormEngine
     */
    private $oTceForm = null;
    /**
     * @var array
     */
    private $PA = array();

    /**
     *
     * @param   array           $PA
     * @param   \TYPO3\CMS\Backend\Form\FormEngine  $fObj
     * @return  string
     */
    public function getSelectTree(&$PA, &$pObj)
    {
        $this->oTceForm = &$PA['pObj'];
        $this->PA = &$PA;

        tx_rnbase::load('tx_mklib_treelib_TreeView');
        $oTreeView = tx_mklib_treelib_TreeView::makeInstance($PA, $pObj);

        tx_rnbase::load('tx_mklib_treelib_Renderer');
        $oRenderer = tx_mklib_treelib_Renderer::makeInstance($PA, $pObj);

        $sContent = $oRenderer->renderTreeView($oTreeView, $this);

        return $sContent;
    }

    /**
     * Liefert
     *
     * @param   string  $cmd
     * @return  tx_xajax_response
     */
    public function sendXajaxResponse($cmd)
    {
        tx_rnbase_parameters::setGetParameter($cmd, 'PM');

        //@TODO: ist $this->PA immer gleich? mehrere treeviews beachten
        tx_rnbase::load('tx_mklib_treelib_TreeView');
        $oTreeView = tx_mklib_treelib_TreeView::makeInstance($this->PA, $this->oTceForm);

        tx_rnbase::load('tx_mklib_treelib_Renderer');
        $oRenderer = tx_mklib_treelib_Renderer::makeInstance($this->PA, $this->oTceForm);

        $sContent = $oRenderer->getBrowsableTree($oTreeView);

        // ajax response erstellen
        /* @var $objResponse tx_xajax_response */
        $objResponse = tx_rnbase::makeInstance('tx_xajax_response');

        $objResponse->addAssign($oTreeView->treeName.'-tree-div', 'innerHTML', $sContent);

        return $objResponse;
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_TCE.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/treelib/class.tx_mklib_treelib_TCE.php']);
}
