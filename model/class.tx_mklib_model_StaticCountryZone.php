<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_model
 *
 *  Copyright notice
 *
 *  (c) 2011 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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

tx_rnbase::load('tx_rnbase_model_base');

/**
 * @author Hannes Bochmann <hannes.bochmann@dmk-ebusiness.de>
 * @package tx_mklib
 * @subpackage tx_mklib_model
 */
class tx_mklib_model_StaticCountryZone extends tx_rnbase_model_base
{

    /**
     * @var array
     */
    private static $instances = array();

    /**
     * @param   mixed $rowOrUid
     * @return tx_mklib_model_StaticCountryZone
     */
    public static function getInstance($rowOrUid = null)
    {
        // Instanzieren, wenn nicht im Cache oder ein Record übergeben wurde.
        if (is_array($rowOrUid) || !isset(self::$instances[$rowOrUid])) {
            $item = tx_rnbase::makeInstance('tx_mklib_model_StaticCountryZone', $rowOrUid);
            // Nur das erzeugte Model zurückgeben
            if (is_array($rowOrUid)) {
                return $item;
            }
            // else, Model Cachen, wenn eine uid übergeben wurde
            self::$instances[$rowOrUid] = $item;
        }

        return self::$instances[$rowOrUid];
    }

    /**
     * Liefert den Namen der Tabelle
     * @return Tabellenname als String
     */
    public function getTableName()
    {
        return 'static_country_zones';
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/model/class.tx_mklib_model_StaticCountry.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/model/class.tx_mklib_model_StaticCountry.php']);
}
