<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */
namespace Advancedideasmechanics\Antivirus\Adapter;

class ClamavScan implements ClamavScanInterface
{

    /*
     * Connecting to clamav requires zINSTREAM '<length><data>'
     * 4 byte unsigned integer network byte order
     * Possible use of zIDSESSION to build a Queue system for larger files and higher traffic servers.
     */

    public function __construct($options = null)
    {

    }

    public function scan($fileHandle, $fileSize, $options)
    {

        $response = null;

        switch ($options['clamavScanMode']) {
            case 'cli':
                exec($options['clamavCliScanner'] . ' ' . escapeshellarg($fileHandle), $execResponse);
                $response['message'] = trim(substr(strstr($execResponse[0], ':'), 1));
                break;
            default:
                $zInstream = "zINSTREAM\0";

                $socket = new ClamavSocket();
                $openSocket = $socket->openSocket($options);
                /*
                     * Check if clamav is available if not return message
                     */

                $checkSocket = $socket->checkSocket($options);
                if ($checkSocket['message'] != "ClamAV is Alive!") {
                    return $checkSocket;
                }

                $sendResponse['instream'] = $socket->send($openSocket, $zInstream);

                $chunkDataSent = 0;
                $chunkDataLength = $fileSize;

                while ($chunkDataSent < $chunkDataLength) {
                    fseek($fileHandle, $chunkDataSent);
                    $chunk = fread($fileHandle, $options['clamavChunkSize']);
                    $chunkLength = pack("N", strlen($chunk));
                    /*
                     * Check if clamav is available if not return message
                     */
                    if ($checkSocket['message'] != "ClamAV is Alive!") {
                        return $checkSocket;
                    }
                    $chunkLengthResponse = $socket->send($openSocket, $chunkLength);

                    $chunkDataResponse = $socket->send($openSocket, $chunk);
                    $chunkDataSent += $chunkDataResponse['written'];

                }
                /*
                     * Currently do not need to send zero string to Clamav with this code.
                     * Leaving it here for the time being for update to how a file is sent to clamvav host socket.
                     */
                $endInstream = pack("N", strlen("")) . "";
                /*
                 * Check if clamav is available if not return message
                */
                $checkSocket = $socket->checkSocket($options);
                if ($checkSocket['message'] != "ClamAV is Alive!") {
                    return $checkSocket;
                }
                $response = $socket->send($openSocket, $endInstream, 1);
                $socket->closeSocket($openSocket);
                return $response;

        }
    }
}