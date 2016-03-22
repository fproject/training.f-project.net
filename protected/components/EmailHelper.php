<?php
///////////////////////////////////////////////////////////////////////////////
//
// Licensed Source Code - Property of f-project.net
//
// © Copyright f-project.net 2014. All Rights Reserved.
//
///////////////////////////////////////////////////////////////////////////////
/**
 * The Email Helper class
 *
 * @author Bui Sy Nguyen <nguyenbs@gmail.com>
 */
class EmailHelper {

    /*public static function sendMail($email,$subject,$message)
    {
        $adminEmail = Yii::app()->params['adminEmail'];
        $headers = "MIME-Version: 1.0\r\nFrom: $adminEmail\r\nReply-To: $adminEmail\r\nContent-Type: text/html; charset=utf-8";
        $message = wordwrap($message, 70);
        $message = str_replace("\n.", "\n..", $message);

        $level = error_reporting(0); // Don't display errors
        $success = mail($email,'=?UTF-8?B?'.base64_encode($subject).'?=',$message,$headers);

        if($level != 0)
            error_reporting($level);
        return $success;
    }*/

    /**
     * Send mail method
     * @param array $emailList
     * @param string $body
     * @param string $subject
     * @return bool
     */
    public function sendMail($toEmail, $fromUser, $project="", $projectGroup="")
    {
        $data = $this->mergeEmailDetail($toEmail, $fromUser, $project, $projectGroup);
        $workerServiceBody = $this->createWorkerServiceBody("pk-main", "inviteUser", $data, Yii::app()->params['sendMailCallbackUrl']);
        $curlRequester = new CurlRequester();
        $response = $curlRequester->sendRequest('POST',Yii::app()->params['workerServiceUrl'],$workerServiceBody);
    }

    private function createWorkerServiceBody($consumerId, $jobType, $data, $callbackUri = null)
    {
        /** @var array $result */
        $result = [];
        $result['consumerId'] = $consumerId;
        $result['jobType'] = $jobType;
        $result['data'] = $data;
        $result['callbackUri'] = $callbackUri;

        return $result;
    }

    private function mergeEmailDetail($toEmail, $fromUser, $project, $projectGroup)
    {
        return [
            "toEmails" => $toEmail,
            'fromUser' => $fromUser,
            'project' => $project,
            'projectGroup' => $projectGroup
        ];
    }
}