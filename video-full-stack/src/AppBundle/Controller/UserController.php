<?php
/**
 * Created by IntelliJ IDEA.
 * User: Tu Lugar Favorito
 * Date: 10/01/2017
 * Time: 11:29 AM
 */

namespace AppBundle\Controller;


use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use EntityBundle\Entity\User;


class UserController extends BaseController
{
    protected function mapearUser($email,$name,$surname,$password,$role,$image,$createdAt,$user = null){
        if($user == null) {
            $user = new User();
        }
        $user->setEmail($email);
        $user->setName($name);
        $user->setSurname($surname);
        if($password != null) {
            $user->setPassword($password);
        }
        $user->setRole($role);
        $user->setImage($image);
        $user->setCreatedAt($createdAt);
        return $user;
    }

    public function newAction(Request $request){
        $this->helpers = $this->get("app.helpers");
        $json = $request->get("json",null);
        $params = json_decode($json);

        if($json != null){
            $createdAt = new \DateTime("now");
            $image = null;
            $email = (isset($params->email)) ? $params->email : null;
            $name = (isset($params->name) && ctype_alpha($params->name)) ? $params->name : null;
            $surname = (isset($params->surname) && ctype_alpha($params->surname)) ? $params->surname : null;
            $password = (isset($params->password)) ? $params->password : null;
            $role = "USER";
            $validate_email = $this->validateEmail($email);
            if(count($validate_email)==0 && $email!= null && $name!=null && $surname!=null && $password!= null){
                $user = $this->mapearUser($email,$name,$surname,$this->helpers->encriptPassword($password),$role,$image,$createdAt);
                $em = $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository("EntityBundle:User")
                    ->findOneBy(array("email" => $email));
                if(count($isset_user) == 0){
                    $em->persist($user);
                    $em->flush();
                    $this->data["status"] = "success";
                    $this->data["code"] = 200;
                    $this->data["msg"] = "New User Created !!";
                }
                else
                {
                    $this->data["msg"] =  "User not created !!";
                }
            }
            else
            {
                $this->data["msg"] =  "Usuario ya existe !!";
            }
        }
        return $this->helpers->jsonData($this->data);
    }

    public function editAction(Request $request){
        

        $this->data["msg"] = "User not created";
        if($this->helpers->authCheck($request)) {
            $identity = $this->helpers->authCheck($request,true);
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("EntityBundle:User")->find($identity->sub);
            $json = $request->get("json", null);

            $params = json_decode($json);

            if ($json != null) {
                $createdAt = new \DateTime("now");
                $image = null;
                $email = (isset($params->email)) ? $params->email : null;
                $name = (isset($params->name) && ctype_alpha($params->name)) ? $params->name : null;
                $surname = (isset($params->surname) && ctype_alpha($params->surname)) ? $params->surname : null;
                $password = (isset($params->password)) ? $params->password : null;
                $role = "USER";
                $validate_email = $this->validateEmail($email);
                if (count($validate_email) == 0 && $email != null && $name != null && $surname != null && $password != null) {
                    $user = $this->mapearUser($email, $name, $surname,
                                    $this->helpers->encriptPassword($password), $role, $image, $createdAt,$user);
                    $em = $this->getDoctrine()->getManager();
                    $isset_user = $em->getRepository("EntityBundle:User")
                        ->findBy(array("email" => $email));

                    if (count($isset_user) == 0 || $identity->email == $email) {
                        $em->persist($user);
                        $em->flush();
                        $this->data["status"] = "success";
                        $this->data["code"] = 200;
                        $this->data["msg"] = "Usuario Actualizado !!";
                    } else {
                        $this->data["msg"] = "Userio no Actualizado !!";
                    }
                } else {
                    $this->data["msg"] = "Datos incorrecto !!";
                }
            }
        }
        else{
            $this->data["msg"] = "No tiene acceso a este url";
        }
        return $this->helpers->jsonData($this->data);
    }

    public function uploadImageAction(Request $request){
        $this->helpers = $this->get("app.helpers");
        $this->data["msg"] = "Error upload imagen";
        if($this->helpers->authCheck($request)){
            $identity = $this->helpers->authCheck($request,true);
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository("EntityBundle:User")->find($identity->sub);

            //upload file

            $file = $request->files->get("image");

            if(!empty($file) && $file != null){
                $ext = $file->guessExtension();
                if($ext === "jpg" || $ext === "jpeg" || $ext === "png" || $ext === "gif") {
                    $file_name = $user->getId() . "_" . time() . "." . $ext;


                    $file->move("uploads/users", $file_name);
                    //borrar imagen anterior
                    $image = $user->getImage();
                    $user->setImage($file_name);
                    $em->persist($user);
                    $em->flush();


                    $this->data["status"] = "success";
                    $this->data["code"] = 200;
                    $this->data["msg"] = "El imagen se cargo.";
                }
                else
                {
                    $this->data["msg"] = "Formato de imagen not valid";
                }
            }
            else{
                $this->data["msg"] = "El imagen no se actualizo";
            }
        }

        return $this->helpers->jsonData($this->data);
    }

    public function channelAction(Request $request,$id = null){
        
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository("EntityBundle:User")->find($id);
        if($id === null) {
            $dql = "select v.id,v.title,v.description,v.image,v.videoPath,u.name,u.surname,u.id as userid from EntityBundle:Video v JOIN v.user u ORDER BY v.id DESC";
            $query = $em->createQuery($dql);
        }
        else
        {
            $dql = "select v.id,v.title,v.description,v.image,v.videoPath,u.name,u.surname,u.id as userid from EntityBundle:Video v JOIN v.user u WHERE u = :id ORDER BY v.id DESC";
            $query = $em->createQuery($dql)->setParameter('id',$id);
        }


        $page = $request->query->getInt("page",1);

        $paginator = $this->get("knp_paginator");
        $items_per_page = 6;
        $paginator = $paginator->paginate($query,$page,$items_per_page);
        $total_items_count = $paginator->getTotalItemCount();
        if(count($user) == 1) {
            $this->data = array(
                "status" => "success",
                "code" => 200,
                "total_items_count" => $total_items_count,
                "page_actual" => $page,
                "items_per_page" => $items_per_page,
                "total_pages" => ceil($total_items_count / $items_per_page),
            );
            $this->data["data"]["videos"] = $paginator;
            $this->data["data"]["user"] = $this->helpers->cleanUser($user);
        } else {
            $this->data["msg"] = "Usuario no existe";
        }

        return $this->helpers->jsonData($this->data);

    }

}