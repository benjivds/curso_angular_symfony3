<?php

namespace AppBundle\Controller;

use AppBundle\Controller\BaseController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends BaseController
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }


    public function pruebasAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('EntityBundle:User')->findAll();
       // return $this->json($this->jsonData($users));
        return $this->helpers->jsonData($users);
    }


    public function loginAction(Request $request){
        $jwt_auth = $this->get("app.jwt_auth");

        $json = $request->get("json",null);

        if($json != null){
            $params = json_decode($json);
            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;

            $validate_email = $this->validateEmail($email);

            if(count($validate_email)==0 && $password != null){
                if($getHash != null){
                    $login = $jwt_auth->login($email,$password,true);
                } else {
                    $login = $jwt_auth->login($email,$password);
                }

                //return $this->helpers->jsonData($login);
                if(!$login){
                    return $this->helpers->jsonDataError("Error Login!");
                }
                else {

                    return new JsonResponse(array("estatus"=>"success","data"=>$login));
                    //return $this->helpers->jsonDataSuccess($login);
                }
            }
            else{
                return $this->helpers->jsonDataError("Login no Valido!!");
            }
        }
        else{
            return $this->helpers->jsonDataError("Send json with post!!");
        }
    }

    public function checkTokenAction(Request $request){
        $hash = $request->get("authorization",null);

        $check = $this->helpers->authCheck($hash,true);
        var_dump($check);
        die();
    }

}
