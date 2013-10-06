<?php
class ctrl_cat extends crtltemplate
{
    public function index() {}
    public function getContractor()
    {
        $tmp = $this->model->getContractor();
        return $tmp;
    }
    public function addNewContractor()
    {
        $tmp = $this->model->addNewContractor();
        return $tmp;
    }
    public function updateContractor()
    {
        $tmp = $this->model->updateContractor();
        return $tmp;
    }
    public function getDiscount()
    {
        $tmp = $this->model->getDiscount();
        return $tmp;
    }
    public function addNewPrice()
    {
        $tmp = $this->model->addNewPrice();
        return $tmp;
    }
    public function getPrice()
    {
        $tmp = $this->model->getPrice();
        return $tmp;
    }
    public function getWork()
    {
        $tmp = $this->model->getWork();
        return $tmp;
    }
    public function savePriceItem()
    {
        $tmp = $this->model->savePriceItem();
        return $tmp;
    }
}
?>