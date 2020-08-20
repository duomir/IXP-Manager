<?php

use Tests\TestCase;

/*
 * Copyright (C) 2009 - 2019 Internet Neutral Exchange Association Company Limited By Guarantee.
 * All Rights Reserved.
 *
 * This file is part of IXP Manager.
 *
 * IXP Manager is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, version v2.0 of the License.
 *
 * IXP Manager is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License v2.0
 * along with IXP Manager.  If not, see:
 *
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

use Entities\Router as RouterEntity;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use IXP\Tasks\Router\ConfigurationGenerator as RouterConfigurationGenerator;

use Grapher as GrapherService;

/**
 * PHPUnit test class to test the configuration generation of router configurations
 * against known good configurations for IXP\Tasks\Router\ConfigurationGenerator
 *
 * @author     Barry O'Donovan <barry@islandbridgenetworks.ie>
 * @category   Tests
 * @copyright  Copyright (C) 2009 - 2019 Internet Neutral Exchange Association Company Limited By Guarantee
 * @license    http://www.gnu.org/licenses/gpl-2.0.html GNU GPL V2.0
 */
class MrtgTest extends TestCase
{

    public function testMrtgConfigurationGeneration()
    {
        $grapher = GrapherService::backend( 'mrtg' );

        $config = $grapher->generateConfiguration()[0];

        $knownGoodConf = file_get_contents( base_path() . "/data/travis-ci/known-good/ci-services-grapher-mrtg.conf" );
        $this->assertFalse( $knownGoodConf === false, "Mrtg Conf generation - could not load known good file ci-services-grapher-mrtg.conf" );

        // clean the configs to remove the comment lines which are irrelevant
        $config        = preg_replace( "/^(#|\s+Based on configuration last generated by).*$/m", "", $config        );
        $knownGoodConf = preg_replace( "/^(#|\s+Based on configuration last generated by).*$/m", "", $knownGoodConf );

        $this->assertEquals( $knownGoodConf, $config, "Known good and generated MRTG configuration do not match" );
    }
}
