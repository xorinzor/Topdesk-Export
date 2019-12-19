<?php
ob_start();

//Classes
include("api.php");
include("Incident.php");
include("Response.php");
include("File.php");
include("Email.php");

//Libraries
include("lib/tcpdf.php");

//Utility functions/classes
include("util.php");
include("export.php");

//Read config file
$config = parse_ini_file("config.ini");

//Initialize constants
define('TOPDESK_USER', $config['user']);
define('TOPDESK_PASS', $config['pass']);
define('BASE_URL', "https://" . $config['topdesk_domain']);
const API_URL = BASE_URL . "/tas/api/";
const RESUME_FILE = "current_progress.json";

//Start parsing.
$api = new api(API_URL, TOPDESK_USER, TOPDESK_PASS);

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
            $result = $api->getIncidentIds(9999);

            return returnJson(false, "", [
                'count' => count($result),
                'data'  => $result
            ]);
            break;

        case "exportTicket":
            $inc = $api->getIncident($data['ticketId']);
            exportData($api, $inc);

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

if(empty($_GET['ticketId']) == false) {
    $data['ticketId'] = preg_replace('/[^ \w]+/', '', $_GET['ticketId']);
}

if(empty($_GET['ticketNo']) == false) {
    $data['ticketNo'] = preg_replace('/\D/', '', $_GET['ticketNo']);
}

$result = apiCall($api, $method, $data);

ob_end_clean();

echo $result;