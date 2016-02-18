<?php
use Phalcon\Mvc\Controller;

class SessionController extends Controller
{

    private function _registerSession($user) {
        $this->session->set(
            'auth',
            array(
                'id'   => $user->id,
                'name' => $user->name
            )
        );
    }

    public function indexAction()
    {

    }

    /**
     * This action authenticate and logs a user into the application
     */
    public function startAction() {
        if ($this->request->isPost()) {

            // Get the data from the user
            $name    = $this->request->getPost('name');
            $password = $this->request->getPost('password');

            // Find the user in the database
            $user = Users::findFirst(
                array(
                    "name = :name: AND password = :password:",
                    'bind' => array(
                        'name'    => $name,
                        'password' => $password
                    )
                )
            );

            if ($user != false) {

                $this->_registerSession($user);

                // Forward to the another controller if the user is valid
                return $this->dispatcher->forward(
                    array(
                        'controller' => 'index',
                        'action'     => 'index'
                    )
                );
            }

            $this->flash->error('Wrong email/password');
        }

        // Forward to the login form again
        return $this->dispatcher->forward(
            array(
                'controller' => 'session',
                'action'     => 'index'
            )
        );
    }
}