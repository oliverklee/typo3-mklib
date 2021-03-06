<?php
/**
 * @package tx_mklib
 * @subpackage tx_mklib_util
 * @author Michael Wagner
 *
 *  Copyright notice
 *
 *  (c) 2011 michael Wagner <michael.wagner@dmk-ebusiness.de>
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
tx_rnbase::load('tx_rnbase_util_Misc');
tx_rnbase::load('tx_rnbase_util_DB');
tx_rnbase::load('tx_rnbase_util_Strings');

if (tx_rnbase_util_Extensions::isLoaded('dam')) {
    require_once(tx_rnbase_util_Extensions::extPath('dam').'tca_media_field.php');
}

/**
 * Klasse für Basisfunktionalitäten mit der DAM Extension
 *
 * @author  Michael Wagner
 * @package tx_mklib
 * @subpackage tx_mklib_util
 *
 * funktioniert nur bis TYPO 6.x da ab dann kein DAM mehr.
 * @TODO FAL Util bereitstellen mit gleicher API
 * @TODO diese klasse sollte ein wrapper sein um abhängig von TYPO3 Version
 * auf DAM oder FAL zu gehen.
 */
class tx_mklib_util_DAM
{

    /**
     * Prüft, ob Dam installiert ist.
     *
     * @return bool
     */
    public static function isLoaded()
    {
        return tx_rnbase_util_Extensions::isLoaded('dam');
    }

    /**
     * Gibt DAM Records von definierten UIDs zurück.
     *
     * @param int           $iUid
     * @return array|null
     */
    public static function getRecords($aUid)
    {
        if (!self::isLoaded()) {
            return array('files' => array(), 'rows' => array());
        }
        if (!is_array($aUid)) {
            $aUid = array($aUid);
        }
        $aRes = tx_dam_db::getDataWhere(false, array('uid' => 'tx_dam.uid IN ('.implode(',', $aUid).')'));

        $aFiles = $aRows = array();
        if (count($aRes)) {
            foreach ($aRes as $aRow) {
                $aFiles[$aRow['uid']] = $aRow['file_path'].$aRow['file_name'];
                $aRows[$aRow['uid']] = $aRow;
            }
        }

        return array('files' => $aFiles, 'rows' => $aRows);
    }
    /**
     * Gibt Fileinfos von DAM Records von definierten UIDs zurück.
     *
     * @param int           $iUid
     * @return array|null
     */
    public static function getRecordsFileInfo($aUid)
    {
        if (!self::isLoaded()) {
            return array();
        }
        $aRefs = self::getRecords($aUid);

        return self::getFileInfos($aRefs);
    }

    /**
     * Fügt eine Referenz hinzu
     *
     * @param string        $sTableName
     * @param string        $sFieldName
     * @param int           $iItemId (Referenz Datensatz)
     * @param int           $iUid (DAM Datensatz)
     * @param bool       $bUpdateCount
     * @return int
     */
    public static function addReference($sTableName, $sFieldName, $iItemId, $iUid, $bUpdateCount = true)
    {
        if (!self::isLoaded()) {
            return 0;
        }
        $aData = array();
        $aData['uid_foreign'] = $iItemId;
        $aData['uid_local'] = $iUid;
        $aData['tablenames'] = $sTableName;
        $aData['ident'] = $sFieldName;

        $iId = tx_rnbase_util_DB::doInsert('tx_dam_mm_ref', $aData);

        // Now count all items
        if ($bUpdateCount) {
            self::updateImageCount($sTableName, $sFieldName, $iItemId);
        }

        return $iId;
    }

    /**
     * Löscht eine Referenz
     *
     * @param string        $sTableName
     * @param string        $sFieldName
     * @param int           $iItemId
     * @param string|int    $iUid           Optional: Kommaseparierte liste mit Uids
     * @param bool       $bUpdateCount
     */
    public static function deleteReferences($sTableName, $sFieldName, $iItemId, $mUid = '', $bUpdateCount = true)
    {
        if (!self::isLoaded()) {
            return false;
        }
        if (!empty($iItemId)) {
            $sWhere = 'tablenames=\'' . $sTableName . '\' AND ident=\'' . $sFieldName .'\' AND uid_foreign=' . $iItemId;
            if (strlen(trim($mUid))) {
                $mUid = implode(',', tx_rnbase_util_Strings::intExplode(',', $mUid));
                $sWhere .= ' AND uid_local IN (' . $mUid .') ';
            }
            tx_rnbase_util_DB::doDelete('tx_dam_mm_ref', $sWhere);
            // Jetzt die Bildanzahl aktualisieren
            if ($bUpdateCount) {
                self::updateImageCount($sTableName, $sFieldName, $iItemId);
            }
        }
    }

