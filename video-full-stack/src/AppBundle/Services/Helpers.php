<?php
/**
 * Created by IntelliJ IDEA.
 * User: Tu Lugar Favorito
 * Date: 09/01/2017
 * Time: 10:45 PM
 */

namespace AppBundle\Services;

use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Response;
use EntityBundle\Entity\User;


class Helpers
{
    private $jwt_auth;

    public function __construct($jwt_auth)
    {
        $this->jwt_auth = $jwt_auth;
    }

    public function jsonData($data){

        $normalizer = array(new GetSetMethodNormalizer());
        $encoder = array("json" => new JsonEncode());

        $serializer = new Serializer($normalizer,$encoder);
        $json = $serializer->serialize($data,'json');

        $response = new Response();
        $response->setContent($json);
        $response->headers->set("Conent-Type","application/json");
        return $response;
    }

    public function encriptPassword($password){
        if($password == null){
            return null;
        }
        return $this->jwt_auth->encriptPassword($password);
    }

    public function verifyPassword($password,$hash){
        return $this->jwt_auth->verifyPassword($password,$hash);
    }

    public function jsonDataWithStatus($status,$data){
        $dataToSend = array("status" => $status,
                            "data" => $data);
        return $this->jsonData($dataToSend);
    }

    public function jsonDataSuccess($data){
        return $this->jsonDataWithStatus("success",$data);
    }

    public function jsonDataError($data){
        return $this->jsonDataWithStatus("error",$data);
    }

    public function authCheck($request,$getIdentify = false){
        $hash = $request->get("authorization",null);
        $check_token = null;
        if($hash != null){
              $check_token = $this->jwt_auth->checkToken($hash,$getIdentify);
        }
        return $check_token;
    }

    public function issetValue($params,$parametro){
        return (isset($params[$parametro]))? $params[$parametro] : null;
    }

    public function checkNotNull($value){
        return ($value!= null) ? $value :null;
    }

    public function checkFile($file){
        return ($file != null && !empty($file));
    }

    public function cleanUser(User $user){
        $user->setEmail(null);
        $user->setPassword(null);
        $user->setRole(null);
        return $user;
    }

}