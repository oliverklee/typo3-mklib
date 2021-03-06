<?php
/**
 * @package tx_mkdownloads
 * @subpackage tx_mkdownloads_marker
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


tx_rnbase::load('tx_mklib_marker_MediaRecord');

/**
 * Diese Klasse ist für die Erstellung von Markerarrays der Section
 *
 * @package tx_mkdownloads
 * @subpackage tx_mkdownloads_marker
 * @author Michael Wagner
 * @deprecated use tx_mklib_marker_MediaRecord
 */
class tx_mklib_marker_DAMRecord extends tx_mklib_marker_MediaRecord
{
}


if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/marker/class.tx_mklib_marker_DAMRecord.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/marker/class.tx_mklib_marker_DAMRecord.php']);
}