    /**
     * Die Bildanzahl aktualisieren
     *
     * @param string        $sTableName
     * @param string        $sFieldName
     * @param int           $iItemId
     */
    public static function updateImageCount($sTableName, $sFieldName, $iItemId)
    {
        if (!self::isLoaded()) {
            return false;
        }
        $aValues = array();
        $aValues[$sFieldName] = self::getImageCount($sTableName, $sFieldName, $iItemId);
        tx_rnbase_util_DB::doUpdate($sTableName, 'uid='.$iItemId, $aValues, 0);
    }

    /**
     * Die Bildanzahl auslesen
     *
     * @param string        $sTableName
     * @param string        $sFieldName
     * @param int           $iItemId
     * @return int
     */
    public static function getImageCount($sTableName, $sFieldName, $iItemId)
    {
        if (!self::isLoaded()) {
            return 0;
        }
        $aOptions['where'] = 'tablenames=\'' . $sTableName . '\' AND ident=\'' . $sFieldName .'\' AND uid_foreign=' . intval($iItemId);
        $aOptions['count'] = 1;
        $aOptions['enablefieldsoff'] = 1;
        $ret = tx_rnbase_util_DB::doSelect('count(*) AS \'cnt\'', 'tx_dam_mm_ref', $aOptions, 0);

        return count($ret) ? intval($ret[0]['cnt']) : 0;
    }

    /**
     * Gibt die Anzahl der Referenzen zurück.
     *
     * @param   string      $sTableName
     * @param   int         $iItemId
     * @param   string      $sFieldName
     * @return  int
     */
    public static function getReferencesCount($sTableName, $iItemId, $sFieldName)
    {
        if (!self::isLoaded()) {
            return 0;
        }
        require_once(tx_rnbase_util_Extensions::extPath('dam') . 'lib/class.tx_dam_db.php');

        $ret = tx_dam_db::getReferencedFiles(
            $sTableName,
            $iItemId,
            $sFieldName,
            'tx_dam_mm_ref',
            '\'ret\' as uid, count(tx_dam.uid) as cnt',
            array(),
            '',
            '',
            1
        );

        return intval($ret['rows']['ret']['cnt']);
    }

    /**
     * Gibt alle Referenzen zurück
     *
     * @param   string      $sTableName
     * @param   int             $iItemId
     * @param   string      $sFieldName
     * @param   array       $options
     * @return  array
     */
    public static function getReferences($sTableName, $iItemId, $sFieldName, $options = array())
    {
        if (!self::isLoaded()) {
            return array('files' => array(), 'rows' => array());
        }
        require_once(tx_rnbase_util_Extensions::extPath('dam') . 'lib/class.tx_dam_db.php');

        $files = tx_dam_db::getReferencedFiles($sTableName, $iItemId, $sFieldName);

        return self::wrapReferencesResult($files, $options);
    }

    /**
     * Wrappt das ergebnis von einer referenzen abfrage
     *
     * @param array $files
     * @param array $options
     *
     * @return array
     */
    protected static function wrapReferencesResult($files, $options = array())
    {
        // den record in ein model wrappen?
        $wrapperClass = $options['wrapperclass'] === true ? 'tx_mklib_model_Dam' :
                            (is_string($options['wrapperclass']) ? trim($options['wrapperclass']) : false);
        if ($wrapperClass && !empty($files['rows'])) {
            foreach ($files['rows'] as $uid => $record) {
                $files['rows'][$uid] = tx_rnbase::makeInstance($wrapperClass, $record);
            }
        }
        // Der Index ist immer die Uid.
        // Beim ListBuilder oä. kann das zu Problemen führen.
        if ($options['resetindex']) {
            $files['rows'] = array_merge($files['rows']);
            $files['files'] = array_merge($files['files']);
        }

        return $files;
    }

    /**
     * Hat der DAM Eintrag noch Referenzen?
     *
     * @return  bool
     */
    public static function damRecordHasReferences($iLocalUid, $foreign_table = '', $foreign_uid = '', $MM_ident = '', $MM_table = 'tx_dam_mm_ref', $options = array())
    {
        if (!self::isLoaded()) {
            return false;
        }
        require_once(tx_rnbase_util_Extensions::extPath('dam') . 'lib/class.tx_dam_db.php');

        $fields = tx_dam_db::getMetaInfoFieldList();
        $res = tx_dam_db::referencesQuery('tx_dam', $iLocalUid, $foreign_table, $foreign_uid, $MM_ident, $MM_table, $fields);

        if ($res && $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            return true;
        }
        //else
        return false;
    }

