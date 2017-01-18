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



class VideoController extends BaseController
{
    protected $pathVideo;
    protected $pathImage;

    public function __construct()
    {
        parent::__construct();
        $this->pathImage = "uploads/video/image/video";
        $this->pathVideo = "uploads/video/video/video";
    }




    protected function mapearVideo($title, $description, $user, $status, $createdAt, $updatedAt, $videoPath = null, $image = null, $video = null)
    {
        if ($video == null) {
            $video = new Video();
        }
        if ($createdAt != null) {
            $video->setCreatedAt($createdAt);
        }
        if ($description != null) {
            $video->setDescription($description);
        }
        if ($image != null) {
            $video->setImage($image);
        }

        if ($status != null) {
            $video->setStatus($status);
        }
        if ($title != null) {
            $video->setTitle($title);
        }
        $video->setUpdatedAt(new DateTime('now'));
        if ($user != null) {
            $video->setUser($user);
        }
        if ($videoPath != null) {
            $video->setVideoPath($videoPath);
        }

        return $video;
    }

    public function newAction(Request $request)
    {
        
        if ($this->helpers->authCheck($request)) {

            $json = $request->get("json", null);
            if ($json != null) {
                $identity = $this->helpers->authCheck($request, true);
                $params = json_decode($json);
                $paramsArray = get_object_vars($params);
                $createdAt = new DateTime('now');
                $updatedAt = $createdAt;
                $image = null;
                $videoPath = null;
                $status = null;
                $user_id = $this->helpers->checkNotNull($identity->sub);
                $title = $this->helpers->issetValue($paramsArray, "title");
                $description = $this->helpers->issetValue($paramsArray, "description");
                $status = $this->helpers->issetValue($paramsArray, "status");
                if ($user_id != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();
                    $user = $em->getRepository("EntityBundle:User")->find($identity->sub);

                    $video = $this->mapearVideo($title, $description, $user, $status, $createdAt, $updatedAt, $videoPath, $image);
                    $em->persist($video);
                    $em->flush();
                    //$video = $em->getRepository("EntityBundel:video")->find($video->getId());
                    $this->data["msg"] = "video guardado con exito";
                    $this->data["status"] = "success";
                    $this->data["code"] = 200;
                    $this->data["data"] = $video;
                } else {
                    $this->data["msg"] = "No estas logeado o no tiene titulo el video";
                }


            } else {
                $this->data["msg"] = "Los parametres del POST son incorrecto";
            }
        }
        return $this->helpers->jsonData($this->data);
    }

    public function editAction(Request $request, Video $video)
    {
        $this->helpers = $this->get("app.helpers");
        if ($this->helpers->authCheck($request)) {

            $json = $request->get("json", null);
            if ($json != null) {
                $identity = $this->helpers->authCheck($request, true);

                $params = json_decode($json);
                $paramsArray = get_object_vars($params);
                $createdAt = null;
                $updatedAt = $createdAt;
                $image = null;
                $videoPath = null;
                $status = null;
                $user_id = $this->helpers->checkNotNull($identity->sub);
                $title = $this->helpers->issetValue($paramsArray, "title");
                $description = $this->helpers->issetValue($paramsArray, "description");
                $status = $this->helpers->issetValue($paramsArray, "status");

                if ($user_id != null && $title != null) {
                    $em = $this->getDoctrine()->getManager();
                    $user = null;//$em->getRepository("EntityBundle:User")->find($identity->sub);

                    if ($user_id === $video->getUser()->getId()) {
                        $video = $this->mapearVideo($title, $description, $user, $status, $createdAt, $updatedAt, $videoPath,
                            $image, $video);
                        $em->persist($video);
                        $em->flush();
                        $this->data["msg"] = "video editado con exito";
                        $this->data["status"] = "success";
                        $this->data["code"] = 200;

                    } else {
                        $this->data["msg"] = "El usuario no es igual como el dueno del video";
                    }
                    //$video = $em->getRepository("EntityBundel:video")->find($video->getId());

                } else {
                    $this->data["msg"] = "No estas logeado o no tiene titulo el video o video no encontrado";
                }

            } else {
                $this->data["msg"] = "Los parametres del POST son incorrecto";
            }
        }
        return $this->helpers->jsonData($this->data);
    }

    protected function moveFile($path_of_file,$video_id,$file,$image = false){
        $ext = $file->guessExtension();
        if($image){
            if(!($ext == "jpeg" || $ext == "jpg" || $ext == "png")){
                return false;
            }
        }
        else{
            if(!($ext=="mp4" || $ext == "mp3")){
                return false;
            }
        }
        $file_name = time().".".$ext;
        $path_of_file = $path_of_file.$video_id;
        $file->move($path_of_file,$file_name);
        return $file_name;
    }

