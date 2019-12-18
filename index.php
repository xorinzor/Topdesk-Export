<!DOCTYPE html>
    <head>
        <title>TopDesk export</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link type="text/css" href="/css/bulma.min.css" rel="stylesheet" />

        <style type="text/css">
            .divider {
                height: 40px;
            }
        </style>
    </head>
    <body class="has-background-info">
        <section class="hero is-info is-large has-text-centered">
            <div class="hero-body">
                <div class="container">
                    <h1 class="title">
                        TopDesk Exporter
                    </h1>
                    <h2 class="subtitle">
                        Just hit the button and sit back.
                    </h2>
                </div>

                <div class="divider">&nbsp;</div>

                <div class="container">
                    <button id="exportButton" class="button is-primary">Start export</button>
                </div>

                <div id="statusDivider" class="is-hidden divider">&nbsp;</div>

                <div class="is-hidden container" id="statusContainer">
                    <p id="statusText">Fetching ticket list, this may take a while..</p>
                    <progress id="statusProgress" class="progress is-small is-primary" max="100">15%</progress>
                </div>

                <div class="divider">&nbsp;</div>

                <div class="container">
                    <p>Deze app maakt gebruik van caching voor de data die opgehaald wordt.</p>
                    <p>Verwijder de data in de /cache map om de meest recente data op te halen van Topdesk.</p>
                </div>
            </div>
        </section>
        <footer class="footer has-background-info">
            <div class="content has-text-centered">
                <p>
                    <strong>Topdesk exporter</strong> by <a href="https://jorinvermeulen.com">Jorin Vermeulen</a>
                </p>
            </div>
        </footer>

        <script type="text/javascript" src="/js/jquery.js"></script>
        <script type="text/javascript">
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
                            $("#statusText").html("Found " + result.result.count + " tickets<br />Exporting ticket <span id='currentTicket'>1</span> of " + result.result.count + "..");
                            $("#statusProgress").val(1).attr("max", result.result.count);

                            totalTicketCount = result.result.count;
                            ticketList = result.result.data;

                            exportTicket(0);
                        }
                    });
                }

                function exportTicket(cnt) {
                    currentTicket = cnt;
                    $("#currentTicket").text(cnt + 1);
                    $("#statusProgress").val(cnt + 1);

                    apiCall({
                        method: "exportTicket",
                        ticketId: ticketList[currentTicket]
                    }, function(result) {
                        if(result === false) {
                            //The request has failed or an invalid response has been returned.
                        } else {
                            if(cnt+1 >= totalTicketCount) {
                                //Finished exporting
                                $("#statusText").text("Export finished, check the /output directory of this app");
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
        </script>
    </body>
</html>