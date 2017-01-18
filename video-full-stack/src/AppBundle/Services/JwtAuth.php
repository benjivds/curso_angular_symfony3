<?php
/**
 * Created by IntelliJ IDEA.
 * User: Tu Lugar Favorito
 * Date: 10/01/2017
 * Time: 08:26 AM
 */

namespace AppBundle\Services;

use Firebase\JWT\JWT;

class JwtAuth
{
    private $em;
    private $key;
    private $expire;
    private $encoding;
    private $keyEncode;

    public function __construct($em)
    {
        $this->em = $em;
        $this->key = "clave-secreta";
        $this->expire = 7 * 24 * 60 *60;
        $this->encoding = "HS256";
        $this->keyEncode = 15;
    }

    private function getUsuario($email,$password){
        $user = $this->em->getRepository("EntityBundle:User")
            ->findOneBy(
                array(
                    "email" => $email,
                 //   "password" => $password
                )
            );
        if(is_object($user) && count($user) == 1){
            if($this->verifyPassword($password,$user->getPassword())){
                if (password_needs_rehash($user->getPassword(), PASSWORD_DEFAULT, ['cost' => $this->keyEncode])) {
                    $user->setPassword($this->encriptPassword($password));
                    $this->em->persist($user);
                    $this->em->flush();
                }
                return $user;
            }

        }
        return null;

    }

    private function createToken($user){
        return array(
            "sub"       => $user->getId(),
            "email"     => $user->getEmail(),
            "name"      => $user->getName(),
            "surname"   => $user->getSurname(),
            "password"  => $user->getPassword(),
            "image"     => $user->getImage(),
            "iat"       => time(),
            "exp"       => time() + $this->expire,
        );
    }

    public function encriptPassword($password){
        return password_hash(hash('sha256',$password), PASSWORD_DEFAULT, ['cost' => $this->keyEncode]);
    }

    public function verifyPassword($password,$hash){
        return password_verify(hash('sha256',$password),$hash);
    }




    public function login($email, $password, $getHash = NULL){
        $existe = false;
        $user = $this->getUsuario($email,$password);

        if(is_object($user)){
            $existe = true;
        }

        if($existe){
            $token = $this->createToken($user);

            $jwt = JWT::encode($token,$this->key,$this->encoding);
            $decoded = JWT::decode($jwt,$this->key,array($this->encoding));
            if($getHash != null){
                return $jwt;
            } else {
                return $decoded;
            }

            //return array("status" => "success", "data" => "Login  Success  !!");
        }
        else {
            return false;
        }

    }

    public function checkToken($jwt, $getIdentity = false){
        $correcto = false;

        try {
            $decoded = JWT::decode($jwt,$this->key,array($this->encoding));

        }
        catch(\UnexpectedValueException $e){
            $correcto = false;
        }
        catch(\DomainException $e){
            $correcto = false;
        }
        if(isset($decoded->sub)){
            $correcto = true;
        }
        if($getIdentity == true && isset($decoded) && is_object($decoded)){
            return $decoded;
        }
        return $correcto;
    }
}