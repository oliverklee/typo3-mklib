<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_mod1
 *
 *  Copyright notice
 *
 *  (c) 2012 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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


tx_rnbase::load('tx_rnbase_util_ListMarker');
tx_rnbase::load('tx_mklib_mod1_export_Util');

/**
 * Base class for Markers.
 *
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_mod1_export_ListMarker extends tx_rnbase_util_ListMarker
{

    /**
     * Callback function for next item
     * @param object $data
     */
    public function renderNext($data)
    {
        $data->record['roll'] = $this->rowRollCnt;
        $data->record['line'] = $this->i; // Marker für aktuelle Zeilenummer
        $data->record['totalline'] = $this->i + $this->totalLineStart + $this->offset; // Marker für aktuelle Zeilenummer der Gesamtliste
        $this->handleVisitors($data);
        $part = $this->entryMarker->parseTemplate($this->info->getTemplate($data), $data, $this->formatter, $this->confId, $this->marker);

        tx_mklib_mod1_export_Util::doOutPut($part);

        $this->rowRollCnt = ($this->rowRollCnt >= $this->rowRoll) ? 0 : $this->rowRollCnt + 1;
        $this->i++;
    }

    /**
     * Call all visitors for an item
     * @param object $data
     */
    protected function handleVisitors($data)
    {
        if (!is_array($this->visitors)) {
            return;
        }
        foreach ($this->visitors as $visitor) {
            call_user_func($visitor, $data);
        }
    }

    /**
     * Render an array of objects
     * @param array $dataArr
     * @param string $template
     * @param string $markerClassname
     * @param string $confId
     * @param string $marker
     * @param tx_rnbase_util_FormatUtil $formatter
     * @param mixed $markerParams
     * @param int $offset
     * @return array
     */
    public function render($dataArr, $template, $markerClassname, $confId, $marker, &$formatter, $markerParams = false, $offset = 0)
    {
        $out = parent::render($dataArr, $template, $markerClassname, $confId, $marker, $formatter, $markerParams, $offset);
        tx_mklib_mod1_export_Util::doOutPut($out);

        return '';
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_ListMarker.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/mod1/export/class.tx_mklib_mod1_export_ListMarker.php']);
}
