<?php
namespace middleware\library;

class Excel
{
    /**
     * 엑셀로 변환할 라인별 데이터
     * @var array
     */
    private static $aRows = array();

    /**
     * addRow 에 사용할 현재 라인 번호
     * @var int
     */
    private static $iCurrentRowNum = 2;

    /**
     * utilExcel 의 객체를 담을 멤버변수
     * @var null
     */
    private static $oInstance = null;

    /**
     * 시트가 생성되었는지 여부
     * @var bool
     */
    private static $bSetActiveSheet = false;

    private static $aExcelReaderTypes = array('Excel2007', 'Excel2003XML', 'Excel5');

    /**
     * 엑셀 라이브러리 추기화
     */
    public static function init()
    {
        self::$aRows = array();
        self::$iCurrentRowNum = 2;
        self::$oInstance = null;
        self::getExcelInstance();
    }

    public static function loadExcelObj($sPath, $sReaderType = null)
    {
        $oPhpExcel = null;
        if (empty($sReaderType)) {
            foreach (self::$aExcelReaderTypes as $sReaderType) {
                $oReader = \PHPExcel_IOFactory::createReader($sReaderType);
                try {
                    $oPhpExcel = $oReader->load($sPath);
                } catch (\PHPExcel_Reader_Exception $e) {
                    continue;
                }
            }
        } else {
            $oReader = \PHPExcel_IOFactory::createReader($sReaderType);
            try {
                $oPhpExcel = $oReader->load($sPath);
            } catch (\PHPExcel_Reader_Exception $e) {
                return null;
            }
        }

        return $oPhpExcel;
    }

    public static function readExcelToArray($sPath, $bAllSheet = false, $sReaderType = null)
    {
        if (is_file($sPath) === false) {
            return false;
        }

        $oPhpExcel = self::loadExcelObj($sPath, $sReaderType);

        if (empty($oPhpExcel) === true) {
            return false;
        }

        $aArrayResult = array();
        foreach ($oPhpExcel->getWorksheetIterator() as $oWorksheet) {
            $aArrayResult[$oWorksheet->getTitle()] = $oWorksheet->toArray();
        }

        if ($bAllSheet === true) {
            return $aArrayResult;
        } else {
            reset($aArrayResult);
            $sKey = key($aArrayResult);

            return $aArrayResult[$sKey];
        }
    }

    /**
     * 지정한 라인에 지정한 데이터를 입력한다.
     * @param $aRowData
     * @param $iRowNum
     * @return bool
     */
    public static function setRow($aRowData, $iRowNum)
    {
        if ($iRowNum < 1) {
            return false;
        }
        self::$aRows[$iRowNum] = $aRowData;

        return true;
    }

    public static function setRowIdx($iIdx)
    {
        self::$iCurrentRowNum = $iIdx;
    }

    /**
     * 지정한 데이터를 엑셀의 첫번째 라인에 입력한다.
     * @param $aHeaderData
     * @return bool
     */
    public static function setHeader($aHeaderData)
    {
        return self::setRow($aHeaderData, 1);
    }

    /**
     * 지정한 데이터를 $iCurrentRowNum 라인에 추가하고,
     * 이미 $iCurrentRowNum 라인이 입력되어 있으면 다음 비어있는 라인에 추가한다.
     * @param $aRowData
     * @return bool
     */
    public static function addRow($aRowData)
    {
        if (isset(self::$aRows[self::$iCurrentRowNum]) === true) {
            self::$iCurrentRowNum++;

            return self::addRow($aRowData);
        }

        return self::setRow($aRowData, self::$iCurrentRowNum++);
    }

    /**
     * 여러 라인의 데이터를 addRow한다.
     * @param $aRowDataList
     * @return bool|int
     */
    public static function addRows($aRowDataList)
    {
        $iCnt = 0;
        foreach ($aRowDataList as $aRowData) {
            $iCnt += self::addRow($aRowData);
        }

        return $iCnt;
    }

    /**
     * 지정한 라인에 지정한 여러 줄의 데이터를 세팅한다.
     * @param $aRowDataList
     * @param $iFromRowNum
     * @return bool|int
     */
    public static function setRows($aRowDataList, $iFromRowNum)
    {
        $iCnt = 0;
        $iRowNum = $iFromRowNum;
        foreach ($aRowDataList as $aRowData) {
            $iCnt += self::setRow($aRowData, $iRowNum);
            $iRowNum++;
        }

        return $iCnt;
    }

    public static function writeFile($sFilePath, $sWriteType = 'xlsx')
    {
        $sWriter = '';
        $sWriteTypeLower = strtolower($sWriteType);

        switch ($sWriteTypeLower) {
            case 'xlsx' :
                $sWriter = 'Excel2007';
                break;
            case 'xls' :
                $sWriter = 'Excel5';
                break;
        }

        if (!empty($sWriter)) {
            $oUtilExcel = self::getExcelInstance();
            $oWriter = \PHPExcel_IOFactory::createWriter($oUtilExcel, $sWriter);
            $oWriter->save($sFilePath . '.' . $sWriteTypeLower);

            return true;
        } else {
            return false;
        }
    }

    /**
     * 입력했던 데이터들을 지정한 파일위치에 엑셀파일로 저장한다.
     * @param $filePath
     * @param string $sWriteType
     * @return bool
     */
    public static function write($filePath, $sWriteType = 'xlsx')
    {
        if (self::_buildExcel() === false) {
            return false;
        }

        return self::writeFile($filePath, $sWriteType);
    }