    /**
     * Setzt einen Dam Record auf hidden
     *
     * @todo nicht nur verstecken sondern auch löschen integrieren
     * @param array $aDamRecord sollte nur einen record in ['rows'] enthalten
     * @param int $iMode verstecken, auf deleted setzen oder ganz löschen
     * @param bool $bDeletePicture
     *
     * @return bool
     */
    public static function deleteDamRecord($aDamRecords, $iMode = 0, $bDeletePicture = false)
    {
        if (empty($aDamRecords['rows'])) {
            return false;
        }
        foreach ($aDamRecords['rows'] as $iDam => $row) {
            //wenn wir nur keine referenzen mehr haben dann können wir das bild und
            //den eigentlichen eintrag löschen
            if (!self::damRecordHasReferences($iDam)) {
                //dam eintrag und bild löschen
                tx_rnbase::load('tx_rnbase_util_DB');
                switch ($iMode) {
                    case 0://verstecken
                    default:
                        tx_rnbase_util_DB::doUpdate('tx_dam', 'tx_dam.uid = '.$iDam, array('hidden' => 1));
                        break;
                    case 1://löschen
                        tx_rnbase_util_DB::doUpdate('tx_dam', 'tx_dam.uid = '.$iDam, array('deleted' => 1));
                        break;
                    case 2://hart löschen
                        tx_rnbase_util_DB::doDelete('tx_dam', 'tx_dam.uid = '.$iDam);
                        break;
                }

                //und bild löschen?
                if ($bDeletePicture) {
                    unlink(
                        PATH_site.'/'.
                        $aDamRecords['files'][$iDam]
                    );
                }
            }
        }

        return true;
    }

    /**
     * Löscht die Referenzen (auf jeden fall),
     * den DAM Eintrag (evtl.) und das Bild (evtl.)
     *
     * @param string $sTableName
     * @param int $iItemId
     * @param string $sFieldName
     * @param int $iMode
     * @param bool $bDeletePicture
     *
     * @return array wie viele referenzen und dam einträge wurden gelöscht
     */
    public function handleDelete($sTableName, $iItemId, $sFieldName, $iMode = 0, $bDeletePicture = false)
    {
        $iDeletedReferences = $iDeletedRecords = 0;
        //dazu holen wir uns alle dam referenzen
        $aReferences = self::getReferences($sTableName, $iItemId, $sFieldName);
        if (!empty($aReferences['rows'])) {
            //zu den referenzen holen wir uns die eigentlichen dam records
            foreach ($aReferences['rows'] as $uid => $row) {
                //jetzt können wir erstmal die referenz löschen da wir sie nicht mehr benötigen
                self::deleteReferences($sTableName, $sFieldName, $iItemId);
                $iDeletedReferences++;
                //kann nur einer sein.
                $aDamRecord = self::getRecords($uid);
                //und dam eintrag selbst löschen wenn dieser keine weiteren referenzen hat
                if (self::deleteDamRecord($aDamRecord, $iMode, $bDeletePicture)) {
                    $iDeletedRecords++;
                }
            }
        }

        return array('deletedReferences' => $iDeletedReferences,'deletedRecords' => $iDeletedRecords);
    }

    /**
     * Return file info for all references for the given reference data
     *
     * @param string    $sTableName
     * @param int       $iItemId
     * @param string    $sFieldName
     * @return array
     */
    public static function getReferencesFileInfo($sTableName, $iItemId, $sFieldName)
    {
        if (!self::isLoaded()) {
            return array();
        }
        $aRefs = self::getReferences($sTableName, $iItemId, $sFieldName);

        return self::getFileInfos($aRefs);
    }

    /**
     * Return file info for all records
     *
     * @param array     $aRows
     * @return array
     */
    private static function getFileInfos($aRows)
    {
        if (isset($aRows['rows']) && count($aRows['rows'])) {
            $aRes = array();
            foreach ($aRows['rows'] as $uid => $record) {
                $aFileInfo = self::getFileInfo($record);
                if (isset($aRows['files'][$uid])) {
                    $aFileInfo['file_path_name'] = $aRows['files'][$uid];
                }
                $aFileInfo['file_abs_url'] = tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL') . $aFileInfo['file_path_name'];
                $aRes[$uid] = $aFileInfo;
            }

            return $aRes;
        }
    }

