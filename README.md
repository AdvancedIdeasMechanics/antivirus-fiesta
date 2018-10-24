# Antivirus-fiesta #
This a fork from my previous php-clamav library. This is the new home for it.
PHP library to check files against ClamAV. You can pass a file via UNIX and TCP sockets. If ClamAV is installed locally you can also use clamscan/clamdscan.

## Install ##
You should have ClamAV installed locally or on a secured remote server.

### Composer ###

`composer install advancedideasmechanics/antivirus-fiesta`

#### Stand-Alone ####

`require('./vendor/autoload.php');`

`$options['clamavScanMOde] = 'cli';`

If ClamAV is installed in another location.

`$options['clamavCliScanner] = '/usr/local/bin/clamscan;`

#### Zend Framework 3 ####

Instructions coming soon.

#### Call package #### 

`$clamav = new Advancedideasmechanics\Antivirus\ClamavService($options);`

`$response = $clamav->sendToScanner($file);`

To see the response from the server you can send to you internal logger or just dump from your test page.

`var_dump($response);`

### Information ###

In `$response['message']` will include the response from ClamAV.

The message if successful will be either "OK" or "VIRUSNAME FOUND"

Options that are available to update are:

`$options['clamavScanMode'] = 'local';` Can be 'cli', 'local', 'server'

`$options['clamavMaxFileSize'] = 25000000;` This should match your clamd.conf filesize limit.

`$options['clamavServerHost'] = '127.0.0.1';`

`$options['clamavServerPort'] = 3310;`

`$options['clamavServerTimeout'] = 30;`

`$options['clamavServerSocketMode'] = TRUE;`

`$options['clamavLocalSocket'] = '/var/run/clamav/clamd.ctl';`

`$options['clamavCliScanner] = '/usr/bin/clamscan';` Can be clamscan or clamdscan

`$options['clamavChunkSize] = 2048;` This is used for sockets and not used for the Command Line scanner.
