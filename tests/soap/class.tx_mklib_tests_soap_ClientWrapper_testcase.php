<?php
/*
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

tx_rnbase::load('tx_rnbase_tests_BaseTestCase');
tx_rnbase::load('tx_mklib_soap_ClientWrapper');
tx_rnbase::load('tx_mklib_tests_Util');

/**
 * @author Hannes Bochmann
 */
class tx_mklib_tests_soap_ClientWrapper_testcase extends tx_rnbase_tests_BaseTestCase
{
    const SOAP_TEST_METHOD = 'mySoapTestMethod';
    const SOAP_TEST_METHOD_RETURN_VALUE = 'myTestSoapMethodResult';

    public function setUp()
    {
        if (!extension_loaded('soap')) {
            $this->markTestSkipped('Skipped because soap is not installed.');
        }

        tx_mklib_tests_Util::disableDevlog();
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodWithValidMethodReturnsExpectedResult()
    {
        $soapClientWrapper = $this->getSoapClientWrapper();
        $soapMethodReturnValue = $soapClientWrapper->callSoapMethod(self::SOAP_TEST_METHOD);

        self::assertEquals(self::SOAP_TEST_METHOD_RETURN_VALUE, $soapMethodReturnValue);
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodWithValidMethodAndParamsAsArrayReturnsExpectedResult()
    {
        $expectedSoapMethodParams = array('someParam' => 'usedInSoapMethod');
        $soapClientWrapper = $this->getSoapClientWrapper($expectedSoapMethodParams);
        $soapMethodReturnValue = $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            array($expectedSoapMethodParams)
        );

        self::assertEquals(self::SOAP_TEST_METHOD_RETURN_VALUE, $soapMethodReturnValue);
    }

    /**
     * @group unit
     */
    public function testCallSoapMethodWithValidMethodAndParamsAsStringReturnsExpectedResult()
    {
        $expectedSoapMethodParams = 'soapMethodParam';
        $soapClientWrapper = $this->getSoapClientWrapper($expectedSoapMethodParams);
        $soapMethodReturnValue = $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            array($expectedSoapMethodParams)
        );

        self::assertEquals(self::SOAP_TEST_METHOD_RETURN_VALUE, $soapMethodReturnValue);
    }

    /**
     * @group unit
     * @expectedException RuntimeException
     * @expectedExceptionCode 987654321
     * @expectedExceptionMessage There was a Soap Exception
     */
    public function testCallSoapMethodWithInvalidMethodThrowsCorrectException()
    {
        $expectedSoapMethodParams = array('someParam' => 'usedInSoapMethod');
        $soapException = new Exception('There was a Soap Exception', 987654321);
        $soapClientWrapper = $this->getSoapClientWrapper(
            $expectedSoapMethodParams,
            $soapException,
            self::exactly(2)
        );
        $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            array($expectedSoapMethodParams)
        );
    }

    /**
     * @group unit
     * @expectedException RuntimeException
     * @expectedExceptionCode 987654321
     * @expectedExceptionMessage There was a Soap Fault
     */
    public function testCallSoapMethodHandlesSoapFaultCorrect()
    {
        $expectedSoapMethodParams = array('someParam' => 'usedInSoapMethod');
        $soapException = new SoapFault('987654321', 'There was a Soap Fault');
        $soapClientWrapper = $this->getSoapClientWrapper(
            $expectedSoapMethodParams,
            $soapException,
            self::exactly(2)
        );
        $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            array($expectedSoapMethodParams)
        );
    }
    /**
     * @group unit
     * @expectedException RuntimeException
     * @expectedExceptionCode 0
     * @expectedExceptionMessage There was a Soap Fault
     */
    public function testCallSoapMethodHandlesSoapFaultWithStringCodeCorrect()
    {
        $expectedSoapMethodParams = array('someParam' => 'usedInSoapMethod');
        $soapException = new SoapFault('a string code', 'There was a Soap Fault');
        $soapClientWrapper = $this->getSoapClientWrapper(
            $expectedSoapMethodParams,
            $soapException,
            self::exactly(2)
        );
        $soapClientWrapper->callSoapMethod(
            self::SOAP_TEST_METHOD,
            array($expectedSoapMethodParams)
        );
    }

    /**
     * @param string $soapMethodReturnValue
     * @param array $expectedParams
     * @return tx_mklib_soap_ClientWrapper
     */
    private function getSoapClientWrapper(
        $expectedParams = array(),
        $exceptionToThrow = null,
        $getSoapClientInvocationCount = null
    ) {
        $soapClient = $this->getSoapClientMock($expectedParams, $exceptionToThrow);

        $soapClientWrapper = $this->getMock('tx_mklib_soap_ClientWrapper', array('getSoapClient'));

        if (!$getSoapClientInvocationCount) {
            $getSoapClientInvocationCount = self::once();
        }

        $soapClientWrapper->expects($getSoapClientInvocationCount)
            ->method('getSoapClient')
            ->will(self::returnValue($soapClient));

        return $soapClientWrapper;
    }

    /**
     * @param string $soapMethodReturnValue
     * @param array $expectedParams
     * @return SoapClient
     */
    private function getSoapClientMock(
        $expectedParams = array(),
        $exceptionToThrow = null
    ) {
        $soapClient = $this->getMock(
            'SoapClient',
            array(self::SOAP_TEST_METHOD),
            array(),
            '',
            false
        );

        if (!is_null($exceptionToThrow)) {
            $methodAction = $this->throwException($exceptionToThrow);
        } else {
            $methodAction = self::returnValue(self::SOAP_TEST_METHOD_RETURN_VALUE);
        }

        if (!empty($expectedParams)) {
            $soapClient->expects(self::once())
                ->method(self::SOAP_TEST_METHOD)
                ->will($methodAction)
                ->with($expectedParams);
        } else {
            $soapClient->expects(self::once())
                ->method(self::SOAP_TEST_METHOD)
                ->will($methodAction);
        }

        return $soapClient;
    }
}
