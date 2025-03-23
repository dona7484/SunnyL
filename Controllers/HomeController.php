<?php
class HomeController extends Controller {
    public function index() {
        $this->render('home/index');
    }

    public function dashboard() {
        $this->render('home/dashboard');
    }
}
