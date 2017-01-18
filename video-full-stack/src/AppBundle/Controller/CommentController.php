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
use DateTime;
use EntityBundle\Entity\User;
use EntityBundle\Entity\Video;
use EntityBundle\Entity\Comment;



class CommentController extends BaseController
{

    public function newAction(Request $request){
        $this->data["msg"] = "Message not created";
        if($this->helpers->authCheck($request)) {
            $identity = $this->helpers->authCheck($request, true);
            $json = $request->get("json",null);
            if($json != null){
                $params = get_object_vars(json_decode($json));

                $user_id = $this->helpers->checkNotNull($identity->sub);
                $video_id = $this->helpers->issetValue($params, "video_id");
                $body = $this->helpers->issetValue($params,"body");
                if($user_id != null){
                    if($video_id != null){
                        $em= $this->getDoctrine()->getManager();
                        $user = $em->getRepository("EntityBundle:User")->find($user_id);
                        $video = $em->getRepository("EntityBundle:Video")->find($video_id);
                        if(count($user) == 1 && count($video) && $body != null){
                            $comment = new Comment();
                            $comment->setBody($body);
                            $comment->setCreatedAt(new DateTime('now'));
                            $comment->setUser($user);
                            $comment->setVideo($video);
                            $em->persist($comment);
                            $em->flush();
                            $this->data = array(
                                "msg" => "se guardo el comentario",
                                "code" => 200,
                                "status" => "success"
                            );
                        } else {
                            $this->data["msg"] = "No se guardo el comentario.";
                        }
                    }else{
                        $this->data["msg"] = "Id de Video invalido.";
                    }
                }else{
                    $this->data["msg"] = "Usuario invalido.";
                }


            } else {
                $this->data["msg"] = "Los parametros están incorrecto";
            }

        }
        else{
            $this->data["msg"] = "Usuario no autorizado o no logeado";
        }

        return $this->helpers->jsonData($this->data);
    }

    public function deleteAction(Request $request, $id = null){

        $this->data["msg"] = "Message no borrado";
        if($this->helpers->authCheck($request)) {
            $identity = $this->helpers->authCheck($request, true);
            $user_id = $this->helpers->checkNotNull($identity->sub);
            $em = $this->getDoctrine()->getManager();
            $comment = $em->getRepository("EntityBundle:Comment")->find($id);
            if(is_object($comment) && $user_id != null){
                if($user_id != null &&
                    ($user_id == $comment->getUser()->getId()
                        || $user_id == $comment->getVideo()->getUser()->getId())){
                    $em->remove($comment);
                    $em->flush();

                    $this->data = array(
                        "status" => "success",
                        "code" => 200,
                        "msg" => "Comentario eliminado exitoso."
                    );
                }
                else {
                    $this->data["msg"] = "Comentario no borrado por falta de derechos.";
                }
            }
            else{

            }

        } else {
            $this->data["msg"] = "Usuario no reconocido";
        }
        return $this->helpers->jsonData($this->data);
    }

    public function listAction(Request $request,$video_id = null){
        $em = $this->getDoctrine()->getManager();

        $video = $em->getRepository("EntityBundle:Video")->find($video_id);
        $comments = $em->getRepository("EntityBundle:Comment")->findBy( array("video"=>$video),array("id"=>"desc"));
        if(count($comments) >= 1){
            $this->data =  array(
                "status" => "success",
                "code"  => 200,
                "data" =>$comments
            );
        } else {
            $this->data["msg"] = "Este video no tiene comentarios";
        }

        return $this->helpers->jsonData($this->data);

    }


}