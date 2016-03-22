<?php
$class=get_class($model);
/* @var $form CCodeForm */
/* @var $model ModelCode */
Yii::app()->clientScript->registerScript('gii.model',"
function loadStickyFieldsFromBag(){
    $('#{$class}_buildRelations').prop('checked', true);
    $('#{$class}_userDefinedTypeColumns').val('');
    $('#{$class}_booleanColumns').val('');
    $('#{$class}_excludeColumns').val('');
    $('#{$class}_voExcludeFields').val('');
    $('#{$class}_ruleExcludeColumns').val('');
    $('#{$class}_searchExcludeColumns').val('');
    $('#{$class}_overrideRules').prop('checked', false);
    $('#{$class}_customRules').val('');
    $('#{$class}_useILogicalDeletableModelInterface').prop('checked', false);
    $('#{$class}_optimisticLockColumn').val('');
    $('#{$class}_useOptimisticLock').prop('checked', false);

    var stickyBagStr=$('#{$class}_stickyBag').val();
    var tableName=$('#{$class}_tableName').val();
    var stickyBagObj=JSON.parse(stickyBagStr);
    if(tableName in stickyBagObj)
    {
        var table=stickyBagObj[tableName];
        for(var fieldName in table)
        {
            if(table.hasOwnProperty(fieldName))
            {
                var elt=$('#{$class}_'+fieldName);
                if(elt.is(':checkbox'))
                {
                    var vl = table[fieldName]==0? false:true;
                    elt.prop('checked', vl);
                }
                else
                    elt.val(table[fieldName]);
            }
        }
    }
};
function getModelClassName(){
    var tableName=$('#{$class}_tableName').val();
    var i=tableName.lastIndexOf('.');
    if(i>=0)
        tableName=tableName.substring(i+1);
    var tablePrefix=$('#{$class}_tablePrefix').val();
    if(tablePrefix!='' && tableName.indexOf(tablePrefix)==0)
        tableName=tableName.substring(tablePrefix.length);
    var modelClass='';
    $.each(tableName.split('_'), function() {
        if(this.length>0)
            modelClass+=this.substring(0,1).toUpperCase()+this.substring(1);
    });
    return modelClass;
};
$('#{$class}_connectionId').change(function(){
	var tableName=$('#{$class}_tableName');
	tableName.autocomplete('option', 'source', []);
	$.ajax({
		url: '".Yii::app()->getUrlManager()->createUrl('gii/model/getTableNames')."',
		data: {db: this.value},
		dataType: 'json'
	}).done(function(data){
		tableName.autocomplete('option', 'source', data);
	});
});
$('#{$class}_tablePrefix').change(function(){
	$('#{$class}_modelClass').val(getModelClassName());
});
$('#{$class}_modelClass').change(function(){
	$(this).data('changed',$(this).val()!='');
});
$('#{$class}_tableName').bind('keyup change', function(){
	var model=$('#{$class}_modelClass');
	var tableName=$(this).val();
	if(tableName.substring(tableName.length-1)!='*') {
		$('.form .row.model-class').show();
	}
	else {
		model.val('');
		$('.form .row.model-class').hide();
	}
	if(!model.data('changed')) {
		model.val(getModelClassName());
		loadStickyFieldsFromBag();
	}
});
$('#{$class}_useOptimisticLock').change(function(){
	if ($(this).is(':checked')) {
        var colName=$('#{$class}_optimisticLockColumn').val();
        if(colName=='')
            $('#{$class}_optimisticLockColumn').val('version');
    }
});
$('.form .row.model-class').toggle($('#{$class}_tableName').val().substring($('#{$class}_tableName').val().length-1)!='*');
");
?>
<h1>Model Generator</h1>

<p>This generator generates a model class for the specified database table.</p>

<?php $form=$this->beginWidget('CCodeForm', array('model'=>$model)); ?>

	<div class="row sticky">
		<?php echo $form->labelEx($model, 'connectionId')?>
		<?php echo $form->textField($model, 'connectionId', array('size'=>65))?>
		<div class="tooltip">
		The database component that should be used.
		</div>
		<?php echo $form->error($model,'connectionId'); ?>


	</div>

    <div class="sticky">
        <?php echo $form->hiddenField($model, 'stickyBag'); ?>
    </div>

	<div class="row sticky">
		<?php echo $form->labelEx($model,'tablePrefix'); ?>
		<?php echo $form->textField($model,'tablePrefix', array('size'=>65)); ?>
		<div class="tooltip">
		This refers to the prefix name that is shared by all database tables.
		Setting this property mainly affects how model classes are named based on
		the table names. For example, a table prefix <code>tbl_</code> with a table name <code>tbl_post</code>
		will generate a model class named <code>Post</code>.
		<br/>
		Leave this field empty if your database tables do not use common prefix.
		</div>
		<?php echo $form->error($model,'tablePrefix'); ?>
	</div>
	<div class="row">
		<?php echo $form->labelEx($model,'tableName'); ?>
		<?php $this->widget('zii.widgets.jui.CJuiAutoComplete',array(
			'model'=>$model,
			'attribute'=>'tableName',
			'name'=>'tableName',
			'source'=>Yii::app()->hasComponent($model->connectionId) ? array_keys(Yii::app()->{$model->connectionId}->schema->getTables()) : array(),
			'options'=>array(
				'minLength'=>'0',
				'focus'=>new CJavaScriptExpression('function(event,ui) {
					$("#'.CHtml::activeId($model,'tableName').'").val(ui.item.label).change();
					return false;
				}')
			),
			'htmlOptions'=>array(
				'id'=>CHtml::activeId($model,'tableName'),
				'size'=>'65',
                'data-tooltip'=>'#tableName-tooltip'
			),
		)); ?>
        <div class="tooltip" id="tableName-tooltip">
		This refers to the table name that a new model class should be generated for
		(e.g. <code>tbl_user</code>). It can contain schema name, if needed (e.g. <code>public.tbl_post</code>).
		You may also enter <code>*</code> (or <code>schemaName.*</code> for a particular DB schema)
		to generate a model class for EVERY table.
		</div>
		<?php echo $form->error($model,'tableName'); ?>
	</div>
	<div class="row model-class">
		<?php echo $form->label($model,'modelClass', ['required'=>true]); ?>
		<?php echo $form->textField($model,'modelClass', ['size'=>65]); ?>
		<div class="tooltip">
		This is the name of the model class to be generated (e.g. <code>Post</code>, <code>Comment</code>).
		It is case-sensitive.
		</div>
		<?php echo $form->error($model,'modelClass'); ?>
	</div>
	<div class="row sticky">
		<?php echo $form->labelEx($model,'baseClass'); ?>
		<?php echo $form->textField($model,'baseClass', ['size'=>65]); ?>
		<div class="tooltip">
			This is the class that the new model class will extend from.
			Please make sure the class exists and can be autoloaded.
		</div>
		<?php echo $form->error($model,'baseClass'); ?>
	</div>
	<div class="row sticky">
		<?php echo $form->labelEx($model,'modelPath'); ?>
		<?php echo $form->textField($model,'modelPath', ['size'=>65]); ?>
		<div class="tooltip">
			This refers to the directory that the model class file should be generated under.
			It should be specified in the form of a path alias, for example, <code>application.models</code>.
		</div>
		<?php echo $form->error($model,'modelPath'); ?>
	</div>
    <div class="row sticky">
        <?php echo $form->labelEx($model,'voPath'); ?>
        <?php echo $form->textField($model,'voPath', ['size'=>65]); ?>
        <div class="tooltip">
            This refers to the directory that the VO class file should be generated under.
            It should be specified in the form of a path alias, for example, <code>application.services.vo</code>.
        </div>
        <?php echo $form->error($model,'modelPath'); ?>
    </div>
	<div class="row">
        <table>
            <tbody>
            <tr>
                <td><?php echo CHtml::label($form->checkBox($model,'buildRelations')." ".$model->getAttributeLabel('buildRelations'), false);?></td>
            </tr>
            </tbody>
        </table>

		<div class="tooltip">
			Whether relations should be generated for the model class.
			In order to generate relations, full scan of the whole database is needed.
			You should disable this option if your database contains too many tables.
		</div>
		<?php echo $form->error($model,'buildRelations'); ?>
	</div>

    <div class="row">
        <?php echo $form->labelEx($model,'customRelations'); ?>
        <?php echo $form->textArea($model,'customRelations', ['rows'=>5,'cols'=>65]); ?>
        <div class="tooltip">
            This is the list of column relation definitions you want to define by your own.
        </div>
    </div>

    <div class="row">
        <table>
            <tbody>
            <tr>
                <td><?php echo CHtml::label($form->checkBox($model,'commentsAsLabels')." ".$model->getAttributeLabel('commentsAsLabels'), false);?></td>
            </tr>
            </tbody>
        </table>
        <div class="tooltip">
            Whether comments specified for the table columns should be used as the new model's attribute labels.
            In case your RDBMS doesn't support feature of commenting columns or column comment wasn't set,
            column name would be used as the attribute name base.
        </div>
        <?php echo $form->error($model,'commentsAsLabels'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'userDefinedTypeColumns'); ?>
        <?php echo $form->textField($model,'userDefinedTypeColumns', ['size'=>65]); ?>
        <div class="tooltip">
            This is the list of date time columns that has type of Object or user defined type.
            Example: 'lastLoginTime'=>'DateTime',
            'xmlData'=>['type'=>'DOMDocument',
            'fromAR'=>'Zend_Xml_Security::scan({xmlData})']
        </div>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'booleanColumns'); ?>
        <?php echo $form->textField($model,'booleanColumns', ['size'=>65]); ?>
        <div class="tooltip">
            This is the list of boolean columns.
        </div>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'excludeColumns'); ?>
        <?php echo $form->textField($model,'excludeColumns', ['size'=>65]); ?>
        <div class="tooltip">
            This is the list of column name or relation name that wil be excluded from output Model code.
        </div>
    </div>

    <div class="row">
        <table>
            <tbody>
            <tr>
                <td><?php echo CHtml::label($form->checkBox($model,'useILogicalDeletableModelInterface')." ".$model->getAttributeLabel('useILogicalDeletableModelInterface'), false);?></td>
            </tr>
            </tbody>
        </table>

        <div class="tooltip">
            Whether to make VO class implements ILogicalDeletableModelInterface interface
        </div>
        <?php echo $form->error($model,'useILogicalDeletableModelInterface'); ?>
    </div>

    <div class="row">
        <table>
            <tbody>
            <tr>
                <td><?php echo CHtml::label($form->checkBox($model,'useOptimisticLock')." ".$model->getAttributeLabel('useOptimisticLock'), false);?></td>
                <td><?php echo $form->textField($model,'optimisticLockColumn', ['size'=>26]);?></td>
            </tr>
            </tbody>
        </table>

        <div class="tooltip">
            Whether to make ActiveRecord class implements optimistic locking
        </div>
        <?php echo $form->error($model,'useOptimisticLock'); ?>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'voExcludeFields'); ?>
        <?php echo $form->textField($model,'voExcludeFields', array('size'=>65)); ?>
        <div class="tooltip">
            This is the list of field name that wil be excluded from output VO code.
        </div>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'ruleExcludeColumns'); ?>
        <?php echo $form->textField($model,'ruleExcludeColumns', array('size'=>65)); ?>
        <div class="tooltip">
            This is the list of column that wil be excluded from validation rules in
            output Model code.
        </div>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'searchExcludeColumns'); ?>
        <?php echo $form->textField($model,'searchExcludeColumns', array('size'=>65)); ?>
        <div class="tooltip">
            This is the list of column that wil be excluded from searching fields
            in output Model code.
        </div>
    </div>

    <div class="row">
        <?php echo $form->labelEx($model,'customLabels'); ?>
        <?php echo $form->textField($model,'customLabels', array('size'=>65)); ?>
        <div class="tooltip">
            This is the list of column label definitions you want to define by your own.
        </div>
    </div>

    <div class="row">
        <table>
            <tbody>
            <tr>
                <td width="300"><?php echo $form->labelEx($model,'customRules'); ?></td>
                <td><div align="right"><?php echo $form->labelEx($model,'overrideRules',['value'=>'0','uncheckValue'=>null]);?></div></td>
                <td><?php echo $form->checkBox($model,'overrideRules');?></td>
            </tr>
            </tbody>
        </table>

        <?php echo $form->textArea($model,'customRules', array('rows'=>5,'cols'=>65)); ?>
        <div class="tooltip">
            This is the list of custom validation rules.
        </div>
    </div>

<?php $this->endWidget(); ?>
