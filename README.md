# PHPAxieSchool

PHPAxieSchool is a php script that help managers and scholars in their Axie Infinity journey

*The script and its developer are NOT affiliated with Axie Infinity*

## Features

At the end of each day, the script gather data on Axie and Coingecko API servers, then build and send an email report to the manager and his scholars.
Each report contain :

- The MMR 
- The SLP earned for the day (the SLP amount should be right even if a claimed occurred)
- Average SLP and MMR
- SLP for today before/after the share
- In-game SLP before/after the share
- Expected for 30 days SLP before/after the share
- The SLP price 

You can define different currencies for the manager and for each of the scholars. (usd, php ...)

## Report screenshots

### _Manager report example_

![Manager report](https://user-images.githubusercontent.com/96583462/147362700-1cb7c06e-0704-4276-a2f1-1e10ac8f0c51.png)

### _Scholar report example_

![Scholar report](https://user-images.githubusercontent.com/96583462/147362739-53fcd082-3751-43a7-af52-8783a90a051d.png)


## Installation

- Edit config.php
- Send the files and folders to your server
- Make sure /data/ and /logs/ are recursively writable by the script
- (optionnal) Put .htaccess and .htpasswd
- Set a cron to run at 0:00 UTC precisely every day (you can use this https://console.cron-job.org/)

## Give a try

Run index.php from the browser, this will fetch the latest data on prices and scholars (at first time run only)
It will show the HTML manager report

> Note: First day will not show the SLP !!! (it needs 2 days to be calculated)

Also feel free to check inside the logs folder from time to time, everything is logged.

## Is it safe to use ?

Yes, it is safe. According to Axie Terms Of Use (at time of writing) (https://axieinfinity.com/terms/), article 2.5 :

```sh
(5) you will not access the Site, the App and the Smart Contracts through automated and non-human means, whether through a bot, script or otherwise. Except as expressly mentioned herein;
Scholarship management
building public tools and bots that facilitate transparency and analysis.
building private, non-commercial tools which store data for analytical purposes. Note that anyone abusing public APIs by spamming requests will be banned from using such APIs in the future.
```


## Dependencies

PHPAxieSchool is using the following dependencies. Big thanks to them.

| Name | URL |
| ------ | ------ |
| PHPMailer | [https://github.com/PHPMailer/PHPMailer/] |
| sleekdb | [https://sleekdb.github.io/] |

Also thanks to https://github.com/leemunroe/responsive-html-email-template


## Support & donations

Feel free to drop an email for any question

If you use this free program a donation is greatly appreciated, even a few SLP would mean the world to me, thanks !

