<?php

declare(strict_types=1);

namespace Dev\Infrastructure\EventListener;

use App\Infrastructure\AsService;
use League\OpenAPIValidation\PSR7\OperationAddress;
use League\OpenAPIValidation\PSR7\RequestValidator;
use League\OpenAPIValidation\PSR7\ResponseValidator;
use League\OpenAPIValidation\PSR7\ValidatorBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;

/**
 * Проверяет Request и Response на соответствие документации OpenApi
 */
#[AsService]
#[When('dev')]
#[When('test')]
final readonly class ValidateOpenApiSchema
{
    public const string VALIDATE_REQUEST_KEY = 'validate_request';
    public const string VALIDATE_RESPONSE_KEY = 'validate_response';

    private RequestValidator $requestValidator;
    private ResponseValidator $responseValidator;

    public function __construct(
        #[Autowire('%env(string:APP_ENV)%')]
        private string $appEnv,
        #[Autowire('%kernel.project_dir%%env(string:OPENAPI_YAML_FILE)%')]
        string $openApiFilePath,
        #[Autowire('%env(bool:OPEN_API_VALIDATION)%')]
        private bool $activateValidation,
    ) {
        $validatorBuilder = (new ValidatorBuilder())->fromYamlFile($openApiFilePath);
        $this->requestValidator = $validatorBuilder->getRequestValidator();
        $this->responseValidator = $validatorBuilder->getResponseValidator();
    }

    /**
     * Проверяет на соответствие запроса и описанной схемы в документации OpenApi.
     * Запускать после TraceableFirewallListener, иначе первой будет ошибка аутентификации.
     */
    #[AsEventListener(priority: 9)]
    public function validateRequest(RequestEvent $event): void
    {
        if (!$this->needValidate($event)) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->needValidateTest($request, self::VALIDATE_REQUEST_KEY)) {
            return;
        }

        $psrHttpFactory = $this->buildPsrHttpFactory();
        $psrRequest = $psrHttpFactory->createRequest($request);
        $this->requestValidator->validate($psrRequest);
    }

    #[AsEventListener]
    public function validateResponse(ResponseEvent $event): void
    {
        if (!$this->needValidate($event)) {
            return;
        }

        $request = $event->getRequest();
        if (!$this->needValidateTest($request, self::VALIDATE_RESPONSE_KEY)) {
            return;
        }

        $psrHttpFactory = $this->buildPsrHttpFactory();
        $psrResponse = $psrHttpFactory->createResponse($event->getResponse());

        $this->responseValidator->validate(
            opAddr: new OperationAddress(
                $request->getPathInfo(),
                strtolower($request->getMethod()),
            ),
            response: $psrResponse,
        );
    }

    private function needValidate(RequestEvent|ResponseEvent $event): bool
    {
        if (!$this->activateValidation || !$event->isMainRequest()) {
            return false;
        }

        return !str_starts_with($event->getRequest()->getPathInfo(), '/_profiler');
    }

    private function buildPsrHttpFactory(): PsrHttpFactory
    {
        $psr17Factory = new Psr17Factory();

        return new PsrHttpFactory($psr17Factory, $psr17Factory, $psr17Factory, $psr17Factory);
    }

    private function needValidateTest(Request $request, string $requestParameterName): bool
    {
        $parameterValue = $request->query->get($requestParameterName) ??
            $request->request->get($requestParameterName);

        return $this->appEnv === 'test' && $parameterValue === '1';
    }
}
