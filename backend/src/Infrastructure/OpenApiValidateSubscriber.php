<?php

declare(strict_types=1);

namespace App\Infrastructure;

use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

#[AsService]
#[When('dev')]
#[When('test')]
final class OpenApiValidateSubscriber implements EventSubscriberInterface
{
    public const DISABLE_VALIDATE_REQUEST_KEY = 'disable_request_validate';
    public const DISABLE_VALIDATE_RESPONSE_KEY = 'disable_response_validate';

    private readonly RequestValidator $requestValidator;
    private readonly ResponseValidator $responseValidator;

    public function __construct(
        #[Autowire('%env(string:APP_ENV)%')]
        private readonly string $appEnv,
        #[Autowire('%kernel.project_dir%%env(string:OPENAPI_YAML_FILE)%')]
        readonly string $openApiFilePath,
    ) {
        $validatorBuilder = (new ValidatorBuilder())->fromYamlFile($openApiFilePath);
        $this->requestValidator = $validatorBuilder->getRequestValidator();
        $this->responseValidator = $validatorBuilder->getResponseValidator();
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 9], // запускать после TraceableFirewallListener, иначе первой будет ошибка аутентификации
            ResponseEvent::class => 'onKernelResponse',
        ];
    }

    /**
     * Проверяет на соответствие запроса и описанной схемы в документации OpenApi
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->needValidate($event) === false) {
            return;
        }

        $request = $event->getRequest();
        if ($this->appEnv === 'test' && $request->request->get(self::DISABLE_VALIDATE_REQUEST_KEY) === '1') {
            return;
        }

        $psrHttpFactory = $this->buildPsrHttpFactory();
        $psrRequest = $psrHttpFactory->createRequest($request);
        $this->requestValidator->validate($psrRequest);
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ($this->needValidate($event) === false) {
            return;
        }

        $request = $event->getRequest();
        if ($this->appEnv === 'test' && $request->request->get(self::DISABLE_VALIDATE_RESPONSE_KEY) === '1') {
            return;
        }

        $psrHttpFactory = $this->buildPsrHttpFactory();
        $psrResponse = $psrHttpFactory->createResponse($event->getResponse());

        $this->responseValidator->validate(
            new OperationAddress(
                $request->getPathInfo(),
                strtolower($request->getMethod())
            ),
            $psrResponse
        );
    }

    private function needValidate(ResponseEvent|RequestEvent $event): bool
    {
        if ($event->isMainRequest() === false) {
            return false;
        }

        if (str_starts_with($event->getRequest()->getPathInfo(), '/_profiler') === true) {
            return false;
        }

        return true;
    }

    private function buildPsrHttpFactory(): PsrHttpFactory
    {
        $psr17Factory = new Psr17Factory();

        return new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    }
}
