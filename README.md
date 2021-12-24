# PHPAxieSchool

PHPAxieSchool is a php script that help managers and scholars in their Axie Infinity journey

*The script and its developer are NOT affiliated with Axie Infinity*

## Features

At the end of each day, the script gather data on Axie and Coingecko API servers, then build and send an email report to the manager and his scholars.
Each report contain :

- the MMR 
- the SLP earned for the day (the SLP amount should be right even if a claimed occurred)
- Average SLP and MMR
- SLP for today before/after the share
- In-game SLP before/after the share
- Expected for 30 days SLP before/after the share
- The SLP price 

You can define different currencies for the manager and for each of the scholars. (usd, php ...)

## Report screenshots

### _Manager report example_

![Capture du 2021-04-01 13-13-49](https://user-images.githubusercontent.com/test.png)

### _Scholar report example_

![Capture du 2021-04-01 13-12-10](https://user-images.githubusercontent.com/test.png)


## Installation

- edit config.php
- send the file to your server
- make sure /data/ is recursively writable 
- (optionnal) put .htaccess and .htpasswd
- set a cron to run at 0:00 UTC precisely every day (you can use this https://console.cron-job.org/)


## Give a try

run index.php from the browser, this will fetch the latest data on prices and scholars

> Note: First day will not show the SLP !!! (it needs 2 days to be calculated)


## Is it safe to use ?

So yes, it is safe. According to Axie Terms Of Use at time of writing (https://axieinfinity.com/terms/), article 2.5 :

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

If you use this free program a donation is greatly appreciated, thanks
