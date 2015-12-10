<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Base extends Controller_Template {

	public $template = 'template';
	public $body = 'index';
	public $auto_render = TRUE;
    public $title = 'Usbo';
    public $styles = array();

	public function before()
	{
		//die("<html><body><center><br><br><h3>Уважаемые посетители.</h3><p>Внезапно на хостинге majordomo пропали все мои базы данных.</p><p>Ведется переписка с тех.поддержкой в надежде восстановить их из бэкапа. Пожалуйста, зайдите позже. Если есть вопросы: <a href=\"mailto:feedback@usbo.info\">feedback@usbo.info</a></p><p align=\"right\"><small>Дата актуальности: 29.07.2015 18:54 (МСК)</small></p></body></html>");
		parent::before();
		session_start();
		if ($this->auto_render === TRUE)
		{
			$this->body = View::factory($this->body);
		}
		Helper_User::auth();
	}    
    
	public function after()
	{
		Cookie::set('action', $this->request->controller());
		$this->template->set('title', $this->title);
        $this->template->set('template', $this->body->render());
		$this->template->set('user_data', Helper_User::get_user_data());
        $this->template->set('styles', $this->styles);
		parent::after();
	}

} // End Base
