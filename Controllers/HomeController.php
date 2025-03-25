<?php
class HomeController extends Controller {
    public function index() {
        $this->render('home/index');
    }

    public function dashboardNotifs() {
        $this->render('home/notifications');
    }
    public function alert() {
        $this->render('home/alert');
    }

    public function eventAlert() {
        $this->render('home/event_alert');
    }
    
    
}
