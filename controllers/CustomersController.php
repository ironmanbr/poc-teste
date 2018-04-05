<?php

require_once '../data/Customer.php';
require_once 'ControllerAbstract.php';

class CustomersController extends ControllerAbstract
{
    protected $error;
    protected $url = '?app=customers';

    public function indexAction()
    {
        $itemsHTML[] = '<tr><td colspan="2">Não há clientes cadastrados</td></tr>';

        $items = (new Customer())->all();
        if ($items) {
            $itemTPL = new Template('customers.list.item.html');
            $itemsHTML = [];
            foreach ($items as $item) {
                $itemTPL->clearValues()
                        ->parser('url', $this->url)
                        ->parser('id', $item['id'])
                        ->parser('name', $item['name'])
                        ->parser('created_at', $item['created_at']);

                $itemsHTML[] = $itemTPL->render();
            }
        }

        $appTPL = new Template('customers.list.html');
        $appTPL->items = implode('', $itemsHTML);
        $this->view = $appTPL->render();

        return $this;
    }

    public function showAction()
    {
        $record = [];

        $id = trim(@$this->request['id']);
        if ($id) {
            try {
                $record = (new Customer())->readOrFail($id);
            } catch (Exception $e) {
                // Whoops... preciso por um alertar que não encontrou o registo para editar.. =/
            }
        }

        $this->fillForm($record);

        return $this;
    }

    public function saveAction()
    {
        if ($this->validate()) {
            $this->saveData()
                ->redirectToList();
        }

        $this->fillForm($this->request);

        return $this;
    }

    public function deleteAction()
    {
        $id = trim(@$this->request['id']);
        if ($id) {
            try {
                (new Customer())->delete($id);
            } catch (Exception $e) {
                //
            }
        }

        $this->redirectToList();

        return $this;
    }

    protected function saveData()
    {
        $customer = new Customer();
        
        $id = trim(@$this->request['id']);
        if ($id) {
            try {
                $customer->readOrFail($id);
            } catch (Exception $e) {
                // Whoops... preciso por um alertar que não encontrou o registo para atualizar.
            }

            $customer->update($this->request);
        } else {
            $customer->create($this->request);
        }

        return $this;
    }

    protected function fillForm($params = [])
    {
        $appTPL = new Template('customers.form.html');
        $appTPL->clearValues()
            ->parser('id', @$params['id'])
            ->parser('name', @$params['name'])
            ->parser('name.error', $this->hasErrorField('name'))
            ->parser('email', @$params['email'])
            ->parser('email.error', $this->hasErrorField('email'));

        $this->view = $appTPL->render();
    }

    protected function validate()
    {
        $this->error = [];

        $fields = ['name', 'email'];
        foreach ($fields as $field) {
            if (!trim(@$this->request[$field])) {
                $this->error[$field] = true;
            }
        }

        return count($this->error) <= 0;
    }

    protected function hasErrorField($field)
    {
        return @$this->error[$field] ? 'has-error' : '';
    }

    protected function redirectToList()
    {
        header("Location: {$this->url}");
        exit;
    }
}