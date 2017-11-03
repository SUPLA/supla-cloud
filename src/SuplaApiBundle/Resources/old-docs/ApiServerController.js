/**
 * @api {get} /server-info Server Info
 * @apiDescription Get server info.
 * @apiGroup Server
 * @apiVersion 2.0.0
 * @apiUse Error401
 * @apiSuccess {object} data
 * @apiSuccess {string} data.address URL address of the server
 * @apiSuccess {string} data.address URL address of the server
 * @apiSuccess {time} data.time Current server time
 * @apiSuccess {object} data.timezone Current server timezone
 * @apiSuccess {string} data.timezone.name Current server timezone name
 * @apiSuccess {int} data.timezone.offset Current server timezone offset in minutes
 * @apiSuccessExample Success
 * {"address":"supla.org","time":"2017-11-03T10:47:29+01:00","timezone":{"name":"Europe/Berlin","offset":3600}}
 */