    /**
     * 입력했던 데이터들을 지정한 파일명의 엑셀파일로 다운로드 시킨다.
     * @param $fileName
     * @param string $sWriteType
     * @param bool $bOrderExcel default : false   if true, use self::_buildExcelForOrder()
     * @return bool
     */
    public static function download($fileName, $sWriteType = 'xlsx', $bOrderExcel = false)
    {
        if ($bOrderExcel) {
            $bRt = self::_buildExcelForOrder();
        } else {
            $bRt = self::_buildExcel();
        }
        if ($bRt === false) {
            return false;
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $fileName . '.' . $sWriteType . '"');

        switch (strtolower($sWriteType)) {
            case 'xlsx':
                $sWriter = 'Excel2007';
                break;
            case 'xls':
                $sWriter = 'Excel5';
                break;
        }

        if (empty($sWriter) === false) {
            $oWriter = \PHPExcel_IOFactory::createWriter(self::getExcelInstance(), $sWriter);
            // Write file to the browser
            $oWriter->save('php://output');
            exit();
        } else {
            return false;
        }
    }

    /**
     * 입력했던 데이터들을 지정한 파일명의 엑셀파일로 다운로드 시킨다.
     * @param $filePath
     * @param string $sWriteType
     * @param bool $bOrderExcel default : false   if true, use self::_buildExcelForOrder()
     * @return bool
     */
    public static function save($filePath, $sWriteType = 'xlsx', $bOrderExcel = false)
    {
        if ($bOrderExcel) {
            $bRt = self::_buildExcelForOrder();
        } else {
            $bRt = self::_buildExcel();
        }
        if ($bRt === false) {
            return false;
        }

        switch (strtolower($sWriteType)) {
            case 'xlsx':
                $sWriter = 'Excel2007';
                break;
            case 'xls':
                $sWriter = 'Excel5';
                break;
        }

        if (!empty($sWriter)) {
            $oWriter = \PHPExcel_IOFactory::createWriter(self::getExcelInstance(), $sWriter);

            return $oWriter->save($filePath . ".{$sWriteType}");
        } else {
            return false;
        }
    }

    /**
     * 입력했던 데이터들을 실제로 utilExcel(PHPExcel)을 사용하여 엑셀형식의 데이터를 입력한다.
     * @return bool
     */
    private static function _buildExcel()
    {
        if (empty(self::$aRows) === true) {
            return false;
        }

        $oUtilExcel = self::getExcelInstance();

        if (self::$bSetActiveSheet === false) {
            $oUtilExcel->setActiveSheetIndex(0);
            self::$bSetActiveSheet = true;
        }

        foreach (self::$aRows as $iRowNum => $aRowData) {
            foreach ($aRowData as $iCellNum => $iCellData) {
                $iCellName = self::_convertCellName($iCellNum);
                $sCellLocation = $iCellName . $iRowNum;
                $oUtilExcel->getActiveSheet()->setCellValue($sCellLocation, $iCellData);
            }
        }

        return true;
    }

    /**
     * add by hrson <hrson@simplexi.com.cn> 2015-06-22
     * modify by gwlee01 2015-07-29
     * also save numeric data by string type to excel col "C".
     * 强行将指定列（C列：快递单号）保存为字符串类型，避免 0 开头的数字编号 丢失0 的情况
     * @return bool
     */
    private static function _buildExcelForOrder()
    {
        if (empty(self::$aRows) === true) {
            return false;
        }

        $oUtilExcel = self::getExcelInstance();

        if (self::$bSetActiveSheet === false) {
            $oUtilExcel->setActiveSheetIndex(0);
            self::$bSetActiveSheet = true;
        }

        foreach (self::$aRows as $iRowNum => $aRowData) {
            foreach ($aRowData as $iCellNum => $iCellData) {
                $iCellName = self::_convertCellName($iCellNum);
                $sCellLocation = $iCellName . $iRowNum;
                $oUtilExcel->getActiveSheet()->setCellValueExplicit($sCellLocation, $iCellData, \PHPExcel_Cell_DataType::TYPE_STRING);
            }
        }

        return true;
    }

    /**
     * 셀의 번호를 셀의 이름(알파벳)으로 변환한다.
     * @param $iCellNum
     * @return string
     */
    private static function _convertCellName($iCellNum)
    {
        $iNumeric = $iCellNum % 26;
        $sLetter = chr(65 + $iNumeric);
        $iNum2 = intval($iCellNum / 26);
        if ($iNum2 > 0) {
            return self::_convertCellName($iNum2 - 1) . $sLetter;
        } else {
            return $sLetter;
        }
    }

    /**
     * utilExcel 인스턴스를 멤버변수에 세팅하고 리턴한다.
     * @return \PHPExcel
     */
    public static function getExcelInstance()
    {
        if (self::$oInstance === null) {
            self::$oInstance = new \PHPExcel();
        }

        return self::$oInstance;
    }

    public static function getExcelDatabyPath($sPath)
    {
        $PHPExcel = \PHPExcel_IOFactory::load($sPath);
        $PHPExcel->setActiveSheetIndex(0);

        return $PHPExcel->getActiveSheet()->toArray();
    }
}
