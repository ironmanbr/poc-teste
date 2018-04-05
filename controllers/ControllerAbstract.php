<?php

require_once __DIR__ . '/../plugins/Template.php';

abstract class ControllerAbstract
{
    protected $request;
    protected $action;
    protected $view;

    protected $actionDefault = 'index';
    protected $actionsValids = ['index', 'show', 'save', 'delete'];

    public function __construct($request)
    {
        $this->request = $request;
    }

    final public function run($action)
    {
        $this->setAction($action)
            ->validateActionOrDefault()
            ->callAction();

        return $this;
    }

    final protected function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    final protected function validateActionOrDefault()
    {
        if (!in_array($this->action, $this->actionsValids)) {
            $this->action = $this->actionDefault;
        }

        return $this;
    }

    protected function callAction()
    {
        $method = $this->action . 'Action';

        $this->$method();

        return $this;
    }

    public function view()
    {
        return $this->view;
    }

    abstract public function indexAction();

    abstract public function showAction();

    abstract public function saveAction();

    abstract public function deleteAction();

}