<div class="row">
	<div class="hidden-xs" style="padding-top: 100px;"></div>
	<div style="padding-top: 20px;"></div>
	<div class="col-lg-4 col-lg-offset-4">
		<div class="panel panel-default">
			<div class="panel-heading">Авторизация на сайте <?php echo CHtml::encode(Yii::app()->name); ?></div>
			<div class="panel-body">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'id' => 'login-form',
				)); ?>
					<?php echo $form->error($model, 'username', array('class'=>'alert alert-danger')); ?>
					<?php echo $form->error($model, 'password', array('class'=>'alert alert-danger')); ?>
					
					<div class="form-group">
						<?php echo $form->labelEx($model, 'username'); ?>
						<?php echo $form->textField($model, 'username', array('class'=>'form-control')); ?>
					</div>
					<div class="form-group">
						<?php echo $form->labelEx($model, 'password'); ?>
						<?php echo $form->passwordField($model, 'password', array('class'=>'form-control')); ?>
					</div>
					<?php if(Yii::app()->user->allowAutoLogin): ?>
						<div class="checkbox">
							<label>
								<?php echo $form->checkbox($model, 'rememberMe'); ?>
								<?php echo $model->getAttributeLabel('rememberMe'); ?>
							</label>
						</div>
					<?php endif; ?>
					<div class="form-group">
						<button type="submit" class="btn btn-success">
							<i class="icon-lock icon-white"></i>
							Войти
						</button>
					</div>
				<?php $this->endWidget(); ?>
			</div>
		</div>
	</div>
</div>
