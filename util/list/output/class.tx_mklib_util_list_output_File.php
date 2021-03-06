<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_util
 *
 *  Copyright notice
 *
 *  (c) 2013 DMK E-BUSINESS GmbH <dev@dmk-ebusiness.de>
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



tx_rnbase::load('tx_mklib_util_list_output_Interface');

/**
 * Interface für Ausgaben des Listbuilders
 *
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner <michael.wagner@dmk-ebusiness.de>
 */
class tx_mklib_util_list_output_File implements tx_mklib_util_list_output_Interface
{

    /**
     * @var string
     */
    private $filename = '';
    /**
     * @var string ressource
     */
    private $fileHandler = null;
    
    public function __construct($data)
    {
        if (is_array($data) && array_key_exists('file', $data)) {
            $this->filename = $data['file'];
        }
        if ($this->filename != '') {
            $this->fileHandler = fopen($this->filename, 'wb');
        }
    }
    
    public function __destruct()
    {
        if ($this->fileHandler) {
            fclose($this->fileHandler);
        }
    }
    
    public function handleOutput($output = '')
    {
        if ($this->fileHandler && $output != '') {
            fwrite($this->fileHandler, $output);
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/output/class.tx_mklib_util_list_output_File.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/list/output/class.tx_mklib_util_list_output_File.php']);
}
