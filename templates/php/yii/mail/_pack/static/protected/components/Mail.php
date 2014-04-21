<?php

require_once Yii::getPathOfAlias('application.extensions.phpmailer') . '/class.phpmailer.php';

class Mail extends CComponent
{
	public $from = null;
	public $replyTo = null;
	public $isHtml = false;
	public $charset = 'UTF-8';
	public $subject = '';
	public $debug = false;
	
	protected $_body;
	protected $_mailer;
	
	public function __construct()
	{
		$config = MailOptions::instance();
		$mailer = new PHPMailer(true);
		$mailer->PluginDir = Yii::getPathOfAlias('application.extensions.phpmailer') . '/';
		if ($config->method == 'smtp') {
			$mailer->IsSMTP();
			$mailer->SMTPAuth = true;
			$mailer->Host = $config->smtp_host;
			$mailer->Username = $config->smtp_login;
			$mailer->Password = $config->smtp_pass;
			if ('' != trim($config->smtp_port)) {
				$mailer->Port = $config->smtp_port;
			}
			if ($config->smtp_ssl == '1') {
				$mailer->SMTPSecure = 'ssl';
			}
			if (strpos($config->smtp_login, '@') !== false) {
				$this->from = $config->smtp_login;
			} else {
				$this->from = 'noreply@' . $_SERVER['HTTP_HOST'];
			}
		} else {
			if (empty($this->from)) {
				$this->from = 'noreply@' . $_SERVER['HTTP_HOST'];
			}
			$mailer->IsMail();
		}
		$this->_mailer = $mailer;
	}
	
	public function render($script, $renderOptions=array())
	{
		$script = Yii::getPathOfAlias($script) . '.php';
		$this->_body = Yii::app()->controller->renderFile($script, $renderOptions, true);
	}
	
	public function send($to)
	{
		if ($this->debug) {
			ob_start();
			echo "To: " . implode(',', (array)$to) . "\r\n";
			echo "Subject: {$this->subject}\r\n";
			echo "\r\n{$this->_body}";
			echo "\r\n\r\n------Message Boundary------\r\n\r\n";
			file_put_contents(Yii::app()->runtimePath . "/mailer-debug", ob_get_clean(), FILE_APPEND);
			return true;
		} else {
			
			$this->_mailer->ClearReplyTos();
			$this->_mailer->ClearAllRecipients();
			$this->_mailer->ClearAttachments();
			$this->_mailer->ClearCustomHeaders();
			
			if (! empty($this->replyTo)) {
				$this->_mailer->AddReplyTo($this->replyTo);
			}
			
			$this->_mailer->SetFrom($this->from);
			
			foreach ((array)$to as $address) {
				$this->_mailer->AddAddress($address);
			}
			
			$this->_mailer->Subject = $this->subject;
			
			if ($this->isHtml) {
				$this->_mailer->MsgHTML($this->_body);
			} else {
				$this->_mailer->Body = $this->_body;
			}
			
			$this->_mailer->CharSet = $this->charset;
			
			try {
				return $this->_mailer->Send();
			} catch (Exception $e) {
				return false;
			}
		}
	}
}
