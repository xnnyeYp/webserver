<?php
/**
 * Created by PhpStorm.
 * User: yueping
 * Date: 2017/6/22
 * Time: 23:46
 * email:596169733@qq.com
 */

class Server {
    private $ip;
    private $port;
    private $www_root;

    function __construct($config)
    {
        var_dump($config);
        $this->ip = $config['ip'];
        $this->port = $config['port'];
        $this->www_root = $config['www_root'];
        $this->await();
    }

    private function await()
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

        if (!$sock) {
            echo "ERROR:" . socket_strerror(socket_last_error()) . "\n";
            die();
        }

        $ret = socket_bind($sock, $this->ip, $this->port);

        if (!$ret) {
            echo "ERROR:". socket_strerror(socket_last_error()) . "\n";
            die();
        }

        echo "SUCCESS\n";
        $ret = socket_listen($sock);

        if (!$ret) {
            throw new Exception("ERROR:" . socket_strerror(socket_last_error()) . "\n");
        }
        do {
            $new_sock = null;

            try {
                $new_sock = socket_accept($sock);
            } catch (Exception $e) {
                echo $e->getMessage();
                echo "ACCEPT FAILED:" . socket_strerror(socket_last_error()) . "\n";
            }

            try {
                $request = socket_read($new_sock, 1024);
                $response = $this->output($request);

                socket_write($new_sock, $response);
                socket_close($new_sock);
            } catch (Exception $e){
                echo $e->getMessage();
                echo "READ FAILED:" . socket_strerror(socket_last_error()) . "\n";
            }
        } while(true);
    }

    /**
     * @param $request
     * @return mixed
     */
    private function output($request)
    {
        echo $request;

        $request_array = explode(" ", $request);

        if (count($request_array) < 2) {
            return $this->not_found();
        }

        $uri = $request_array[1];

        $filename = $this->www_root . $uri;

        echo "request:" . $filename . "\n";

        //静态文件处理

        if (file_exists($filename)) {
            return $this->add_header(file_get_contents($filename));
        } else {
            return $this->not_found();
        }
    }

    /**
     * 404
     * @return string
     */
    private function not_found()
    {
        $content = "<h1>File Not Found </h1>";
        return "HTTP/1.1 404 File Not Found\r\nContent-Type: text/html\r\nContent-Length: ".strlen($content)."\r\n\r\n".$content;
    }

    /**
     * 加上头信息
     * @param $string
     * @return string
     */
    private function add_header($string){
        return "HTTP/1.1 200 OK\r\nContent-Length: ".strlen($string)."\r\nServer: mengkang\r\n\r\n".$string;
    }
}