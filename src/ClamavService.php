<?php
/**
 * Created by PhpStorm.
 * User: Matthew Pallotta
 * Date: 8/8/18
 * Time: 9:00 AM
 */
namespace Advancedideasmechanics\Antivirus;

use Advancedideasmechanics\Antivirus\Adapter\ClamavSocket as ClamavSocket;
use Advancedideasmechanics\Antivirus\Adapter\ClamavScan as ClamavScan;

class ClamavService implements ClamavServiceInterface {

    /*
     * $this->option['clamavScanMode'] = 'local' || 'server' || 'cli'
     * local is the default behaviour
     * This tells the socket to use ether the server settings or
     * just connect to local daemon running via socket pid and not a port.
     */
    public $option = [
        'clamavScanMode' => 'local',
        'clamavMaxFileSize' => 25000000,
        'clamavServerHost' => 'localhost',
        'clamavServerPort' => 3310,
        'clamavServerTimeout' => 30,
        'clamavServerSocketMode' => TRUE,
        'clamavLocalSocket' => '/var/run/clamav/clamd.ctl',
        'clamavCliScanner' => '/usr/bin/clamscan',
        'clamavChunkSize' => 2048,
    ];

    public function __construct($options = null) {

        if(!extension_loaded('sockets')) {
            return ['message' => "Sockets not enabled"];
        }
            if(is_array($options)) {
                if(isset($options['clamavScanMode'])){
                    $this->option['clamavScanMode'] = $options['clamavScanMode'];
                }

                if(isset($options['clamavMaxFileSize'])){
                    $this->option['clamavMaxFileSize'] = $options['clamavMaxFileSize'];
                }

                if(isset($options['clamavServerHost'])){
                    $this->option['clamavServerHost'] = $options['clamavServerHost'];
                }

                if(isset($options['clamavServerPort'])){
                    $this->option['clamavServerPort'] = $options['clamavServerPort'];
                }

                if(isset($options['clamavServerTimeout'])){
                    $this->option['clamavServerTimeout'] = $options['clamavServerTimeout'];
                }

                if(isset($options['clamavServerSocketMode'])){
                    $this->option['clamavServerSocketMode'] = $options['clamavServerSocketMode'];
                }

                if(isset($options['clamavLocalSocket'])){
                    $this->option['clamavLocalSocket'] = $options['clamavLocalSocket'];
                }

                if(isset($options['clamavCliScanner'])) {
                    $this->option['clamavCliScanner'] = $options['clamavCliScanner'];
                }

                if(isset($options['clamavChuckSize'])){
                    $this->option['clamavChunkSize'] = $options['clamavChunkSize'];
                }
        }

    }

    public function sendToScanner($file)
    {
        $response = null;
        $openedFile = null;
        $checkClamav = $this->checkClamav();

        if($checkClamav['message'] == "ClamAV is Alive!") {
            $openedFile = fopen($file, "rb");
            /*
             * Check is file exists or opens
             */
            if(!$openedFile) {
                return ['message' => 'File not found or unable to open'];
            }

            $openedFilesize = filesize($file);

            if($openedFilesize <= $this->option['clamavMaxFileSize']) {
                $clamavScan = new ClamavScan();
                switch($this->option['clamavScanMode']) {
                    case 'cli':
                        $response = $clamavScan->scan($file, $openedFilesize, $this->option);
                        break;
                    default:
                        $response = $clamavScan->scan($openedFile, $openedFilesize, $this->option);
                }

            } else {
                $response =  ['message' => 'File is to large for clamav\'s ' . $this->options['clamavMaxFilesize'] . '. This file is: ' . $openedFilesize];
            }
            fclose($openedFile);
            return $response;


        } else {
            return ['message' => 'ClamAV is not available.'];
        }
    }

    public function checkClamav() {
        $response = null;
        /*
         * Send Ping to ClamAV Service
         * Want a better way to handle this
         */
        switch($this->option['clamavScanMode']){
            case "cli":
                if(is_file($this->option['clamavCliScanner'])) {
                    $response['message'] = "ClamAV is Alive!";
                } else {
                    $response['message'] = "ClamAV is not available or not found";
                }
                break;
            default:
                $socket = new ClamavSocket();
                $response = $socket->checkSocket($this->option);
        }
        return $response;
    }

    public function hello() {
        return ["message" => "hello"];
    }

}