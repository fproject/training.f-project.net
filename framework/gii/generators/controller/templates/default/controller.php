<?php
/**
 * This is the template for generating a controller class file.
 * The following variables are available in this template:
 * - $this: the ControllerCode object
 */
?>
<?php echo "<?php\n"; ?>
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net <?php echo date("Y"); ?>. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
class <?php echo $this->controllerClass; ?> extends <?php echo $this->baseClass."\n"; ?>
{
	/* ************************************************************************
	 *
	 * Gii Generated properties and methods
	 *
	 *********************************************************************** */

<?php foreach($this->getActionIDs() as $action): ?>
	public function action<?php echo ucfirst($action); ?>()
	{
		$this->render('<?php echo $action; ?>');
	}

<?php endforeach; ?>
	// Uncomment the following methods and override them if needed
	/*
	public function filters()
	{
		// return the filter configuration for this controller, e.g.:
		return array(
			'inlineFilterName',
			array(
				'class'=>'path.to.FilterClass',
				'propertyName'=>'propertyValue',
			),
		);
	}

	public function actions()
	{
		// return external action classes, e.g.:
		return array(
			'action1'=>'path.to.ActionClass',
			'action2'=>array(
				'class'=>'path.to.AnotherActionClass',
				'propertyName'=>'propertyValue',
			),
		);
	}
	*/

	/* ************************************************************************
	 *
	 * f-project.net implementation properties and methods are after this block
	 *
	 *********************************************************************** */

}