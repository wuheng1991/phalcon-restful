<?php

namespace Api\Modules\Backend\Controllers;
use Phalcon\Mvc\Model;
use Backend\Services\AuthServer;

class AuthController extends ControllerBase
{

    public function onConstruct()
    {
        #parent::onConstruct();//继承父类构造

        $this->authServer = new AuthServer();
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function indexAction(){
        $ret = $this->authServer->listDataService();
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: post
     * @return json
     */
    public function createAction(){
        $params = $this->request->getPost();
        $ret = $this->authServer->addDataService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: put
     * @return json
     */
    public function saveAction($id){
        if($id){
            $params = $this->request->getPut();
            $ret = $this->authServer->saveDataService($id, $params);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: delete
     * @return json
     */
    public function deleteAction($id)
    {
        if($id){
            $ret = $this->authServer->deleteDataService($id);
            return $this->response->setJsonContent($ret);
        }
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function getRolesAction(){
        $ret = $this->authServer->getRolesDataService();
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function addAuthAssignmentAction(){
        $params = $this->request->get();
        $ret = $this->authServer->addAuthAssignmentService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: get
     * @return json
     */
    public function getAuthAssignmentAction(){
        $params = $this->request->get();
        $ret = $this->authServer->getAuthAssignmentService($params);
        return $this->response->setJsonContent($ret);
    }

    /**
     * Method Http accept: put
     * @return json
     */
    public function saveAuthAssignmentAction(){
        $params = $this->request->getPut();
        $ret = $this->authServer->saveAuthAssignmentService($params);
        return $this->response->setJsonContent($ret);
    }

}

