<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
$this->menu=array(
    array('label'=>'Project Dashboard', 'url'=>array('/project')),
    array('label'=>'User Management', 'url'=>array('/user')),
    array('label'=>'RBAC Management', 'url'=>array('/rights')),
);

?>

<h1>Welcome to <?php echo CHtml::encode(Yii::app()->name); ?></h1>

<?php if(!Yii::app()->user->isGuest):?>
    <p>
        You last logged in on <?php echo Yii::app()->user->lastLoginTime; ?>.
    </p>
<?php endif;?>