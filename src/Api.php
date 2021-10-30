<?php

namespace MGGFLOW\Microservices;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;

class Api
{
    public array $defaultParams = [];
    public string $paramsType = 'form_params';
    public array $requestOptions = [];
    public ?Response $response = null;

    protected Client $client;
    protected string $msvcApiUrl;
    protected string $msvcName;
    protected string $fullMsvcApiUrl;
    protected string $objectName;
    protected string $actionName;
    protected array $params = [];
    protected string $asFormParamsType = 'form_params';
    protected string $asMultipartParamsType = 'multipart';

    public function __construct($msvcName, $apiUrl)
    {
        $this->msvcName = $msvcName;
        $this->msvcApiUrl = $apiUrl;

        $this->createFullMsvcApiUrl();

        $this->client = new Client();
    }

    protected function createFullMsvcApiUrl()
    {
        $this->fullMsvcApiUrl = $this->msvcApiUrl . '/' . $this->msvcName;
    }

    public function __get($name)
    {
        return $this->setObjectName($name);
    }

    public function setObjectName($name): self
    {
        $this->objectName = $name;

        return $this;
    }

    public function __call($name, $arguments)
    {
        $this->setActionName($name);

        $this->resetCallData();

        if (isset($arguments[0])) {
            $this->setParams($arguments[0]);
        } else {
            $this->setParams([]);
        }

        return $this;
    }

    public function setActionName($name): self
    {
        $this->actionName = $name;

        return $this;
    }

    protected function resetCallData()
    {
        $this->requestOptions = [];
        $this->response = null;
    }

    public function setParams($params): self
    {
        $this->params = array_merge($this->defaultParams, $params);

        return $this;
    }

    public function send()
    {
        $this->prepareRequestOptions();

        $this->response = $this->client->request('POST', $this->genRequestUrl(), $this->requestOptions);

        return $this->getContent();
    }

    protected function prepareRequestOptions()
    {
        switch ($this->paramsType) {
            case $this->asMultipartParamsType:
                $this->requestOptions[$this->asMultipartParamsType] = $this->genParamsMultipart();
                break;
            default:
                $this->requestOptions[$this->asFormParamsType] = $this->genParamsForm();
                break;
        }
    }

    protected function genParamsMultipart(): array
    {
        $multipart = [];
        foreach ($this->params as $name => $contents) {
            if (is_array($contents)) continue;
            $multipart[] = [
                'name' => $name,
                'contents' => $contents
            ];
        }

        return $multipart;
    }

    protected function genParamsForm(): array
    {
        return $this->params;
    }

    protected function genRequestUrl(): string
    {
        if (empty($this->objectName)) {
            return $this->fullMsvcApiUrl . '/' . $this->actionName;
        } else {
            return $this->fullMsvcApiUrl . '/' . $this->objectName . '/' . $this->actionName;
        }
    }

    protected function getContent()
    {
        if ($this->response->getStatusCode() > 400) return false;

        $content = json_decode($this->response->getBody()->getContents());

        return $content ?? false;
    }

    public function asForm(): self
    {
        $this->paramsType = $this->asFormParamsType;

        return $this;
    }

    public function asMultipart(): self
    {
        $this->paramsType = $this->asMultipartParamsType;

        return $this;
    }
}