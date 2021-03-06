<?php

namespace application\models;

use application\core\base\Model;
use application\controllers\ErrorController;

class Camagru extends Model {
    private $login;
    private $postArray = [];
    private $route = ['controller' => 'Error', 'action' => 'whooops'];
    public $message = '';

    public function __construct($allVariables) {
        parent::__construct();
        if ($_SESSION['login'] && !count($allVariables)) {
            ErrorController::whooopsAction($this->route, "Вы отправили пустой запрос!");
            exit;
        }
        if (!$_SESSION['login']) {
            ErrorController::errorPage();
            exit;
        }
        $this->login = $_SESSION['login'];
        $this->postArray = $allVariables;
        $this->table = 'user';
    }

    public function changeLogin() {
        $tmp = $this->postArray['login'];
        if (!count($checkLogin = $this->findOne($this->postArray['login'], "login"))) {
            if ($trueFalse = $this->checkLoginRegular($this->postArray['login'])) {
                $changeLogin = $this->findOne($this->login, "login");
                $prevLogin = $this->login;
                $this->login = $_SESSION['login'] = $this->postArray['login'];
                $this->updateOne($this->table, "login", "\"$tmp\"", "id", $changeLogin[0]['id']);
                $this->updateUserLogin("images", "user", "\"" . $prevLogin . "\"", "\"" . $this->postArray['login'] . "\"");
                $this->updateUserLogin("likes", "user", "\"" . $prevLogin . "\"", "\"" . $this->postArray['login'] . "\"");
                $this->updateUserLogin("comments", "user", "\"" . $prevLogin . "\"", "\"" . $this->postArray['login'] . "\"");
                header('Location: /camagru/cabinet');
            } else {
                ErrorController::whooopsAction($this->route, $this->message);
            }
        }else {
            ErrorController::whooopsAction($this->route, "Такой логин уже занят!");
        }
    }

    function changePassword() {
        if ($this->postArray['pass'] == "" || $this->postArray['repass'] == "") {
            ErrorController::whooopsAction($this->route, "Должны быть заполнены оба поля с паролем!");
            exit;
        }
        $pass = password_hash($this->postArray['pass'], PASSWORD_DEFAULT);
        if ($this->postArray['pass'] != $this->postArray['repass']) {
            ErrorController::whooopsAction($this->route, "Пароли не совпадают!");
            exit;
        }
        if (!preg_match("/(?=^.{8,}$)((?=.*\d)|(?=.*\W+))(?![.\n])(?=.*[A-Z])(?=.*[a-z]).*$/", $this->postArray['pass'])) {
            $this->message = "Пароль должен состоять минимум" . "<br>" . " из 8 символов, одной цифры, одной буквы" . "<br>" . "в верхнем регистре и одной в нижнем";
            ErrorController::whooopsAction($this->route,  $this->message);
            exit;
        }
        $checkPassword = $this->findOne($this->login, "login");
        $this->updateOne($this->table, "password", "\"$pass\"", "id", $checkPassword[0]['id']);
        header('Location: /camagru/cabinet');
    }

    public function changeName() {
        if ($this->postArray['name'] == "") {
            ErrorController::whooopsAction($this->route, "Имя не может быть пустым!");
            exit;
        }
        $name = trim(htmlspecialchars(stripslashes($this->postArray['name'])));
        $checkName = $this->findOne($this->login, "login");
        $this->updateOne($this->table, "name", "\"$name\"", "id", $checkName[0]['id']);
        header('Location: /camagru/cabinet');
    }
    public function changeMail() {
        if ($this->postArray['email'] == "") {
            ErrorController::whooopsAction($this->route, "Поле Email не должно быть пустым!");
            exit;
        }
        if (filter_var($this->postArray['email'], FILTER_VALIDATE_EMAIL)) {
            $checkMail = $this->findOne($this->login, "login");
            $mail = $this->postArray['email'];
            if (!$this->findOne($mail, "email")) {
                $this->updateOne($this->table, "email", "\"$mail\"", "id", $checkMail[0]['id']);
                header('Location: /camagru/cabinet');
            }else {
                ErrorController::whooopsAction($this->route, "Такой Email уже занят!");
                exit;
            }
        }else {
            ErrorController::whooopsAction($this->route, "Неверный формат Email!");
        }
    }

    public function checkLoginRegular($login) {
        $len = strlen($login);
        if (empty($login)) {
            $this->message = 'Логин не может быть пустым';
            return false;
        }
        if ($len < 4 || $len > 20) {
            $this->message = 'В логине должно быть от 4 до 20 символов';
            return false;
        }
        if (!preg_match("/^[a-zA-Z1-9]+$/", $login)) {
            $this->message = 'В логине должны быть только латинские буквы';
            return false;
        }

        if (!empty($login)) {
            if (is_numeric($login{0})) {
                $this->message = 'Логин должен начинаться с буквы';
                return false;
            }
        }
        return true;
    }
}
