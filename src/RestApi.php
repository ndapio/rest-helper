<?php

namespace NDAP\RestHelper;

class RestApi {

    protected string $base_url;
    protected string $proxy_line;
    protected string $token;
    protected string $username;
    protected string $password;
    protected RequestService $service;

    public function __construct($base_url) {
        $this->base_url = $base_url;
        $this->service = new RequestService();
        $this->proxy_line = "";
    }

    public function addProxy($proxy_line) {
        $this->proxy_line = $proxy_line;
    }
	
	public function headerAuthBasic($username = "", $password = "") {
		if ($username != "") {
			$this->username = $username;
		}
		if ($password != "") {
			$this->password = $password;
		}
		return "Basic: ".$this->username.":".$this->password;
	}
	
	public function headerAuthBearer($token = "") {
		if ($token != "") {
			$this->token = $token;
		}
		return "Bearer: ".$this->token;
	}

    public function init($args = array()): array {
        $data = array();
        if (isset($args["path"]) && $args["path"] != "") {
            $data["url"] = $this->base_url.$args["path"];
        } else {
            $data["url"] = $this->base_url;
        }
        if (isset($args["params"]) && (is_array($args["params"]) || is_object($args["params"]))) {
            $data["params"] = $args["params"];
        } else {
            $data["params"] = array();
        }
        if (isset($args["headers"]) && is_array($args["headers"])) {
            $data["headers"] = $args["headers"];
        } else {
            $data["headers"] = array();
        }
        if (isset($args["data_type"]) && $args["data_type"] != "") {
            $data["data_type"] = $args["data_type"];
        } else {
            $data["data_type"] = "json:json";
        }
        if (isset($args["response_type"]) && $args["response_type"] != "") {
            $data["response_type"] = $args["response_type"];
        } else {
            $data["response_type"] = "full";
        }
        if (isset($args["proxy_line"]) && $args["proxy_line"] != "") {
            $data["proxy"] = $args["proxy_line"];
        } else {
            if ($this->proxy_line != "") {
                $data["proxy"] = $this->proxy_line;
            } else {
                $data["proxy"] = "";
            }
        }
        return $data;
    }

    public function doGET($args = array()) {
        $data = $this->init($args);
        return $this->service->send("GET", $data["url"], $data["response_type"], $data["data_type"], $data["headers"], $data["params"], $data["proxy"]);
    }

    public function doPOST($args = array()) {
        $data = $this->init($args);
        return $this->service->send("POST", $data["url"], $data["response_type"], $data["data_type"], $data["headers"], $data["params"], $data["proxy"]);
    }

    public function doPUT($args = array()) {
        $data = $this->init($args);
        return $this->service->send("PUT", $data["url"], $data["response_type"], $data["data_type"], $data["headers"], $data["params"], $data["proxy"]);
    }

    public function doDELETE($args = array()) {
        $data = $this->init($args);
        return $this->service->send("DELETE", $data["url"], $data["response_type"], $data["data_type"], $data["headers"], $data["params"], $data["proxy"]);
    }



}
