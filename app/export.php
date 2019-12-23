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

    $pdf->startPageGroup();

    // add a page
    $pdf->AddPage();

    //Add the details about the ticket
    $details =
        "<h1>{$incident->getNumber()} - {$incident->getBriefDescription()}</h1>" .
        "<table>
            <tr><td>Date created: </td>     <td>{$incident->getTimestamp()}</td></tr>
            <tr><td>Created by: </td>       <td>{$incident->getCaller()}</td></tr>
            <tr><td colspan='2'>&nbsp;</td></tr>
            <tr><td>Brief Description:</td> <td>{$incident->getBriefDescription()}</td></tr>
            <tr><td>Type:</td>              <td>{$incident->getData()->entryType->name}</td></tr>
            <tr><td>Type:</td>              <td>{$incident->getData()->callType->name}</td></tr>
            <tr><td>Category:</td>          <td>{$incident->getData()->category->name}</td></tr>
            <tr><td>Subcategory:</td>       <td>{$incident->getData()->subcategory->name}</td></tr>
            <tr><td>External number:</td>   <td>{$incident->getData()->externalNumber}</td></tr>
            <tr><td colspan='2'>&nbsp;</td></tr>
            <tr><td>Priority:</td>          <td>{$incident->getData()->priority->name}</td></tr>
            <tr><td>Duration:</td>          <td>{$incident->getData()->duration->name}</td></tr>
            <tr><td>Target date:</td>       <td>" . (new DateTime($incident->getData()->targetDate))->format("Y-m-d H:i:s") . "</td></tr>
            <tr><td>Completed date:</td>    <td>" . (empty($incident->getData()->completedDate) ? "" : (new DateTime($incident->getData()->completedDate))->format("Y-m-d H:i:s")). "</td></tr>
            <tr><td colspan='2'>&nbsp;</td></tr>
            <tr><td>Operator Group:</td>    <td>{$incident->getOperatorGroup()}</td></tr>
            <tr><td>Operator:</td>          <td>{$incident->getOperator()}</td></tr>
            <tr><td>Supplier:</td>          <td>" . (is_null($incident->getData()->supplier) ? "" : $incident->getData()->supplier->name) . "</td></tr>
            <tr><td>Status:</td>            <td>{$incident->getData()->processingStatus->name}</td></tr>
        </table>";

    $pdf->writeHTML($details, true, false, true, false, '');

    //Create next group
    $pdf->startPageGroup();
    $pdf->AddPage();

    //Add the responses
    $communication = '';
    foreach($incident->getResponses() as $response) {
        if($response instanceof Email) {
            $attachmentPath = $incPath . DIRECTORY_SEPARATOR . "emails";
            @mkdir($attachmentPath);
            file_put_contents($attachmentPath . DIRECTORY_SEPARATOR . $response->getName(), $api->makeCall(BASE_URL . $response->getUrl(), [], [], false)['result']);
        }
        elseif($response instanceof File) {
            $attachmentPath = $incPath . DIRECTORY_SEPARATOR . "files";
            @mkdir($attachmentPath);
            file_put_contents($attachmentPath . DIRECTORY_SEPARATOR . $response->getName(), $api->makeCall(BASE_URL . $response->getUrl(), [], [], false)['result']);
        }

        if(!empty($response->getRequest())) {
            $communication .= $response->getContent();
        }
    }

    // output the HTML content
    $pdf->writeHTML($communication, true, false, true, false, '');

    $pdf->lastPage();

    //Close and output PDF document
    $pdf->Output(realpath($incPath).DIRECTORY_SEPARATOR."ticket.pdf", 'F');
}