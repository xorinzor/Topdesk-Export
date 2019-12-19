# TopdeskExport
An export script for a topdesk SAAS installation

## Requirements
- apache or nginx
- PHP 7.3+ server

> Running windows? A simple Xampp installation will do just fine.

## Installation
1. Rename config.example.ini to config.ini
2. [Generate an app-password in topdesk](https://developers.topdesk.com/tutorial.html#show-collapse-usage-createAppPassword)
3. edit the config.ini file and set the correct values
4. Hit the button, sit back, and let the script do it's magic.

> Note: remember that the exporter has the same access as the account you've used to generate the app-password.
If tickets or responses are hidden from this account, they will not (and cannot) be included in the export.

## Usage
- Hit the `Start Export` button and watch the magic happen.
- The exporter keeps track of its progress and will resume where it left off if the page is closed and reopened. This data is stored in the `current_progress.json`
- The `/cache` directory will store data returned by the Topdesk API. This way, less calls to the API have to be made. This data however, is not checked for age.
- To start a fresh export, make sure to empty the `/cache` directory and to remove the `current_progress.json` file

## Useful?

Did you find this tool useful? Feel free to buy me a beer or a pizza!
- [Paypal donation](https://www.paypal.me/xorinzor)
- [BuyMeACoffee](https://www.buymeacoffee.com/xorinzor)