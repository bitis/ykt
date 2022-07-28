<?php

namespace App;

use GuzzleHttp\Client;

class Ykt
{
    protected $pcToken;

    protected $userId;

    protected $signUpUserId;

    protected $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/103.0.0.0 Safari/537.36'
    ];

    /**
     * @var Client
     */
    protected $http;

    public function __construct($token, $userId, $signUpUserId)
    {
        $this->http = new Client([
            'base_uri' => 'https://ykt.caigou2003.com/',
            'headers' => $this->headers
        ]);

        $this->pcToken = $token;
        $this->userId = $userId;
        $this->signUpUserId = $signUpUserId;
    }

    /**
     * 答题
     *
     * @param $onlineClassId
     * @param $onlineColumnId
     * @param $onlineDataId
     * @param $questionId
     * @param $anchorId
     * @param $userAnswer
     * @return mixed
     */
    public function onlineAnswer($onlineClassId, $onlineColumnId, $onlineDataId, $questionId, $anchorId, $userAnswer)
    {
        return $this->request('/ykt_java//study/monitorProcess', compact('onlineClassId',
            'onlineColumnId', 'onlineDataId', 'questionId', 'anchorId', 'userAnswer'))['resMessage'];
    }

    /**
     * 获取视频、题目信息
     *
     * @param $onlineClassId
     * @param $onlineColumnId
     * @param $onlineDataId
     * @return mixed
     */
    public function onlineVideo($onlineClassId, $onlineColumnId, $onlineDataId)
    {
        return $this->request('/ykt_java//study/onlineVideo', compact('onlineClassId',
            'onlineColumnId', 'onlineDataId'))['resData']['questionList'];
    }

    /**
     * 获取课程栏目、视频
     *
     * @param $onlineClassId
     * @return mixed
     */
    public function listColumn($onlineClassId)
    {
        return $this->request('/ykt_java//study/listColumn', compact('onlineClassId'))['resData']['columnList'];
    }

    /**
     * 获取课程栏目下视频
     *
     * @param $onlineClassId
     * @param $onlineColumnId
     * @return mixed
     */
    public function listDataArticle($onlineClassId, $onlineColumnId)
    {
        return $this->request('/ykt_java//study/listDataArticle',
            compact('onlineClassId', 'onlineColumnId'))['resData']['dataArticleList'];
    }

    /**
     * 监听学习进度
     *
     * @param $onlineClassId
     * @param $onlineColumnId
     * @param $videoId
     * @param $videoPlayTime
     * @param $monitorInterval
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function monitorProcess($onlineClassId, $onlineColumnId, $videoId, $videoPlayTime, $monitorInterval)
    {
        return $this->request('/ykt_java//study/monitorProcess', compact('onlineClassId',
            'onlineColumnId', 'videoId', 'videoPlayTime', 'monitorInterval'));
    }

    public function request($path, $params)
    {
        $response = $this->http->post($path, [
            'form_params' => array_merge([
                'signUpUserId' => $this->signUpUserId,
                'pcToken' => $this->pcToken,
                'userId' => $this->userId,
                'source' => '4',
            ], $params)
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}