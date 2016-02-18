<?php
use Phalcon\Acl;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Role;
use Phalcon\Events\Event;
use Phalcon\Mvc\User\Plugin;
use Phalcon\Mvc\Dispatcher;

class SecurityPlugin extends Plugin {

    const ROLE_GUEST = 'guest';
    const ROLE_USER = 'user';

    /**
     * Returns an existing or new access control list
     *
     * @returns AclList
     */
    public function getAcl()
    {
        $acl = new AclList();
        $acl->setDefaultAction(Acl::DENY);

        //Register roles
        $roles = array(
            'user'  => new Role(self::ROLE_USER),
            'guest' => new Role(self::ROLE_GUEST)
        );
        foreach ($roles as $role) {
            $acl->addRole($role);
        }

        //Private area resources
        $privateResources = array(
            'index'    => array('restricted'),
        );
        foreach ($privateResources as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }
        //Public area resources
        $publicResources = array(
            'index'      => array('index'),
            'session'      => array('index', 'start'),
            'signup'      => array('index', 'register'),
        );
        foreach ($publicResources as $resource => $actions) {
            $acl->addResource(new Resource($resource), $actions);
        }

        //Grant access to public areas to both users and guests
        foreach ($roles as $role) {
            foreach ($publicResources as $resource => $actions) {
                foreach ($actions as $action){
                    $acl->allow($role->getName(), $resource, $action);
                }
            }
        }
        //Grant access to private area to role Users
        foreach ($privateResources as $resource => $actions) {
            foreach ($actions as $action){
                $acl->allow(self::ROLE_USER, $resource, $action);
            }
        }

        return $acl;
    }

    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher) {

        // Check whether the "auth" variable exists in session to define the active role
        $auth = $this->session->get('auth');
        if (!$auth) {
            $role = self::ROLE_GUEST;
        } else {
            $role = self::ROLE_USER;
        }

        // Take the active controller/action from the dispatcher
        $controller = $dispatcher->getControllerName();
        $action = $dispatcher->getActionName();

        // Obtain the ACL list
        $acl = $this->getAcl();

        // Check if the Role have access to the controller (resource)
        $allowed = $acl->isAllowed($role, $controller, $action);
        if ($allowed != Acl::ALLOW) {

            // If he doesn't have access forward him to the index controller
            $this->flash->error("You don't have access to this module");
            $dispatcher->forward(
                array(
                    'controller' => 'session',
                    'action' => 'index'
                )
            );

            // Returning "false" we tell to the dispatcher to stop the current operation
            return false;
        }
    }

}