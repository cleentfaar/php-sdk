<?php
/**
 * Copyright 2016, Optimizely
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Optimizely\Tests;

use Optimizely\ErrorHandler\NoOpErrorHandler;
use Optimizely\Logger\NoOpLogger;
use Optimizely\ProjectConfig;
use Optimizely\Utils\Validator;


class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public function testValidateJsonSchemaValidFile()
    {
        $this->assertTrue(Validator::validateJsonSchema(DATAFILE));
    }

    public function testValidateJsonSchemaInvalidFile()
    {
        $invalidDatafile = '{"key1": "val1"}';
        $this->assertFalse(Validator::validateJsonSchema($invalidDatafile));
    }

    public function testValidateJsonSchemaNoJsonContent()
    {
        $invalidDatafile = 'some random file.';
        $this->assertFalse(Validator::validateJsonSchema($invalidDatafile));
    }

    public function testAreAttributesValidValidAttributes()
    {
        // Empty attributes
        $this->assertTrue(Validator::areAttributesValid([]));

        // Valid attributes
        $this->assertTrue(Validator::areAttributesValid([
            'location' => 'San Francisco',
            'browser' => 'Firefox'
        ]));
    }

    public function testAreAttributesValidInvalidAttributes()
    {
        // String as attributes
        $this->assertFalse(Validator::areAttributesValid('Invalid string attributes.'));

        // Integer as attributes
        $this->assertFalse(Validator::areAttributesValid(42));

        // Boolean as attributes
        $this->assertFalse(Validator::areAttributesValid(true));

        // Sequential array as attributes
        $this->assertFalse(Validator::areAttributesValid([0, 1, 2, 42]));

        // Mixed array as attributes
        $this->assertFalse(Validator::areAttributesValid([0, 1, 2, 42, 'abc' => 'def']));
    }

    public function testIsUserInExperimentNoAudienceUsedInExperiment()
    {
        $config = new ProjectConfig(DATAFILE, new NoOpLogger(), new NoOpErrorHandler());
        $this->assertTrue(Validator::isUserInExperiment(
            $config,
            $config->getExperimentFromKey('paused_experiment'),
            []
        ));
    }

    public function testIsUserInExperimentAudienceUsedInExperimentNoAttributesProvided()
    {
        $config = new ProjectConfig(DATAFILE, new NoOpLogger(), new NoOpErrorHandler());

        // Test with empty attributes
        $this->assertFalse(Validator::isUserInExperiment(
            $config,
            $config->getExperimentFromKey('test_experiment'),
            []
        ));

        // Test with null attributes
        $this->assertFalse(Validator::isUserInExperiment(
            $config,
            $config->getExperimentFromKey('test_experiment'),
            null
        ));
    }

    public function testIsUserInExperimentAudienceMatch()
    {
        $config = new ProjectConfig(DATAFILE, new NoOpLogger(), new NoOpErrorHandler());
        $this->assertTrue(Validator::isUserInExperiment(
            $config,
            $config->getExperimentFromKey('test_experiment'),
            ['device_type' => 'iPhone', 'location' => 'San Francisco']
        ));
    }

    public function testIsUserInExperimentAudienceNoMatch()
    {
        $config = new ProjectConfig(DATAFILE, new NoOpLogger(), new NoOpErrorHandler());
        $this->assertFalse(Validator::isUserInExperiment(
            $config,
            $config->getExperimentFromKey('test_experiment'),
            ['device_type' => 'Android', 'location' => 'San Francisco']
        ));
    }
}
