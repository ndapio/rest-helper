<?php

use NDAPio\RestHelper\RestApi;

class SampleRestful extends RestApi {

    public function __construct($base_url = "https://sample.com") {
        $this->base_url = $base_url;
    }
	
    public function sample() {
        $args = array(
            "path" => "/path",
            "params" => array(
                "param_1" => "value_1",
            ),
            "headers" => array(
                "Header 1" => "Value 1"
            ),
            "data_type" => "json:json", // value => json:json, form:json, form:xml
            // default if not defined => json:json
        );
        return $this->doGET($args); // doPOST, doPUT, doDELETE
    }
	
	public function getAccessToken() {
        // do business here
        $this->token = "token";
    }


    public function getDataWithToken() {
        $args = array(
            "path" => "/path",
            "params" => array(
                "param_1" => "value_1",
            ),
            "headers" => array(
                "Authorization" => $this->headerAuthBearer(),
            )
        );
        return $this->doGET($args);
    }

    public function postDataWithUsernamePassword($username, $password) {
        $args = array(
            "path" => "/path",
            "params" => array(
                "param_2" => "value_2",
            ),
            "headers" => array(
                "Authorization" => $this->headerAuthBasic($username, $password)
            )
        );
        return $this->doPOST($args);
    }

    public function postDataInFormFormat($paramters_in_array) {
        $args = array(
            "path" => "/path",
            "params" => $paramters_in_array,
            "data_type" => "form:json",
        );
        return $this->doPOST($args);
    }

    public function postDataWithProxy($paramters_in_array) {
        $this->addProxy("111.222.333.444:3128");
        $args = array(
            "path" => "/path",
            "params" => $paramters_in_array,
            "data_type" => "form:json",
        );
        return $this->doPOST($args);
    }

}
