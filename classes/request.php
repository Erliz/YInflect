<?php
/**
 * User: Stanislav Vetlovskiy
 * Date: 16.02.2013
 */

class Request
{
    private $curl;
    private $url = 'http://export.yandex.ru/inflect.xml?name=';
    private $proxy = false;
    public $name;

    function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->curl, CURLOPT_AUTOREFERER, 1);
        //curl_setopt($this->curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($this->curl, CURLOPT_MAXREDIRS, 30);
        // это необходимо, чтобы cURL не высылал заголовок на ожидание
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Expect:'));
        // не проверять SSL сертификат
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
        // не проверять Host SSL сертификата
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYHOST, false);
        // версия HTTP запроса
        curl_setopt($this->curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($this->curl, CURLOPT_HEADER, 1);
    }

    /**
     * @param string $ip   - ip:port
     * @param string $user - login:passwd
     *
     * @return bool
     */
    public function setProxy($ip, $user = null)
    {
        curl_setopt($this->curl, CURLOPT_HTTPPROXYTUNNEL, 0);
        curl_setopt($this->curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($this->curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
        curl_setopt($this->curl, CURLOPT_PROXY, $ip);
        if ($user) {
            curl_setopt($this->curl, CURLOPT_PROXYUSERPWD, $user);
        }
        $this->proxy = true;

        return true;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function get($name)
    {
        $result = $this->parse($this->request($name));

        return $result;
    }

    /**
     * @param string $name
     *
     * @return array
     */
    private function request($name)
    {
        curl_setopt($this->curl, CURLOPT_URL, $this->url . urlencode($name));
        while (true) {
            $answer = curl_exec($this->curl);
            $info = curl_getinfo($this->curl);
            $result['header'] = array_diff(explode("\r\n", substr($answer, 0, $info['header_size'])), array(''));
            $body = substr($answer, $info['header_size']);
            $result['data'] = $body;
            if ($info['http_code'] != '200') {
                sleep(1);
                echo 'retrying' . "\n";
                continue;
            }
            break;
        }

        return $result;
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function parse(array $data)
    {
        $xml = simplexml_load_string($data['data']);
        $result = (array)$xml->inflection;
        unset($result['@attributes']);
        if (count($result) == 1) {
            $result = $result + array_fill(1, 5, '');
        }

        return $result;
    }
}
