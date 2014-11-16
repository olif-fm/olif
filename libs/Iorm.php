<?php
namespace Olif;

interface  IcontrollerORM {
    public function set($id);
    public function get($field = "");
    public function assign();
}
