<?php

/**
 * This function turns the incident data into a PDF file
 * @param $api
 * @param $incident
 */

function exportIncidentToPDF($api, $incident) {
    $operatorGroupPath = '../output' . DIRECTORY_SEPARATOR . $incident->getOperatorGroup();
    @mkdir($operatorGroupPath);

    $incPath = $operatorGroupPath . DIRECTORY_SEPARATOR . $incident->getNumber();
    @mkdir($incPath);

    $communication =
        "<h1>{$incident->getNumber()} - {$incident->getBriefDescription()}</h1>" .
        '<table>'.
            //"<tr><td colspan='4'>{$incident->getNumber()} - {$incident->getBriefDescription()}</td></tr>".
            "<tr>
                <td>Ticket created: </td>
                <td>{$incident->getTimestamp()}</td>
                <td>Operator Group:</td>
                <td>{$incident->getOperatorGroup()}</td>
            </tr>".
            "<tr>
                <td>Created by: </td>
                <td>{$incident->getCaller()}</td>
                <td>Operator:</td>
                <td>{$incident->getOperator()}</td>
            </tr>".
        '</table><br /><hr /><br />';


    foreach($incident->getResponses() as $response) {
        if($response instanceof Email) {
            $attachmentPath = $incPath . DIRECTORY_SEPARATOR . "emails";
            @mkdir($attachmentPath);
            file_put_contents($attachmentPath . DIRECTORY_SEPARATOR . $response->getName(), $api->makeCall(BASE_URL . $response->getUrl(), [], false));
        }
        elseif($response instanceof File) {
            $attachmentPath = $incPath . DIRECTORY_SEPARATOR . "files";
            @mkdir($attachmentPath);
            file_put_contents($attachmentPath . DIRECTORY_SEPARATOR . $response->getName(), $api->makeCall(BASE_URL . $response->getUrl(), [], false));
        }

        if(!empty($response->getRequest())) {
            $communication .= $response->getContent();
        }
    }

    // create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // set document information
    $pdf->SetCreator('github.com/xorinzor/TopdeskExport');
    $pdf->SetAuthor('github.com/xorinzor/TopdeskExport');
    $pdf->SetTitle($incident->getNumber() . ' - ' . $incident->getBriefDescription());
    $pdf->SetSubject($incident->getNumber() . ' - ' . $incident->getBriefDescription());
    $pdf->SetKeywords('Topdesk, ' . $incident->getNumber());

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

    // set margins
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetPrintHeader(false);
    $pdf->SetPrintFooter(false);

    // set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    // set image scale factor
    $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

    // set some language-dependent strings (optional)
    if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
        require_once(dirname(__FILE__).'/lang/eng.php');
        $pdf->setLanguageArray($l);
    }

    // ---------------------------------------------------------

    // set font
    $pdf->SetFont('dejavusans', '', 10);

    // add a page
    $pdf->AddPage();

    // output the HTML content
    $pdf->writeHTML($communication, true, false, true, false, '');

    $pdf->lastPage();

    //Close and output PDF document
    $pdf->Output(realpath($incPath).DIRECTORY_SEPARATOR."ticket.pdf", 'F');
}