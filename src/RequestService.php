<?php

namespace NDAPio\RestHelper;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class RequestService {

    private Client $client;
    private ResponseInterface $response;

    public function __construct() {
        $this->client = new Client();
    }

    public function getResponse($response_type, $data_type = "json:json") {
        $data_types = $this->getDataTypes($data_type);
        $output = $data_types["output"];
        if (isset($this->response)) {
            $response = "";
            if ($response_type == "body") {
                $response = $this->response->getBody();
            }
            if ($response_type == "full") {
                $response_data = $this->response->getBody();
                $response_data_parsed = "";
                if ($output == "json") {
                    $response_data_parsed = json_decode($response_data, true);
                }
                if ($output == "string") {
                    $response_data_parsed = $response_data;
                }
                if ($output == "xml" || $output == "xmlobject") {
                    $response_data_parsed = simplexml_load_string($response_data);
                }
                if ($output == "xmlarray") {
                    $response_data_parsed = json_decode(json_encode((array)simplexml_load_string($response_data)), true);
                }
                $code = $this->response->getStatusCode();
                $cookie_line = $this->response->getHeaderLine('set-cookie');
                $cookie_line = str_replace("expires=Sun,", "expires=Sun", $cookie_line);
                $cookie_line = str_replace("expires=Sat,", "expires=Sat", $cookie_line);
                $cookie_line = str_replace("expires=Fri,", "expires=Fri", $cookie_line);
                $cookie_line = str_replace("expires=Thu,", "expires=Thu", $cookie_line);
                $cookie_line = str_replace("expires=Wed,", "expires=Wed", $cookie_line);
                $cookie_line = str_replace("expires=Tue,", "expires=Tue", $cookie_line);
                $cookie_line = str_replace("expires=Mon,", "expires=Mon", $cookie_line);
                $cookies_temp = explode(',', $cookie_line);
                $cookies = array();
                $cookies_ignore = array(
                    "expires", "domain", "path", "max-age", "samesite"
                );
                foreach ($cookies_temp as $temp) {
                    $temp_parts = explode(";", trim($temp));
                    foreach ($temp_parts as $part) {
                        $part_parts = explode("=", trim($part));
                        if (count($part_parts) == 2) {
                            $key = trim($part_parts[0]);
                            if (!in_array(strtolower($key), $cookies_ignore)) {
                                $cookies[$key] = trim($part_parts[1]);
                            }
                        } else {
                            if (count($part_parts) > 2) {
                                if (count($part_parts) == 3) {
                                    $key = trim($part_parts[0]);
                                    $cookies[$key] = trim($part_parts[1]) . "=" . trim($part_parts[2]);
                                }
                                if (count($part_parts) == 4) {
                                    $key = trim($part_parts[0]);
                                    $cookies[$key] = trim($part_parts[1]) . "==" . trim($part_parts[3]);
                                }
                            }
                        }
                    }
                }
                if ($code >= 200 && $code <= 299) {
                    $response = array(
                        "status" => "success",
                        "time" => time(),
                        "code" => $code,
                        "cookies" => $cookies,
                        "response" => $response_data_parsed
                    );
                } else {
                    $response = array(
                        "status" => "error",
                        "time" => time(),
                        "code" => $code,
                        "cookies" => $cookies
                    );
                }
            }
            if ($response_type == "redirect") {
                $response = $this->response->getHeaderLine('X-Guzzle-Redirect-History');
            }
            if ($response_type == "cookie") {
                $response = $this->response->getHeaderLine('set-cookie');
            }
            return $response;
        } else {
            if ($data_type == "json") {
                return "[]";
            } else {
                return "";
            }
        }
    }

    public function getDataTypes($data_type) {
        $data_type_parts = explode(":", $data_type);
        if (count($data_type_parts) == 2) {
            return array(
                "input" => $data_type_parts[0],
                "output" => $data_type_parts[1]
            );
        } else {
            return array(
                "input" => $data_type,
                "output" => $data_type
            );
        }
    }

    public function getGuzzleOptions($method, $data_type, $headers = [], $params = [], $proxy_line = "") {
        $data_types = $this->getDataTypes($data_type);
        $input = $data_types["input"];
        $guzzle_options = array();
        $guzzle_options["http_errors"] = false;
        if ($proxy_line != "") {
            $proxies = array(
                "http" => $proxy_line,
                "https" => $proxy_line
            );
            $guzzle_options["proxy"] = $proxies;
        }
        if ($method == "POST" || $method == "PUT" || $method == "DELETE") {
            if ($input == "json" || $input == "") {
                $headers["Content-Type"] = "application/json";
                $guzzle_options["body"] = json_encode($params);
            }
            if ($input == "form") {
                $headers["Content-Type"] = "application/x-www-form-urlencoded";
                $guzzle_options["form_params"] = $params;
            }
        }
        if ($method == "GET") {

        }
        $guzzle_options["headers"] = $headers;
        return $guzzle_options;
    }

    public function send($method, $url, $response_type, $data_type, $headers = [], $params = [], $proxy_line = "") {
        if ($method == "GET") {
            $query_string = http_build_query($params);
            $url = $url . "?" . $query_string;
        }
        try {
            $guzzle_options = $this->getGuzzleOptions($method, $data_type, $headers, $params, $proxy_line);
            $this->response = $this->client->request($method, $url, $guzzle_options);
            return $this->getResponse($response_type, $data_type);
        } catch (\Exception $e) {
            $action = $method . " " . $url;
            return $this->handleException($action, $e, $headers, $params, $proxy_line);
        }
    }

    public function handleException($action, $exception, $headers, $params, $proxy = "") {
        return array(
            "status" => "exception",
            "code" => 000,
            "time" => time(),
            "response" => array(
                "message" => $exception->getMessage(),
                "action" => $action,
                "headers" => $headers,
                "params" => $params,
                "proxy" => $proxy
            )
        );
    }

}
