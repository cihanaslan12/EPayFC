<?php

require_once "framework/Controller.php";
require_once "model/Item.php";

class ControllerItem extends Controller {
    public function index(): void {
        $this->browse();
    }

    public function browse(): void {

    }
}