    public function uploadAction(Request $request,Video $video)
    {
        $this->helpers = $this->get("app.helpers");
        if ($this->helpers->authCheck($request) && $video != null) {
            $identity = $this->helpers->authCheck($request, true);
            $em = $this->getDoctrine()->getManager();
            $user_id = $this->helpers->checkNotNull($identity->sub);
           // $video = $em->getRepository("EntityBundle:Video")->find($video_id);
            if ($user_id === $video->getUser()->getId()) {
                $file_image = $request->files->get("image");
                $file_video = $request->files->get("video");
                if($this->helpers->checkFile($file_image)){
                    $file_name = $this->moveFile($this->pathImage,$video->getId(),$file_image,true);
                    if($file_name != false) {
                        $video->setImage($file_name);
                        $em->persist($video);
                        $em->flush();
                        $this->data["status"] = "success";
                        $this->data["code"] = 200;
                        $this->data["msg"] = "Imagen de video modificado.";
                    }else{
                        $this->data["msg"] = "Extension archivo image incorrecto";
                    }

                } else {
                    if ($this->helpers->checkFile($file_video)) {
                        $file_name = $this->moveFile($this->pathVideo, $video->getId(), $file_video,false);
                        if($file_name != false) {
                            $video->setVideoPath($file_name);
                            $em->persist($video);
                            $em->flush();
                            $this->data["status"] = "success";
                            $this->data["code"] = 200;
                            $this->data["msg"] = "Archivo de video modificado.";
                        }else{
                            $this->data["msg"] = "Extension archivo video incorrecto";
                        }

                    } else {
                        $this->data["msg"] = "No hay archivos para subir, video no modificado";
                    }
                }
            }
            else{
                $this->data["msg"] = "No tiene acceso para editar este video.";
            }

        }
        return $this->helpers->jsonData($this->data);
    }

    public function listAction(Request $request){
        $this->helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        $dql = "select v.id,v.title,v.description,v.image,v.videoPath,u.name,u.surname,u.id as userid from EntityBundle:Video v JOIN v.user u ORDER BY v.id DESC";

        $query = $em->createQuery($dql);

        $page = $request->query->getInt("page",1);

        $paginator = $this->get("knp_paginator");
        $items_per_page = 6;
        $paginator = $paginator->paginate($query,$page,$items_per_page);
        $total_items_count = $paginator->getTotalItemCount();

        $this->data = array(
            "status" => "success",
            "code"   => 200,
            "total_items_count" => $total_items_count,
            "page_actual" => $page,
            "items_per_page" => $items_per_page,
            "total_pages" => ceil($total_items_count/$items_per_page),
            "data" => $paginator
        );

        return $this->helpers->jsonData($this->data);
    }

    public function lastVideosAction(Request $request){
        $this->helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();

        $dql = "select v.id,v.title,v.description,v.image,v.videoPath,u.name,u.surname,u.id as userid from EntityBundle:Video v JOIN v.user u  ORDER BY v.createdAt DESC";

        $query = $em->createQuery($dql)->setMaxResults(5);
        $videos = $query->getResult();
        $this->data = array(
            "status" => "success",
            "code"   => 200,
            "data" => $videos
        );
        return $this->helpers->jsonData($this->data);

    }

    public function videoDetailAction(Request $request,$video_id = null){
        $this->helpers = $this->get("app.helpers");
        $video = $this->getDoctrine()->getManager()->getRepository("EntityBundle:Video")->find($video_id);
        $this->data["msg"] = "El Video no existe";
        $video->setUser($this->helpers->cleanUser($video->getUser()));
        if($video) {
            $this->data = array(
                "status" => "success",
                "code" => 200,
                "data" => $video
            );
        }
        return $this->helpers->jsonData($this->data);
    }
    public function searchAction(Request $request,$search = null){
        $this->helpers = $this->get("app.helpers");
        $em = $this->getDoctrine()->getManager();
        if($search != null){
            $dql = "select v.id,v.title,v.description,v.image,v.videoPath,u.name,u.surname,u.id as userid from EntityBundle:Video v JOIN v.user u "
                ." where v.title LIKE :search OR v.description LIKE :search ORDER BY v.id DESC";
        } else {
            $dql = "select v.id,v.title,v.description,v.image,v.videoPath,u.name,u.surname,u.id as userid from EntityBundle:Video v JOIN v.user u  ORDER BY v.id DESC";
        }
        $query = $em->createQuery($dql);
        if($search != null){
            $query->setParameter('search',"%$search%");
        }
        $page = $request->query->getInt("page",1);

        $paginator = $this->get("knp_paginator");
        $items_per_page = 6;
        $paginator = $paginator->paginate($query,$page,$items_per_page);
        $total_items_count = $paginator->getTotalItemCount();

        $this->data = array(
            "status" => "success",
            "code"   => 200,
            "total_items_count" => $total_items_count,
            "page_actual" => $page,
            "items_per_page" => $items_per_page,
            "total_pages" => ceil($total_items_count/$items_per_page),
            "data" => $paginator
        );
        return $this->helpers->jsonData($this->data);


    }


}