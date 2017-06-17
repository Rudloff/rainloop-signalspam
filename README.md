# Signal Spam plugin for RainLoop

This [RainLoop](https://www.rainloop.net/) plugin forwards messages flagged as spam to the [Signal Spam](https://www.signal-spam.fr/) API.

## Install

The code needs to be placed in `data/_data_/_default_/plugins/signalspam/` (in RainLoop's directory).
If the plugin's directory is not named `signalspam`, it will not load correcly.

You also need to run [Composer](https://getcomposer.org/) in the `signalspam` directory:

```bash
composer install --no-dev
```
