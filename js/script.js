$(function() {
    var totalTicketCount = 0;
    var currentTicket = 0;
    var ticketList = [];
    var running = false;

    $("#exportButton").on('click', function() {
        if(running != true) {
            enableProgress();
        }

        return false;
    });

    function enableProgress() {
        running = true;

        $("#exportButton").addClass("is-loading").attr('disabled', true);

        $("#statusDivider").removeClass("is-hidden");
        $("#statusContainer").removeClass('is-hidden');

        getTicketCount();
    }

    function disableProgress() {
        running = false;

        $("#exportButton").remove();
    }

    function getTicketCount() {
        apiCall({
            method: "getIncidentList"
        }, function(result) {
            if(result === false) {
                //The request has failed or an invalid response has been returned.
            } else {
                $("#statusText").append("<br />Found " + result.result.count + " tickets<br />Checking if previous progress exists..");
                $("#statusProgress").val(1).attr("max", result.result.count);

                totalTicketCount = result.result.count;
                ticketList = result.result.data;

                checkPreviousProgress();
            }
        });
    }

    function checkPreviousProgress() {
        apiCall({
            method: "getPreviousProgress"
        }, function(result) {
            if(result === false) {
                //The request has failed or an invalid response has been returned.
            } else {
                if(result.result.previousProgressAvailable === false) {
                    $("<br />No previous progress exists.").appendTo("#statusText");
                    //No previous progress exists
                    startExport(0);
                } else {
                    var tmpLastTicketNumber = parseInt(result.result.lastTicketNumber);
                    var tmpLastTicketName = result.result.lastTicket;

                    //Doublecheck that the offset is still valid, and the array hasn't shifted, possibly
                    //leading to missing tickets in the export.
                    if(ticketList.length < tmpLastTicketNumber || ticketList[tmpLastTicketNumber] === tmpLastTicketName) {
                        //Offset matches, resume export.

                        $("#statusText").append("<br />Previous progress found and validated, resuming export.");
                        startExport(tmpLastTicketNumber);
                    } else {
                        //Offset doesn't match, previous progress is invalidated. Start over.
                        $("#statusText").append("<br />Previous progress found, but offset doesn't match. Invalidated. Starting over.");
                        startExport(0);
                    }
                }
            }
        });
    }

    function startExport(startAt) {
        $("#statusText").append("<br />Exporting ticket <span id='currentTicket'>" + startAt + "</span> of " + ticketList.length + "..");
        exportTicket(startAt);
    }

    function exportTicket(cnt) {
        currentTicket = cnt;
        $("#currentTicket").text(cnt + 1);
        $("#statusProgress").val(cnt + 1);

        apiCall({
            method: "exportTicket",
            ticketId: ticketList[currentTicket],
            ticketNo: currentTicket
        }, function(result) {
            if(result === false) {
                //The request has failed or an invalid response has been returned.
            } else {
                if(cnt+1 >= totalTicketCount) {
                    //Finished exporting
                    $("#statusText").append("<br />Export finished, check the /output directory of this app");
                    $("#statusProgress").val(1).attr("max", 1);
                    disableProgress();
                } else {
                    //Continue with next ticket
                    exportTicket(cnt + 1);
                }
            }
        });
    }

    function apiCall(data, callback) {
        $.getJSON("/webApi.php", data, function(data) {
            if(data.error == true) {
                callback(false);
                console.log("An error occured according to the returned JSON, data:", data);
            } else {
                callback(data);
            }
        })
            .fail(function() {
                callback(false)
                console.log("An error occured while performing the API call");
            });
    }
});