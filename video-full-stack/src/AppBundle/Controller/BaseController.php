<?php
/**
 * Created by IntelliJ IDEA.
 * User: Tu Lugar Favorito
 * Date: 10/01/2017
 * Time: 12:55 PM
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\DependencyInjection\ContainerInterface;
use AppBundle\Services\Helpers;

class BaseController extends Controller
{

    protected $data;
    protected $helpers;

    public function __construct()
    {
        $this->data = array(
            "status" => "error",
            "code" => 400,
            "msg" => "No tienes acceso a este servicio."
        );


    }

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->helpers = $this->get("app.helpers");
    }
    protected function validateEmail($email){
        $emailConstraint = new Email();
        $emailConstraint->message = "This email is not valid";

        return $this->get("validator")->validate($email,$emailConstraint);

    }

}