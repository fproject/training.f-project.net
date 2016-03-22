<?php
/* @var $this UserController */
/* @var $form UserChangePasswordForm */
$this->pageTitle=Yii::app()->name . ' - '."Change Password";
$this->breadcrumbs=array(
	"Profile" => array('/user/profile'),
	"Change Password",
);
$this->menu=array(
	(UserManager::loginUserIsAdmin()?
        array('label'=>'Manage Users', 'url'=>array('admin')) : array()),
    array('label'=>'User Profile', 'url'=>array('profile')),
    array('label'=>'Update User Profile', 'url'=>array('update')),
    array('label'=>'Logout', 'url'=>array('/site/logout')),
);
?>

<h1><?php echo "Change password"; ?></h1>

<div class="form">
<?php
    /** @var CActiveForm $widget */
    $widget=$this->beginWidget('CActiveForm', array(
	'id'=>'changePassword-form',
	'enableAjaxValidation'=>false,
	'clientOptions'=>array(
		'validateOnSubmit'=>true,
	),
)); ?>

	<p class="note"><?php echo 'Fields with <span class="required">*</span> are required.'; ?></p>
	<?php echo $widget->errorSummary($form); ?>
	
	<div class="row">
	<?php echo $widget->labelEx($form,'oldPassword'); ?>
	<?php echo $widget->passwordField($form,'oldPassword'); ?>
	<?php echo $widget->error($form,'oldPassword'); ?>
	</div>
	
	<div class="row">
	<?php echo $widget->labelEx($form,'password'); ?>
	<?php echo $widget->passwordField($form,'password'); ?>
	<?php echo $widget->error($form,'password'); ?>
	<p class="hint">
	<?php echo "Minimal password length 4 symbols."; ?>
	</p>
	</div>
	
	<div class="row">
	<?php echo $widget->labelEx($form,'verifyPassword'); ?>
	<?php echo $widget->passwordField($form,'verifyPassword'); ?>
	<?php echo $widget->error($form,'verifyPassword'); ?>
	</div>
	
	
	<div class="row submit">

	<?php echo CHtml::submitButton("Save"); ?>
	</div>

<?php $this->endWidget(); ?>
</div><!-- form -->