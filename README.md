# TopdeskExport
An export script for a topdesk installation (SAAS installation is confirmed to work, self-hosted is untested but should work too as long as it's up-to-date).

This will produce a file structure that looks like the following:

```
/output
└── /Operator Group (every ticket is grouped by it's operator group)
    └── /Ticket Name
        └── /emails (contains all emails that have been sent)
        └── /files (contains all attachments)
        └── ticket.pdf (contains the ticket details in a readable format)
```

## Requirements
- apache or nginx
- PHP 7.3+ server

> Running windows? A simple Xampp installation will do just fine.

## Installation
1. Rename config.example.ini to config.ini
2. [Generate an app-password in topdesk](https://developers.topdesk.com/tutorial.html#show-collapse-usage-createAppPassword)
3. edit the config.ini file and set the correct values
4. Hit the button, sit back, and let the script do it's magic.

> Note: if you have lots of tickets (8000+) you might run into request timeouts. Edit your php.ini to increase this limit (or make it unlimited)

> Note: remember that the exporter has the same access as the account you've used to generate the app-password.
If tickets or responses are hidden from this account, they will not (and cannot) be included in the export.

> Note: Remember to run the script from an IP-address that has access to your Topdesk environment if you have whitelisting enabled. Otherwise the script will be unable to communicate with your Topdesk installation.

## Usage
- Hit the `Start Export` button and watch the magic happen. The export is saved in `/output`.
- The exporter keeps track of its progress and will resume where it left off if the page is closed and reopened. This data is stored in the `current_progress.json`
- The `/cache` directory will store data returned by the Topdesk API. This way, less calls to the API have to be made. This data however, is not checked for age.
- To start a fresh export, make sure to empty the `/cache` directory and to remove the `current_progress.json` file
## Useful?

Did you find this tool useful? Feel free to buy me a beer or a pizza!
- [BuyMeACoffee](https://www.buymeacoffee.com/xorinzor)
- [Paypal donation](https://paypal.me/xorinzor)

## Suggestions?
Got ideas or feedback? [Create an issue](https://github.com/xorinzor/TopdeskExport/issues/new) and let me know!

## Todo
- Prevent request timeouts if lots of data is being fetched
- Improve the layout of the generated PDF
- Add more fields from the tickets to the PDF (such as connected Assets)
- Add excel,csv, or json files containing parseable data, both a complete list, as well as per-ticket.

## Changelog
23-12-2019 14:00 - Implemented handling of HTTP 206, unlimited tickets can now be parsed instead of a max of 10.000

19-12-2019 15:15 - Improved PDF generation, translated some text

19-12-2019 14:30 - Organized files a bit more

19-12-2019 14:00 - Version 1.0 
