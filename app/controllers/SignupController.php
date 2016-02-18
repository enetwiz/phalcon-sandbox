<?php
use Phalcon\Mvc\Controller;

class SignupController extends Controller
{

    public function indexAction()
    {
        $a = 1;
    }

    public function registerAction()
    {
        $user = new Users();

        // Store and check for errors
        $success = $user->save($this->request->getPost(), array('name', 'email', 'password'));

        if ($success) {
            $this->flash->success('Dziekujemy za rejestracje');
        } else {
            $errormsg = "Niestety wystapily bledy:<br/>";
            foreach ($user->getMessages() as $message) {
                $errormsg .=  $message->getMessage() . "<br/>";
            }
            $this->flash->error($errormsg);
        }

    }
}