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

//Initialize constants
const BASE_URL = "https://centrumveiligwonen.topdesk.net";
const API_URL = BASE_URL . "/tas/api/";

const TOPDESK_USER = "jorin.vermeulen@cvw.nl";
const TOPDESK_PASS = "p3plq-xrwyj-6irnt-eag6w-675at";

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

$result = apiCall($api, $method, $data);

ob_end_clean();

echo $result;