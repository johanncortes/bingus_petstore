<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header("Location: auth_controlador.php"); exit(); }
require_once 'comprador_modelo.php';
require_once 'config.php';

class Comprador_controller {
    private $model;

    public function __construct() { $this->model = new Comprador_model(); }

    public function listar() {
        $compradores = $this->model->getCompradores();
        include 'comprador_view.php';
    }
}

$c = new Comprador_controller();
$a = $_GET['action'] ?? 'listar';
if ($a == 'listar') $c->listar();
else $c->listar();
?>
