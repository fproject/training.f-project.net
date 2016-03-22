<?php
Yii::import('application.modules.amfGateway.components.AmfDiscoveryService');
/**
 * Implements the code model to generate Flex Service from Zend AMF PHP service.
 */
class FlexServiceCode extends CCodeModel
{
    /**
     * @var string default path alias where generated workflow files are copied
     */
    public $flexServicePath='';

    /**
     * @var string default path alias where generated workflow files are copied
     */
    public $amfServicePath='application.services.amf';

    public $serviceDescriptors=[];
    /////////////////////////////////////////////////////////////////////////////////////////////////

    
    /**
     * (non-PHPdoc)
     * @see CCodeModel::rules()
     */
    public function rules()
    {
        return array_merge(parent::rules(), array(
        	array('flexServicePath', 'validateFlexServicePath'),
        	array('flexServicePath', 'sticky'),
        ));
    }
    /**
     * Checks that the workflow path is a valid existing folder.
     *
     * @param string $attribute
     * @param array $params
     */
    public function validateFlexServicePath($attribute,$params)
    {
    	if($this->hasErrors('flexServicePath'))
    		return;
    	if(is_dir($this->flexServicePath)===false)
    		$this->addError('flexServicePath','You must input a valid directory path.');
    }

    /**
     * (non-PHPdoc)
     * @see CCodeModel::attributeLabels()
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), array(
            'flexServicePath' 		=> 'Output Flex Service Path',
        ));
    }
	
    /**
     * To preserve the uploaded file during code generation without having to upload a new file
     * the uploaded file is saved and it is used during the current session, unless a new file is
     * uploaded.
     *
     * (non-PHPdoc)
     * @see CCodeModel::prepare()
     */
    public function prepare()
    {
        $this->files=[];
        $templatePath=$this->templatePath;
        $this->serviceDescriptors = $this->discoverServiceDescriptors();

        foreach($this->serviceDescriptors as $descriptor)
        {
            $this->files[]=new CCodeFile(
                $this->getFlexServiceFile($descriptor),
                $this->render($templatePath.'/service.php',
                    array(
                        'descriptor'=>$descriptor,
                        'imports'=>$this->getImports($descriptor),
                    ))
            );
        }
    }

    /**
     * @return ServiceDescriptor[]
     */
    private function discoverServiceDescriptors()
    {
        $discoveryPath = Yii::getPathOfAlias("application.modules.amfGateway.components.AmfDiscoveryService");
        $amfPath = Yii::getPathOfAlias($this->amfServicePath);
        AmfDiscoveryService::$serviceFolderPaths = [$amfPath];
        AmfDiscoveryService::$serviceNames2ClassFindInfo["AmfDiscoveryService"] = new ClassFindInfo($discoveryPath, 'AmfDiscoveryService');
        $amfDiscoveryService = new AmfDiscoveryService();
        return $amfDiscoveryService->discover();
    }

    /**
     * @param ServiceDescriptor $descriptor
     * @return string
     */
    private function getFlexServiceFile($descriptor)
    {
        return $this->flexServicePath.'/'.$descriptor->name.'.as';
    }

    const MODEL_CLASS_NAME_REGEX = '/F([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)/';

    /**
     * @param ServiceDescriptor $descriptor
     * @return array
     */
    private function getImports($descriptor)
    {
        $imports = [];
        foreach($descriptor->methods as $method)
        {
            foreach($method->parameters as $param)
            {
                if(preg_match(self::MODEL_CLASS_NAME_REGEX,$param->type,$matches))
                {
                    Yii::trace(var_export($matches,true));
                    $s = $matches[1];
                    if(!array_key_exists($s,$imports))
                    {
                        $imports[$s] = $s;
                    }
                }
            }
        }
        return $imports;
    }

    public function classPHPDoc2ASDoc($comment,$tabs=-1)
    {
        return str_replace("\r\n", "\r\n\t", $comment);
    }

    public function methodPHPDoc2ASDoc($comment,$tabs=-1)
    {
        return str_replace("\r\n", "\r\n\t", $comment);
    }

    /**
     * @param MethodDescriptor $method
     * @return string
     */
    public function getAsParamDeclarations($method)
    {
        $s = "";
        foreach($method->parameters as $param)
        {
            if(preg_match(self::MODEL_CLASS_NAME_REGEX,$param->type,$matches))
            {
                $s = $s.$param->name.":".$matches[1].", ";
            }
            else
            {
                $s = $s.$param->name.":".$this->phpType2ASType($param->type).", ";
            }
        }
        return trim($s);
    }

    /**
     * @param MethodDescriptor $method
     * @return string
     */
    public function getAsCallParams($method)
    {
        $s = "";
        foreach($method->parameters as $param)
        {
            if($s != "")
                $s = $s.', ';
            $s = $s.$param->name;
        }
        return $s;
    }

    public function phpType2ASType($phpType)
    {
        switch($phpType)
        {
            case "string":
                $phpType = "String";
                break;
            case "array":
                $phpType = "Array";
                break;
            case null:
            case "":
            case "mixed":
                $phpType = "Object";
                break;
            case "bool":
                $phpType = "Boolean";
                break;
            default:
                break;
        }
        return $phpType;
    }
}
?>