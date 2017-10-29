<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaWebApiBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Request;

/**
 * @api {ENTITY} IODevice IODevice
 * @apiGroup Entities
 * @apiVersion 2.1.3
 * @apiParam {Number} id IO Device ID
 * @apiParam {String} guid IO Device GUID
 * @apiParam {String} name IO Device name
 * @apiParam {[Location](#api-Entities-EntityLocation)} location IO Device Location
 * @apiParam {Boolean} enabled Whether the IO Device is enabled or not.
 * @apiParam {String} comment Custom IO Device comment (user entered).
 * @apiParam {String} softwareVersion The version of a firmware installed in the IO device.
 * @apiParamExample {json} Example IO Device
 * {"id": 123, "guid": "22ca8686bfa31a2ae5f55a7f60009e14", "location":{"id": 123, "caption": "My Location", "enabled": true},
 * "enabled": true, "comment": "My IO Device", "softwareVersion": "2.5.3"}
 */

/**
 * @api {ENTITY} Location Location
 * @apiGroup Entities
 * @apiVersion 2.1.3
 * @apiParam {Number} id Location ID
 * @apiParam {String} caption Custom location caption (user entered).
 * @apiParam {Boolean} enabled Whether the location is enabled or not.
 * @apiParamExample {json} Example Location
 * {"id": 123, "caption": "My Location", "enabled": true}
 */

/**
 * @Rest\Version({"2.1", "2.2"})
 */
class ApiIODeviceController extends FOSRestController {
    /**
     * @api {get} /iodevices List
     * @apiDescription Get list of devices without their state.
     * @apiGroup IODevices
     * @apiVersion 2.0.0
     * @apiSuccess {[IODevice[]](#api-Entities-EntityIodevice)} iodevices List of IO devices
     */
    /**
     * @api {get} /iodevices List
     * @apiDescription Get list of devices without their state.
     * @apiGroup IODevices
     * @apiVersion 2.1.3
     * @apiSuccess {[IODevice[]](#api-Entities-EntityIodevice)} - List of IO devices
     */
    public function getIodevicesAction(Request $request) {
        $user = $this->getUser();
        return $this->view($user->getIODevices(), 200);
    }
}
