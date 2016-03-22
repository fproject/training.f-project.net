<?php echo "<?php\n"; ?>
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net <?php echo date("Y"); ?>. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
class DefaultController extends Controller
{
	public function actionIndex()
	{
		$this->render('index');
	}
}