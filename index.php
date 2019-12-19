<!DOCTYPE html>
    <head>
        <title>TopDesk exporter</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link type="text/css" href="/static/css/bulma.min.css" rel="stylesheet" />

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
                    <p>Make sure you have read the README.md file</p>
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

        <script type="text/javascript" src="/static/js/jquery.js"></script>
        <script type="text/javascript" src="/static/js/script.js"></script>
    </body>
</html>