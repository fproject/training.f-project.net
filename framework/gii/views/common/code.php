<?php
Yii::import('gii.components.SourceCodeHighlighter');
if($file->type==='php')
{
	echo '<div class="content">';
    SourceCodeHighlighter::formatPHPSourcecode($file->content);
	echo '</div>';
}
elseif($file->type==='as')
{
    echo '<div class="content">';
    SourceCodeHighlighter::formatAS3Sourcecode($file->content);
    echo '</div>';
}
elseif(in_array($file->type,array('txt','js','css','as')))
{
	echo '<div class="content">';
	echo nl2br($file->content);
	echo '</div>';
}
else
	echo '<div class="error">Preview is not available for this file type.</div>';
?>