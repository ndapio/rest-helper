<?php

use NDAPio\RestHelper\RestApi;

class RestCountries extends RestApi {

    public function __construct($base_url = "https://restcountries.com/v3.1") {
        // doc: https://restcountries.com/
        $this->base_url = $base_url;
    }

    public function getAllCountries() {
        // format: https://restcountries.com/v3.1/all
        $args = array(
            "path" => "/all",
            "params" => array()
        );
        return $this->doGET($args);
    }

    public function searchCountryByName($name) {
        // format: https://restcountries.com/v3.1/name/{name}?fullText=true
        $args = array(
            "path" => "/name/".$name,
            "params" => array(
                "fullText" => true
            )
        );
        return $this->doGET($args);
    }

}
