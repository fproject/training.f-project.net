<h1>Flex Service Generator</h1>
<p>This generator generate Flex AS3 Service files from a Zend AMF PHP services defined under alias 'application.services.amf'.</p>

<?php
    /** @var FlexServiceCode $model */
    /** @var CCodeForm $form */
	$form=$this->beginWidget('CCodeForm',
		array(
			'model'       => $model,
			'htmlOptions' => array('enctype' => 'multipart/form-data')
		)
	);
?>
	<?php echo $form->errorSummary($model); ?>
	<div class="row sticky">
		<?php
			echo $form->labelEx($model,'flexServicePath');
			echo $form->textField($model,'flexServicePath', array('size'=>65));
		?>
		<div class="tooltip">
			This refers to the directory that the new Flex AS3 Service files should be generated under.
			It should be specified in the form of a physical path instead of a alias, for example, <code>C:/flexService</code>.
            <br /><br />When running the generator on Mac OS, Linux or Unix, you may need to change the
			permission of the script path so that it is full writeable. <br />Otherwise you will get a generation error.
		</div>
	</div>

<?php $this->endWidget(); ?>