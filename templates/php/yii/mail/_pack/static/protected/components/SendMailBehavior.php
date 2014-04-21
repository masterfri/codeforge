<?php

class SendMailBehavior extends CActiveRecordBehavior
{
	public $to;
	public $subject;
	public $template;
	
	public function afterSave()
	{
		if ($this->owner->getIsNewRecord()) {
			$email = Html::normalizeOption($this->to);
			if ('' != trim($email)) {
				$mailer = new Mail();
				$mailer->debug = defined('DEBUG_MAIL') && DEBUG_MAIL;
				$mailer->subject = Html::normalizeOption($this->subject);
				$mailer->isHtml = true;
				$template = Html::normalizeOption($this->template);
				if (empty($template)) {
					$template = sprintf('application.views.mail.%s', lcfirst(get_class($this->owner)));
				}
				$mailer->render($template, array(
					'model' => $this->owner,
				));
				return $mailer->send(preg_split('#\s*,\s*#', $email, -1, PREG_SPLIT_NO_EMPTY));
			}
		}
	}
}
