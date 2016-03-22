<?php

/* @var $this UserController */
/* @var $model UserRegistrationForm */
/* @var $profile UserProfile */

$this->pageTitle=Yii::app()->name . ' - Registration';
$this->breadcrumbs=array(
	"Registration",
);
?>

<h1><?php echo "Registration"; ?></h1>

<?php if(Yii::app()->user->hasFlash('registration')): ?>
<div class="success">
<?php echo Yii::app()->user->getFlash('registration'); ?>
</div>
<?php else: ?>

<div class="form">
<?php
/** @var ActiveForm $form */
$form=$this->beginWidget('ActiveForm', array(
	'id'=>'registration-form',
	'enableAjaxValidation'=>true,
	'disableAjaxValidationAttributes'=>array('UserRegistrationForm_verifyCode'),
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
	'htmlOptions' => array('enctype'=>'multipart/form-data'),
)); ?>

	<p class="note"><?php echo 'Fields with <span class="required">*</span> are required.'; ?></p>
	
	<?php echo $form->errorSummary(array($model,$profile)); ?>
	
	<div class="row">
	<?php echo $form->labelEx($model,'userName'); ?>
	<?php echo $form->textField($model,'userName'); ?>
	<?php echo $form->error($model,'userName'); ?>
	</div>
	
	<div class="row">
	<?php echo $form->labelEx($model,'password'); ?>
	<?php echo $form->passwordField($model,'password',['value'=>'']); ?>
	<?php echo $form->error($model,'password'); ?>
	<p class="hint">
	<?php echo "Minimal password length 4 symbols."; ?>
	</p>
	</div>
	
	<div class="row">
	<?php echo $form->labelEx($model,'verifyPassword'); ?>
	<?php echo $form->passwordField($model,'verifyPassword',['value'=>'']); ?>
	<?php echo $form->error($model,'verifyPassword'); ?>
	</div>
	
	<div class="row">
	<?php echo $form->labelEx($model,'email'); ?>
	<?php echo $form->textField($model,'email'); ?>
	<?php echo $form->error($model,'email'); ?>
	</div>
	
<?php 
		$profileFields=$profile->getFields();
		if ($profileFields) {
			foreach($profileFields as $field)
            {
?>
            <div class="row">
                <?php echo $form->labelEx($profile,$field->varName); ?>
                <?php
                if ($widgetEdit = $field->widgetEdit($profile))
                {
                    echo $widgetEdit;
                }
                elseif ($field->range)
                {
                    echo $form->dropDownList($profile,$field->varName,UserProfile::range($field->range));
                }
                elseif ($field->fieldType=="TEXT")
                {
                    echo$form->textArea($profile,$field->varName,array('rows'=>6, 'cols'=>50));
                }
                else
                {
                    echo $form->textField($profile,$field->varName,array('size'=>60,'maxlength'=>(($field->fieldSize)?$field->fieldSize:255)));
                }
                 ?>
                <?php echo $form->error($profile,$field->varName); ?>
            </div>
<?php
			}
		}
?>
    <?php if(CCaptcha::checkRequirements()): ?>
	<div class="row">
		<?php echo $form->labelEx($model,'verifyCode'); ?>
		
		<?php $this->widget('CCaptcha'); ?>
		<?php echo $form->textField($model,'verifyCode'); ?>
		<?php echo $form->error($model,'verifyCode'); ?>
		
		<p class="hint"><?php echo "Please enter the letters as they are shown in the image above."; ?>
		<br/><?php echo "Letters are not case-sensitive."; ?></p>
	</div>
	<?php endif; ?>
	
	<div class="row submit">
		<?php echo CHtml::submitButton("Register"); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->
<?php endif; ?>