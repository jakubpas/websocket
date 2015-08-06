<?php
namespace WebSocket;

/**
 * Class Server
 * @author Jakub Pas
 * @package WebSocket
 */

class Server
{
    private $host = '';
    private $port = 0;

    public function __construct($host,$port)
    {
        $this->host = $host;
        $this->port = $port;
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        socket_set_option($sock, SOL_SOCKET, SO_REUSEADDR, 1);
        socket_bind($sock, 0, $this->port);
        socket_listen($sock);
        $clients = array($sock);
        while (true) {
            $read = $clients;
            if (socket_select($read, $write = null, $except = null, 0) < 1) {
                continue;
            }
            if (in_array($sock, $read)) {
                $clients[] = $newSocket = socket_accept($sock);
                $this->handShake($newSocket);
                socket_getpeername($newSocket, $ip);

                $this->send(array('type' => 'system', 'message' => $ip . ' Connected'),$clients);

                echo "New client connected: {$ip}\n";
                $key = array_search($sock, $read);
                unset($read[$key]);
            }
            foreach ($read as $read_sock) {
                while (socket_recv($read_sock, $buf, 1024, 0) >= 1) {
                    $msg = json_decode($this->unmask($buf),true);

                    $this->send($msg,$clients);
                }
            }
        }
        socket_close($sock);
    }

    private function handShake($socket)
    {
        $header = socket_read($socket, 1024);
        $headers = array();
        $lines = preg_split("/\r\n/", $header);
        foreach ($lines as $line) {
            $line = chop($line);
            if (preg_match('/\A(\S+): (.*)\z/', $line, $matches)) {
                $headers[$matches[1]] = $matches[2];
            }
        }
        $secKey = (isset($headers['Sec-WebSocket-Key'])) ? $headers['Sec-WebSocket-Key'] : '';
        $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" . "Upgrade: websocket\r\n" . "Connection: Upgrade\r\n" . "WebSocket-Origin: $this->host\r\n" . "WebSocket-Location: ws:` $this->host:$this->port/shout.php\r\n" . "Sec-WebSocket-Accept:$secAccept\r\n\r\n";
        socket_write($socket, $upgrade, strlen($upgrade));
    }

    /**
     * Unmask incoming framed message
     * @param $text
     * @return string
     */
    public function unmask($text)
    {
        $length = ord($text[1]) & 127;
        if ($length == 126) {
            $masks = substr($text, 4, 4);
            $data = substr($text, 8);
        } elseif ($length == 127) {
            $masks = substr($text, 10, 4);
            $data = substr($text, 14);
        } else {
            $masks = substr($text, 2, 4);
            $data = substr($text, 6);
        }
        $text = '';
        for ($i = 0; $i < strlen($data); ++$i) {
            $text .= $data[$i] ^ $masks[$i % 4];
        }
        return $text;
    }

    /**
     * Encode message for transfer to client.
     * @param $text
     * @return string
     */
    public function mask($text)
    {
        $header = '';
        $b1 = 0x80 | (0x1 & 0x0f);
        $length = strlen($text);
        if ($length <= 125) {
            $header = pack('CC', $b1, $length);
        } elseif ($length > 125 && $length < 65536) {
            $header = pack('CCn', $b1, 126, $length);
        } elseif ($length >= 65536) {
            $header = pack('CCNN', $b1, 127, $length);
        }
        return $header . $text;
    }

    public function read($buf) {
        $received_text = $this->unmask($buf);
        return json_decode($received_text);
    }

    public function send($message, $clients)
    {
        $msg = $this->mask(json_encode($message));
        foreach ($clients as $socket) {
            @socket_write($socket, $msg, strlen($msg));
        }
        return true;
    }
}