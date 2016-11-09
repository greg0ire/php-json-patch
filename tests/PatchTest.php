<?php

namespace Remorhaz\JSON\Test\Patch;

use Remorhaz\JSON\Data\RawSelectableReader;
use Remorhaz\JSON\Data\RawSelectableWriter;
use Remorhaz\JSON\Patch\Patch;

class PatchTest extends \PHPUnit_Framework_TestCase
{


    /**
     * @param mixed $data
     * @param array $patchData
     * @param mixed $expectedData
     * @dataProvider providerValidSpecPatch_Result
     */
    public function testApply_ValidSpecPatch_Applied($data, array $patchData, $expectedData)
    {
        $dataWriter = new RawSelectableWriter($data);
        $patchDataReader = new RawSelectableReader($patchData);
        (new Patch($dataWriter))->apply($patchDataReader);
        $this->assertEquals($expectedData, $data);
    }


    public function providerValidSpecPatch_Result(): array
    {
        $dataSetList = [];
        foreach ($this->getSpecTests() as $testInfo) {
            if (isset($testInfo->error) || !isset($testInfo->expected)) {
                continue;
            }
            $dataSet = [
                $testInfo->doc,
                $testInfo->patch,
                $testInfo->expected,
            ];
            if (isset($testInfo->comment)) {
                $dataSetList[$testInfo->comment] = $dataSet;
            } else {
                $dataSetList[] = $dataSet;
            }
        }
        return $dataSetList;
    }


    /**
     * @param mixed $data
     * @param array $patchData
     * @dataProvider providerInvalidPatch
     * @expectedException \RuntimeException
     */
    public function testApply_InvalidSpecPatch_ExceptionThrown($data, array $patchData)
    {
        $dataWriter = new RawSelectableWriter($data);
        $patchDataReader = new RawSelectableReader($patchData);
        (new Patch($dataWriter))->apply($patchDataReader);
    }


    public function providerInvalidSpecPatch(): array
    {
        $dataSetList = [];
        foreach ($this->getSpecTests() as $testInfo) {
            if (!isset($testInfo->error)) {
                continue;
            }
            $dataSet = [
                $testInfo->doc,
                $testInfo->patch,
            ];
            if (isset($testInfo->comment)) {
                $dataSetList[$testInfo->comment] = $dataSet;
            } else {
                $dataSetList[] = $dataSet;
            }
        }
        return $dataSetList;
    }

    /**
     * @param mixed $data
     * @param array $patchData
     * @param mixed $expectedData
     * @dataProvider providerValidPatch_Result
     */
    public function testApply_ValidPatch_Applied($data, array $patchData, $expectedData)
    {
        $dataWriter = new RawSelectableWriter($data);
        $patchDataReader = new RawSelectableReader($patchData);
        (new Patch($dataWriter))->apply($patchDataReader);
        $this->assertEquals($expectedData, $data);
    }


    public function providerValidPatch_Result(): array
    {
        $dataSetList = [];
        foreach ($this->getTests() as $testInfo) {
            if (isset($testInfo->error) || !isset($testInfo->expected)) {
                continue;
            }
            $dataSet = [
                $testInfo->doc,
                $testInfo->patch,
                $testInfo->expected,
            ];
            if (isset($testInfo->comment)) {
                $dataSetList[$testInfo->comment] = $dataSet;
            } else {
                $dataSetList[] = $dataSet;
            }
        }
        return $dataSetList;
    }

    /**
     * @param mixed $data
     * @param array $patchData
     * @dataProvider providerInvalidPatch
     * @expectedException \RuntimeException
     */
    public function testApply_InvalidPatch_ExceptionThrown($data, array $patchData)
    {
        $dataWriter = new RawSelectableWriter($data);
        $patchDataReader = new RawSelectableReader($patchData);
        (new Patch($dataWriter))->apply($patchDataReader);
    }


    public function providerInvalidPatch(): array
    {
        $dataSetList = [];
        foreach ($this->getTests() as $testInfo) {
            if (!isset($testInfo->error)) {
                continue;
            }
            $dataSet = [
                $testInfo->doc,
                $testInfo->patch,
            ];
            if (isset($testInfo->comment)) {
                $dataSetList[$testInfo->comment] = $dataSet;
            } else {
                $dataSetList[] = $dataSet;
            }
        }
        return $dataSetList;
    }


    private function getSpecTests(): array
    {
        $testsFile = dirname(realpath(PHPUNIT_COMPOSER_INSTALL)) . "/../tests/data/spec_tests.json";
        return $this->getTestInfoList($testsFile);
    }


    private function getTests(): array
    {
        $testsFile = dirname(realpath(PHPUNIT_COMPOSER_INSTALL)) . "/../tests/data//tests.json";
        return $this->getTestInfoList($testsFile);
    }


    private function getTestInfoList($fileName): array
    {
        $testInfoListJSON = file_get_contents($fileName);
        $testInfoList = json_decode($testInfoListJSON);
        $isNotDisabledTest = function(\stdClass $testInfo) {
            return !(isset($testInfo->disabled) && $testInfo->disabled);
        };
        return array_filter($testInfoList, $isNotDisabledTest);
    }
}
