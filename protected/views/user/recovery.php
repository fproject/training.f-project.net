<?php
/* @var $this UserController */
/* @var $model UserRecoveryForm */
$this->pageTitle=Yii::app()->name . ' - '."User Recovery";
$this->breadcrumbs=array(
    "Login" => array('site/login',
        "User Recovery"),
);
?>

    <h1><?php echo "User Recovery"; ?></h1>

<?php if(Yii::app()->user->hasFlash('recoveryMessage')): ?>
    <div class="success">
        <?php echo Yii::app()->user->getFlash('recoveryMessage'); ?>
    </div>
<?php else: ?>

    <div class="form">

        <?php
        /** @var ActiveForm $form */
        $form=$this->beginWidget('ActiveForm', array(
            'id'=>'recovery-form',
            'enableAjaxValidation'=>false,
            'clientOptions'=>array(
                'validateOnSubmit'=>true,
            ),
            'htmlOptions' => array('enctype'=>'multipart/form-data'),
        )); ?>

        <?php echo $form->errorSummary(array($model)); ?>

        <div class="row">
            <?php echo $form->labelEx($model,'accountNameOrEmail'); ?>
            <?php echo $form->telField($model,'accountNameOrEmail') ?>
            <p class="hint"><?php echo "Enter your username or email address."; ?></p>
        </div>

        <?php if(CCaptcha::checkRequirements()): ?>
            <div class="row">
                <?php echo $form->labelEx($model,'verifyCode'); ?>

                <?php $this->widget('CCaptcha'); ?>
                <?php echo $form->textField($model,'verifyCode'); ?>

                <p class="hint"><?php echo "Please enter the letters as they are shown in the image above."; ?>
                    <br/><?php echo "Letters are not case-sensitive."; ?></p>
            </div>
        <?php endif; ?>

        <div class="row submit">
            <?php echo CHtml::submitButton("Recover"); ?>
        </div>

        <?php $this->endWidget(); ?>
    </div><!-- form -->
<?php endif; ?>