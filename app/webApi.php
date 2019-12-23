<?php

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);

//Classes
include("topdeskAPI.php");
include("models/Incident.php");
include("models/Response.php");
include("models/File.php");
include("models/Email.php");

//Libraries
include("lib/tcpdf.php");

//Utility functions/classes
include("util.php");
include("export.php");

//Read config file
$config = parse_ini_file("../config.ini");

//Initialize constants
define('TOPDESK_USER', $config['user']);
define('TOPDESK_PASS', $config['pass']);
define('BASE_URL', "https://" . $config['topdesk_domain']);
const API_URL = BASE_URL . "/tas/api/";

const RESUME_FILE = "../current_progress.json";

//Start parsing.
$api = new topdeskAPI(API_URL, TOPDESK_USER, TOPDESK_PASS);

function returnJson(bool $error, string $message, $result = array()) {
    return json_encode([
        "error"     => $error,
        "message"   => $message,
        "result"    => $result
    ]);
}

function apiCall($api, $method, $data)
{
    switch ($method) {
        case "getPreviousProgress":
            if(file_exists(RESUME_FILE) == false) {
                return returnJson(false, "no previous progress has been saved yet", [
                    'previousProgressAvailable' => false
                ]);
            }

            $filedata = file_get_contents(RESUME_FILE);

            if(empty($filedata)) {
                return returnJson(false, "no previous progress has been saved yet", [
                    'previousProgressAvailable' => false
                ]);
            }

            return returnJson(false, "", json_decode($filedata));
            break;

        case "getIncidentList":
            $result = $api->getIncidentIds(1000);

            return returnJson(false, "", [
                'count' => count($result),
                'data'  => $result
            ]);
            break;

        case "exportTicket":
            $inc = $api->getIncident($data['ticketId']);

            exportIncidentToPDF($api, $inc);

            $progress = [
                'previousProgressAvailable' => true,
                'lastTicket'        => $data['ticketId'],
                'lastTicketNumber'  => $data['ticketNo']
            ];

            file_put_contents(RESUME_FILE, json_encode($progress));

            return returnJson(false, "Export succeeded", []);
            break;

        default:
            return returnJson(true, "Invalid method requested");
    }
}

$method = $_GET['method'];
$data = [];

//Sanitize the GET variables used by the API
$data['ticketId'] = (empty($_GET['ticketId']) == false) ? preg_replace('/[^ \w]+/', '', $_GET['ticketId']) : '';
$data['ticketNo'] = (empty($_GET['ticketNo']) == false) ? preg_replace('/\D/', '', $_GET['ticketNo']) : 0;

$result = apiCall($api, $method, $data);

//Return the JSON data
echo $result;