<?php   /** @var ServiceDescriptor $descriptor */
        /** @var array $imports */
        /** @var FlexServiceCode $this */
?>
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of ProjectKit
//
// Copyright © <?php echo date("Y"); ?> ProjectKit. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/* ****************************************************************************
*
* This source file is automatically generated and maintained by Gii, be careful
* when modifying it.
*
* Your additional properties and methods should be placed at the bottom of
* this source file, after the notice lines.
*
******************************************************************************/
package net.projectkit.service
{
	import mx.rpc.CallResponder;
<?php foreach($imports as $import): ?>
	import net.projectkit.model.<?php echo $import; ?>;
<?php endforeach; ?>
	[RemoteService("<?php echo $descriptor->name; ?>")]
<?php $cmt = $this->classPHPDoc2ASDoc($descriptor->comment, 1);
    if(isset($cmt) && $cmt != "")
        echo "\t".$cmt."\r\n"; ?>
	public class <?php echo $descriptor->name; ?> extends ServiceBase
	{
<?php foreach($descriptor->methods as $method): ?>
        <?php $cmt = $this->methodPHPDoc2ASDoc($method->comment, 2);
            if(isset($cmt) && $cmt != "")
                echo $cmt."\r\n"; ?>
        public function <?php echo $method->name; ?>(<?php echo $this->getAsParamDeclarations($method) ?>

                            completeCallback:Function=null, failCallback:Function=null):CallResponder
        {
            return createServiceCall(remoteService.<?php echo $method->name; ?>(<?php echo $this->getAsCallParams($method) ?>),
                                        completeCallback, failCallback);
        }

<?php endforeach; ?>
	}
}