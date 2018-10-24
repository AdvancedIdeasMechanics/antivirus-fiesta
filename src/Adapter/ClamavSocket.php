<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */
namespace Advancedideasmechanics\Antivirus\Adapter;

class ClamavSocket implements ClamavSocketInterface{

    public function __construct($options = null) {

    }

    public function openSocket($options) {
        /*
         * Socket should be opened as non-blocking
         * stream_socket_client()
         * stream_set_blocking($stream, FALSE)
         */

        $socket = null;
        $message = null;
        $errorno = null;
        $errorstr = null;

        if($options['clamavScanMode'] != 'cli') {

            $clamavServer = $options['clamavServerHost'];
            $clamavServerPort = $options['clamavServerPort'];

            switch($options['clamavScanMode']) {
                case 'server':
                    $socket = stream_socket_client("tcp://$clamavServer:$clamavServerPort", $errorno, $errorstr, $options['clamavServerTimeout']);
                    break;
                default:
                    $socket = stream_socket_client("unix://".$options['clamavLocalSocket'], $errorno, $errorstr, $options['clamavServerTimeout']);
            }

            if(!$socket) {
                $message = "$errorstr ($errorno)";
                return ['message' => $message];
            }
                if ($options['clamavServerSocketMode'] === false && $options['clamavScanMode'] == 'server') {
                    stream_set_blocking($socket, FALSE);
                }
            return $socket;
        }
    }

    public function closeSocket($socket) {
            fclose($socket);
    }

    public function checkSocket($options)
    {
        $options['clamavServerSocketMode'] = TRUE;
        $socket = $this->openSocket($options);

        $pingResponse = null;

        if ($options['clamavServerSocketMode'] === false && $options['clamavScanMode'] == 'server') {
            /*
             * Turn off blocking till the PING happens. Not sure this a great option.
             * May probably need to open a new Socket to test PING.
             * Currently this may send screw a scan. Hopefully because IDSESSION or INSTREAM is sent
             * It will ignore.
             */
            stream_set_blocking($socket, TRUE);
            fwrite($socket, "PING", 4);
            $pingResponse = fread($socket,4);
            stream_set_blocking($socket, FALSE);

        } else {
            fwrite($socket, "PING", 4);
            $pingResponse = fread($socket,4);
        }

        $this->closeSocket($socket);

        if ($pingResponse == "PONG") {
            return ['message' => 'ClamAV is Alive!'];
        } else {
            return ['message' => 'ClamAV is NOT Alive!'];
        }

    }

    public function send($socket, $chunk, $end = 0) {

        $response = [];
        $sentData = 0;
        $cmdLength = strlen($chunk);

        /*
         * If a fwrite does not write the full length because socket gets another packet
         * Track the amount written and continue to try and write the rest.
         * May need to include this with stream_select if statement. or move stream_select into while loop.
         */
        while ($sentData< $cmdLength) {
            $fwrite = fwrite($socket, substr($chunk, $sentData));
            if($end == 1) {

                $response['message'] = trim(substr(strstr(stream_get_contents($socket, 255), ':'), 1));
            }
            $sentData += $fwrite;
            $response['written'] = $sentData;

        }

        return $response;

    }
}