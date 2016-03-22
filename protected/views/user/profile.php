<?php
/** @var User $model */
/** @var UserProfile $profile */
$this->pageTitle=Yii::app()->name . ' - '."Profile";
$this->breadcrumbs=array(
	"Profile",
);
$this->menu=array(
	(UserManager::loginUserIsAdmin()?
        array('label'=>'Manage Users', 'url'=>array('admin')) : array()),
    array('label'=>'Update Profile', 'url'=>array('update')),
    array('label'=>'Change password', 'url'=>array('changePassword')),
    array('label'=>'Logout', 'url'=>array('/site/logout')),
);
?><h1><?php echo 'Your profile'; ?></h1>

<?php if(Yii::app()->user->hasFlash('profileMessage')): ?>
<div class="success">
	<?php echo Yii::app()->user->getFlash('profileMessage'); ?>
</div>
<?php endif; ?>
<table class="dataGrid">
	<tr>
		<th class="label"><?php echo CHtml::encode($model->getAttributeLabel('userName')); ?></th>
	    <td><?php echo CHtml::encode($model->userName); ?></td>
	</tr>
	<?php
        /** @var UserProfileField[] $profileFields */
		$profileFields=UserProfileField::model()->forOwner()->sort()->findAll();
		if ($profileFields)
        {
			foreach($profileFields as $field)
            {
			?>
	<tr>
		<th class="label"><?php echo CHtml::encode($field->title); ?></th>
    	<td><?php echo ($field->widgetView($profile) ?
                $field->widgetView($profile):CHtml::encode(($field->range ?
                    UserProfile::range($field->range,$profile->getAttribute($field->varName)):$profile->getAttribute($field->varName)))); ?></td>
	</tr>
			<?php
			}//$profile->getAttribute($field->varname)
		}
	?>
	<tr>
		<th class="label"><?php echo CHtml::encode($model->getAttributeLabel('email')); ?></th>
    	<td><?php echo CHtml::encode($model->email); ?></td>
	</tr>
	<tr>
		<th class="label"><?php echo CHtml::encode($model->getAttributeLabel('createTime')); ?></th>
    	<td><?php echo $model->createTime; ?></td>
	</tr>
	<tr>
		<th class="label"><?php echo CHtml::encode($model->getAttributeLabel('lastLoginTime')); ?></th>
    	<td><?php echo $model->lastLoginTime; ?></td>
	</tr>
	<tr>
		<th class="label"><?php echo CHtml::encode($model->getAttributeLabel('status')); ?></th>
    	<td><?php echo CHtml::encode(User::itemAlias("UserStatus",$model->status)); ?></td>
	</tr>
</table>
