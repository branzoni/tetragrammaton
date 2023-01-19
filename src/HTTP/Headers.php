<?php

namespace Tet\HTTP;

use Tet\Params;

class Headers extends Params
{
    function setContentDescriptionHeader($value){
        $this->data["Content-Description"] = $value;
    }

    function setContentTypeHeader($value){
        $this->data["Content-Type"] = $value;
    }

    function setContentLengthHeader($value){
        $this->data["Content-Length"] = $value;
    }

    function setContentDispositionHeader($value){
        $this->data["Content-Disposition"] = $value;
    }

    function setContentTransferEncodingHeader($value){
        $this->data["Content-Transfer-Encoding"] = $value;
    }

    function setExpiresHeader($value){
        $this->data["Expires"] = $value;
    }

    function setCacheControlHeader($value){
        $this->data["Cache-Control"] = $value;
    }

    function setPragmaHeader($value){
        $this->data["Pragma"] = $value;
    }    

    function getContentDescriptionHeader(){
        return $this->data["Content-Description"];
    }

    function getContentTypeHeader(){
        return  $this->data["Content-Type"];
    }

    function getContentLengthHeader(){
        return $this->data["Content-Length"];
    }

    function getContentDispositionHeader(){
        return $this->data["Content-Disposition"];
    }

    function getContentTransferEncodingHeader(){
        return $this->data["Content-Transfer-Encoding"];
    }   

    function getCacheControlHeader(){
        return $this->data["Cache-Control"];
    }

    function getPragmaHeader(){
        return $this->data["Pragma"];
    }    

    function getExpiresHeader(){
        return $this->data["Expires"];
    }
}