    /**
     * Return first reference for the given reference data
     *
     * @param string    $sTableName
     * @param int       $iItemId
     * @param string    $sFieldName
     * @return array
     */
    public static function getFirstReference($sTableName, $iItemId, $sFieldName)
    {
        if (!self::isLoaded()) {
            return array();
        }
        $refs = self::getReferences($sTableName, $iItemId, $sFieldName);
        if (!empty($refs)) {
            $res = array();
            // Loop through all data ...
            foreach ($refs as $key => $data) {
                // ... and use only the first record WITH its uid!
                $uid = key($refs[$key]);
                $res[$key] = array($uid => $data[$uid]);
            }

            return $res;
        }
    }

    /**
     * Return file information for a dam record
     *
     * @param array $row    $damrecord['rows'][uid] data array, i.e. the actual database table record
     * @return array
     */
    public static function getFileInfo(array $row)
    {
        if (!self::isLoaded()) {
            return array();
        }
        $res = array();
        foreach ($row as $key => $value) {
            if (substr($key, 0, 4) == 'file') {
                $res[$key] = $value;
            }
        }

        return $res;
    }

    /**
     * Return file info of first reference for the given reference data
     *
     * @param string $refTable
     * @param int $refUid
     * @param string $refField
     * weshalb dort die uid nich
     */
    public static function getFirstReferenceFileInfo($refTable, $refUid, $refField)
    {
        if (!self::isLoaded()) {
            return array();
        }
        $ref = self::getFirstReference($refTable, $refUid, $refField);
        if (isset($ref['rows']) && is_array($ref['rows'])
            && count($ref['rows']) && $firstKey = key($ref['rows'])
          ) {
            if ($firstKey) {
                $res = self::getFileInfo($ref['rows'][$firstKey]);
                if (isset($ref['files'][$firstKey])) {
                    $res['file_path_name'] = $ref['files'][$firstKey];
                }
                $res['file_abs_url'] = tx_rnbase_util_Misc::getIndpEnv('TYPO3_SITE_URL') . $res['file_path_name'];
                $res['file_uid'] = $ref['rows'][$firstKey]['uid'];
            }

            return $res ? $res : null;
        }
    }

    /**
     * Indiziert eine Datei mit DAM.
     *
     * @param string    $sFile
     * @param int       $iBeUserId
     * @return uid
     */
    public static function indexProcess($sFile, $iBeUserId = false)
    {
        if (!self::isLoaded()) {
            return 0;
        }
        if (!$iBeUserId) {
            tx_rnbase::load('tx_mklib_util_MiscTools');
            $iBeUserId = tx_mklib_util_MiscTools::getProxyBeUserId();
        }
        // process file indexing
        self::initBE4DAM($iBeUserId);
        $damData = tx_dam::index_process($sFile);

        return $damData[0]['uid'];
    }
    /**
     * Bereitet das Backend für die Indizierung einer Datei vor
     *
     * @param int       $iBeUserId
     */
    private static function initBE4DAM($beUserId)
    {
        global $PAGES_TYPES, $BE_USER;
        if (!is_array($PAGES_TYPES) || !array_key_exists(254, $PAGES_TYPES)) {
            // SysFolder als definieren
            $PAGES_TYPES[254] = array(
                'type' => 'sys',
                'icon' => 'sysf.gif',
                'allowedTables' => '*',
            );
        }
        // Check BE User
        if (!is_object($BE_USER) || !is_array($BE_USER->user)) {
            if (!$beUserId) {
                throw tx_rnbase::makeInstance(
                    'tx_mklib_exception_NoBeUser',
                    'NO BE User id given!'
                );
            }
            unset($BE_USER);
            $BE_USER = tx_rnbase::makeInstance('t3lib_tsfeBeUserAuth');
            $BE_USER->OS = TYPO3_OS;
            $BE_USER->setBeUserByUid($beUserId);
            $BE_USER->fetchGroupData();
            $BE_USER->backendSetUC();
            // Ohne Admin-Rechte gibt es leider Probleme bei der Verarbeitung mit der TCE.
            $BE_USER->user['admin'] = 1;
            $GLOBALS['BE_USER'] = $BE_USER;
        }

        if (!$GLOBALS['LANG']) {
            // Bei Ajax-Calls fehlt das Objekt
            require_once(tx_rnbase_util_Extensions::extPath('lang').'lang.php');
            $GLOBALS['LANG'] = tx_rnbase::makeInstance('language');
            $GLOBALS['LANG']->init($BE_USER->uc['lang']);
        }
    }
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_DAM.php']) {
    include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/mklib/util/class.tx_mklib_util_DAM.php']);
}
